<?php 
/*
    This file is part of EnvoiMoinsCher's shipping plugin for Magento.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Boxtale_Envoimoinscher_Model_Emc_Onepage extends Mage_Checkout_Model_Type_Onepage 
{

  public function saveShippingMethod($shippingMethod)
  {
    Mage::getSingleton('core/session')->setParcelPoint('');
    $postData = Mage::getSingleton('core/app')->getRequest()->getPost();
    $orderData = Mage::getModel('checkout/cart')->getQuote()->getData();
    $point = explode('-', $postData['point']);

    $codes = Mage::helper("envoimoinscher")->decomposeCode($shippingMethod);
    $opeSrvInfo = Mage::getModel('envoimoinscher/emc_services')->getByOpeSrv($codes['ope'], $codes['srv']);
    if($opeSrvInfo['is_parcel_point_es'] == 1 && count($point) > 1 
	&& ctype_alnum($point[0]) && ctype_alnum($point[1]))
    {
      Mage::getSingleton('core/session')->setParcelPoint($postData['point']);
      // remove additional informations after shipping method title
      $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
      $rate->load((int)$rate->getRateId())->addData(array('method_title' => $this->cleanShippingName($rate->getMethodTitle())))->save();
    }
    elseif($opeSrvInfo['is_parcel_point_es'] == 1 && (count($point) <= 1 ||
	!ctype_alnum($point[0]) || ctype_alnum($point[1]))) 
    {
      return array('error' => -1, 'message' => array("Veuillez sélectionner le point relais de retrait"));
    }
    return parent::saveShippingMethod($shippingMethod);
  }


  public function savePayment($data)
  {
    $result = parent::savePayment($data); 
    // remove Relais Colis JavaScript Link 
    $quote = $this->getQuote(); 
    $shippAdd = $quote->getShippingAddress();
    $shippingMethod = $shippAdd->getShippingMethod();
    $shippingDescription = $this->cleanShippingName($shippAdd->getShippingDescription());
    $shippAdd->setShippingDescription(trim($shippingDescription));
 
    // $codes = Mage::helper("envoimoinscher")->decomposeCode($shippingMethod);
// Mage::log("saveShippingMethod ::: {$codes['ope']} ::: {$codes['srv']} ");
// Mage::log("Cleanded \$shippingDescription is {$shippingDescription} ");
    // Update quote tables : avoid to show HTML a tag with "select your parcel point"
    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
    $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_shipping_rate');
    $sql = "UPDATE $tableName SET method_title = ?
    WHERE address_id = ".(int)$shippAdd->getAddressId()." AND code = '".$shippingMethod."'";
    $write->query($sql, array(trim($shippingDescription))); 
    $write2 = Mage::getSingleton('core/resource')->getConnection('core_write');
    $tableName2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_address');
    $sql2 = "UPDATE $tableName2 SET shipping_description = ?
    WHERE address_id = ".(int)$shippAdd->getAddressId()." AND shipping_method = '".$shippingMethod."'";
    $write2->query($sql2, array(trim($shippingDescription))); 
    return $result;	 
  }

  /**
   * Removes all HTML tags from shipping name.
   * @access private
   * @param string $name Shipping name (may contain HTML tags)
   * @return string Shipping name without HTML tags
   */
  private function cleanShippingName($name)
  {
    $method = explode('<!-- additional -->', $name);
    // Magento 1.7 escapes HTML chars method
    if(count($method) < 2)
    {
      $method = explode(htmlspecialchars('<!-- additional -->'), $name);
    }
    if(count($method) < 2) return $name;
    return trim($method[0]);
  }
   
}
 

?>