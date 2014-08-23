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
/**
 * Boxtale_Envoimoinscher : handler to shipping methods used in frontend.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

  /**
   * Operator mode code
   * @access protected
   * @var string
   */
  protected $_code = 'envoimoinscher';

  /**
   * Services mapping between database ids and codes returned by API.
   * @access private
   * @var array
   */
  private $servicesMap = array();

  
  /**
   * Make API call and get available offers.
   * @param Mage_Shipping_Model_Rate_Request $request
   * @return Mage_Shipping_Model_Rate_Result
   */
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
		if (isset($GLOBALS['CACHE_OFFERS']))
		{
			return $GLOBALS['CACHE_OFFERS'];
		}
		
    // we don't launch the EnvoiMoinsCher quotation if the module isn't activated
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $orderData = Mage::getModel('checkout/cart')->getQuote()->getShippingAddress(); // order info
    if(!$this->getConfigFlag('active') || (!in_array($orderData->getCountryId(), explode(',', $moduleConfig['specificcountry'])) 
    && $moduleConfig['sallowspecific'] == 1 )) 
    {
	  return false;
    }
    $offresPresented = array();

    // get module helper
    $helper = Mage::helper("envoimoinscher");

    // company name used for quote request
    $storeName = $helper->getCompanyName($moduleConfig['company']);

    // get scale pricing model
    $scaDb = Mage::getModel("envoimoinscher/emc_services_has_zones");
    
    $result = Mage::getModel('shipping/rate_result');
    foreach($orderData->getStreet() as $s => $street) 
    {
	  $deliveryStreet[] = $street;
    }
    $totals = Mage::helper('checkout/cart')->getCart()->getQuote()->getTotals();
    $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
    $quoteData = Mage::getModel('sales/quote')->load($quoteId)->getData();

    // get dimensions by order weight
    $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
    $dimensions = $dimDb->getByWeight($orderData->getWeight());

    // get operators with pricing type
    $serDb = Mage::getModel("envoimoinscher/emc_services");
    
    // precise weight (weight must be bigger than real weight to pass EnvoiMoinsCher validation for sending abroad) and services
    $services = $helper->constructServicesString($moduleConfig['services_fran_small'], $moduleConfig['services_fran_ex']);
    // $zoneId = 2;
    $zoneId = Boxtale_Envoimoinscher_Model_Emc_Services::ZONE_FRANCE;
    $orderWeight = $orderData->getWeight();
    if($orderData->getCountryId() != "FR")
    {
	  $services = $helper->constructServicesString($moduleConfig['services_inte_small'], $moduleConfig['services_inte_ex']);
	  // $zoneId = 1;
	  $zoneId = Boxtale_Envoimoinscher_Model_Emc_Services::ZONE_INTERNATIONAL;
      // $orderWeight = $orderWeight + 0.1;
    }
    if(trim($services) != '')
    {
      // @Deprecated it doesn't take discount amount
      // get shipping rules 
      // $rules = Mage::getModel('envoimoinscher/emc_services_rules')->getRulesByServices(array(
          // 'price' => $totals['subtotal']->getValue(), 'country' => $orderData->getCountryId()
        // )
      // );
      $rules = Mage::getModel('envoimoinscher/emc_services_rules')->getRulesByServices(array(
          'price' => $quoteData['subtotal_with_discount'], 'country' => $orderData->getCountryId()
        )
      );
      $typeDest = "particulier";
      if($orderData->getCompany() != "")
      {
        $typeDest = "entreprise";
      }
      Mage::getSingleton('core/session')->setEmcDiscountRules(array());
      // get carrier names with API library
      $cotCl = new Env_Quotation(array("user" => $moduleConfig["user"], "pass" => 
                               $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
			// get module helper
			$helper = Mage::helper("envoimoinscher");
			
			$tracking = $helper->getTrackinData();
			$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		
      $from = array("pays" => "FR", "code_postal" => $moduleConfig["postal_code"], "ville" => $moduleConfig["city_name"], "type" => "entreprise", "societe" => $storeName, "adresse" => $moduleConfig["address"]);
      // set quotation informations (addresses, parcel parameters)
      $quotInfo = array(
			"collecte" => $helper->setCollectDate((int)$moduleConfig['pickup_date']), 
      "delai" => Mage::getModel('envoimoinscher/emc_delay')->getPriority($moduleConfig['priority']),
      "code_contenu" => $moduleConfig["content"], 
      "type_emballage.emballage" => $moduleConfig["wrapping"], 
			'valeur' => $quoteData['subtotal_with_discount'],
      "version" => Mage::helper('envoimoinscher')->getModuleInfoToApi("version"), 
			"module" => Mage::helper('envoimoinscher')->getModuleInfoToApi("name"));
      $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
      $cotCl->setPerson("expediteur", $from);
      $cotCl->setPerson("destinataire", array("pays" => $orderData->getCountryId(), "code_postal" => $orderData->getPostcode(), "ville" => $orderData->getCity(), "type" => $typeDest, 
      "adresse" => implode('|', $deliveryStreet)));
      $cotCl->setType($moduleConfig["type"], array(1 => array("poids" => $orderWeight, "longueur" => $dimensions[0]['length_ed'], 
      "largeur" => $dimensions[0]['width_ed'], "hauteur" => $dimensions[0]['height_ed'])));
      
			
			
      $acceptedSrv = $serDb->getServicesListIn($services, 'command');
			
			$offers_quotation = array();
			if (count($acceptedSrv) >= 10)
			{
      $cotCl->getQuotation($quotInfo); 
      $cotCl->getOffers(false);
				$offers_quotation = $cotCl->offers;
			}
			else
			{
				foreach($acceptedSrv as $offerDb)
				{
					$quotInfo['operateur'] = substr($offerDb,0,4);
					$quotInfo['service'] = substr($offerDb,4,strlen($offerDb)-4);
					$cotCl->getQuotation($quotInfo);
					if ($cotCl->curlError)
					{
						continue;
					}
					$cotCl->getOffers(false);
					if (isset($cotCl->offers[0]))
					{
						foreach($cotCl->offers as $quotOffer)
						{
							$offers_quotation[count($offers_quotation)] = $quotOffer;
						}
					}
					$cotCl->offers = array();
				}
			}
			
      // sort offers and get scale for defined operators 
      $offers = $this->sortOffers($offers_quotation, $acceptedSrv);
      $scale = $scaDb->getByWeightOrPrice($quoteData['subtotal_with_discount'], $orderData->getWeight(), $services, $zoneId);

			// check if we need to display the "find parcel point" link
			$displaySuppHtml = true;
			// TODO trouver une solution pour savoir s'il faut afficher ou non le bouton point relais
			
      $restDb = Mage::getModel('envoimoinscher/emc_restitutions');
      if(!isset($moduleConfig['pricing_return'])) $moduleConfig['pricing_return'] = $restDb->getDefaultApiKey();
	  $apiKey = $restDb->getApiKey($moduleConfig['pricing_return']);
	  
      $servicesInfos = $serDb->listServices();
      foreach($offers as $o => $offer) 
      {
        $priceId = $this->servicesMap[$offer['operator']['code'].$offer['service']['code']];
        $methodCode = $offer["operator"]["code"]."_".$offer["service"]["code"];
        // check if the offer hasn't already been added
        // check profitability criterion too
        if(!in_array($methodCode, $offresPresented)
          && ($scale[$priceId]['price'] == '' || ((float)$scale[$priceId]['price'] >= (float)$offer["price"][$apiKey]) ||
         // ($scale[$priceId]['profitability'] > 0 && (float)$scale[$priceId]['price'] < (float)$offer["price"]["tax-inclusive"])
         ($scale[$priceId]['profitability'] > Boxtale_Envoimoinscher_Model_Emc_Services::PROF_NOTSHOW && (float)$scale[$priceId]['price'] < (float)$offer["price"][$apiKey])
          )
        )
        {
	      // if has scale
	      // if(isset($scale[$priceId]) && ($scale[$priceId]['price'] != '' && (((float)$scale[$priceId]['price'] < (float)$offer["price"]["tax-inclusive"] && $scale[$priceId]['profitability'] == 2)
	      if(isset($scale[$priceId]) && ($scale[$priceId]['price'] != '' && (((float)$scale[$priceId]['price'] < (float)$offer["price"][$apiKey] && $scale[$priceId]['profitability'] == Boxtale_Envoimoinscher_Model_Emc_Services::PROF_SHOWSCALE)
            || ((float)$scale[$priceId]['price'] >= (float)$offer["price"][$apiKey]))))
          {
	        $offer["price"][$apiKey] = $scale[$priceId]['price'];
Mage::Log("Found price for offer ".$offer["service"]["code"]."=======>".$scale[$priceId]);
          }
		  else {
Mage::Log("Not found price for offer ".$offer["service"]["code"]."=======>".$scale[$priceId]); }
          // handle parcel points choice
          $suppHtml = "";
          if($displaySuppHtml && $offer['delivery']['type'] == 'PICKUP_POINT')
          { 
            $code = $this->_code."_".$offer["operator"]["code"]."_".$offer["service"]["code"];
            $suppHtml = '<!-- additional --> <a href="#" id="mapLink'.$code.'" class="class_'.time().'" onclick="javascript:selectPoint(\''.$code.'\'); return false;">Sélectionner le point de proximité</a>';
if($moduleConfig['opencartatclick'] == 1)
{
$suppHtml .= '<script type="text/javascript">
Event.observe("s_method_envoimoinscher_'.$this->_code.'_'.$methodCode.'", "click", function(event) {
  document.getElementById("mapLink'.$code.'").setStyle({display : "none"});
  selectPoint("'.$code.'");
});
</script>
';
}
            // Mage::getSingleton('core/session')->setPoints($offer['mandatory']['retrait.pointrelais']['array']);
            $method = "setPoints".strtoupper($offer["operator"]["code"]);
            Mage::getSingleton('core/session')->$method($offer['mandatory']['retrait.pointrelais']['array']);
          }
          $offer['service']['label'] = $servicesInfos[$priceId]['label_store'];
          
          // check if some rules exist for this service
          $finalPrice = $offer["price"][$apiKey];
          if(isset($rules[$offer["operator"]["code"]."_".$offer["service"]["code"]]))
          {
Mage::Log("Rule exists for ".$offer["operator"]["code"]."_".$offer["service"]["code"]);
            $finalPrice = Mage::getModel('envoimoinscher/emc_services_rules')->getShippingAmount($rules[$offer["operator"]["code"]."_".$offer["service"]["code"]], $offer["price"][$apiKey], $offer["operator"]["code"]."_".$offer["service"]["code"]);
          }
	      $method = Mage::getModel('shipping/rate_result_method');
	      $method->setCarrier($this->_code);
	      // $method->setCarrierTitle($offer["service"]["label"]);
	      // $method->setCarrierTitle($offer["operator"]["label"]);
	      $method->setMethod($this->_code."_".$methodCode);
	      $method->setMethodTitle("{$offer["service"]["label"]}".$suppHtml);
	      // $method->setMethodTitle("{$offer["service"]["label"]} ({$offer["operator"]["label"]})".$suppHtml);
	      $method->setPrice($finalPrice);
          $method->setMethodDescription($servicesInfos[$priceId]['desc_store']);
	      $result->append($method);
          $offresPresented[] = $methodCode;
        }
      }
    }
		$GLOBALS['CACHE_OFFERS'] = $result;
    return $result;
  }

  /**
   * Sort offers and make an array with operators which have scale pricing.
   * @return array Array with offers defined by user
   */
  private function sortOffers($offers, $acceptedSrv) 
  {
    foreach($offers as $o => $offer) 
    {
	  if(!in_array($offer['operator']['code'].$offer['service']['code'], $acceptedSrv)) 
	  {
	    unset($offers[$o]);
	  }
	  else
      {
        $key = array_keys($acceptedSrv, $offer['operator']['code'].$offer['service']['code']);
        $this->servicesMap[$offer['operator']['code'].$offer['service']['code']] = $key[0];
      }
    }
    return $offers;
  }

  /**
   * Retourne les modes de livraison authorisés.
   * @return array
   */
  public function getAllowedMethods()
  {
	return array($this->_code => $this->getConfigData('name'));
  }

  /**
   * Getter for carrier code needed for Magento version under 1.4 
   * @return string
   */
  public function getCarrierCode()
  {
	return $this->_code;
  }
 

}