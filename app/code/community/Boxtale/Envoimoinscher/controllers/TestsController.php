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
 * Boxtale_Envoimoinscher : tests controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_TestsController extends Mage_Adminhtml_Controller_Action 
{        

  /**
   * Make one array with presented operators (without doubles).
   * @access public
   * @param array $filterOpe Operators list.
   * @return array Array with all operators.
   */
  public function doAction() 
  {
    $product = Mage::getModel('catalog/product');
    $products = $product->getCollection() 
    ->addAttributeToSelect(array('name', 'weight'), 'inner') 
    ->setOrder('name', 'asc')->getData();
    $countries = Mage::getModel('directory/country_api')->items();
    $result = array();
    foreach($countries as  $country)
    {
      $result[$country['country_id']] = $country['name']; 
    }
    asort($result);
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $block->setData('products', $products);
    $block->setData('countlabels', $result);
    $block->setData('defaultvals', array('address' => $moduleConfig['address'], 'city' => $moduleConfig['postal_code'], 'zipcode' => $moduleConfig['city_name']));
    $block->setData('link', $this->getUrl('envoimoinscher/tests/estimate'));
    $this->renderLayout();
  }

  /**
   * Calls API quotation
   */
  public function estimateAction()
  {
    $getData = $this->getRequest()->getParams();
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
 
    // get product
    $itemDb = Mage::getModel("catalog/product");
    $product = Mage::getModel('catalog/product') 
    ->load($getData['product']); 

    // get dimensions by order weight
    $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
    $dimensions = $dimDb->getByWeight($product->getWeight()); 

		
    $cotCl = new Env_Quotation(array("user" => 
																$moduleConfig["user"], "pass" => 
																$moduleConfig["mdp"], "key" => 
																$moduleConfig["cle"]));
    // get module helper
    $helper = Mage::helper("envoimoinscher");

		$tracking = $helper->getTrackinData();
		$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		
    // set quotation informations (addresses, parcel parameters)
    $quotInfo = array(
		"collecte" => $helper->setCollectDate((int)$moduleConfig['pickup_date']), 
    "delai" => Mage::getModel('envoimoinscher/emc_delay')->getPriority($moduleConfig['priority']),
    "code_contenu" => $moduleConfig["content"], 
    "type_emballage.emballage" => $moduleConfig["wrapping"], 
		"valeur" => $product->getPrice(),
    "version" => Mage::helper('envoimoinscher')->getModuleInfoToApi("version"), 
		"module" => Mage::helper('envoimoinscher')->getModuleInfoToApi("name")
		);
    $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $cotCl->setPerson("expediteur", array("pays" => "FR", "code_postal" => $getData["from_zipcode"], "ville" => $getData["from_city"], "type" => "entreprise", "adresse" => $getData['from_address']));
    $cotCl->setPerson("destinataire", array("pays" => $getData['to_country'], "code_postal" => $getData['to_zipcode'], "ville" => $getData['to_city'], "type" => "particulier", "adresse" => $getData['to_address']));
    $cotCl->setType($moduleConfig["type"], array(1 => array("poids" => $product->getWeight(), "longueur" => $dimensions[0]['length_ed'], 
    "largeur" => $dimensions[0]['width_ed'], "hauteur" => $dimensions[0]['height_ed'])));
    $cotCl->getQuotation($quotInfo); 
    $cotCl->getOffers(false); 
     
    if($cotCl->curlError)
    {
      $result = array('error' => 1, 'message' => $cotCl->curlErrorText);
    }
    elseif($cotCl->respError)
    {
      $result = array('error' => 1, 'message' => $cotCl->respErrorsList[0]['message']);
    }
    elseif(count($cotCl->offers) == 0)
    {
      $result = array('error' => 1, 'message' => 'Votre envoi nécessite une réponse personnalisée. Vous pouvez demander un devis à EnvoiMoinsCher.com par téléphone au 01 75 77 37 97');
    }
    else
    {
      $offers = array();
      foreach($cotCl->offers as $offer)
      {
        $offer['characteristics'] = '<b>-</b>'.implode('<br /><b>-</b>  ', $offer['characteristics']);
        $offers[] = $offer;
      }
      $result = array('offers' => $offers);
    }
    echo json_encode($result);
    die();
  }

} 