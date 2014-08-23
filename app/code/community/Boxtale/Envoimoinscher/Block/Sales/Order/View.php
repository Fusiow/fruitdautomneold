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
 * Boxtale_Envoimoinscher : order's view.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_Block_Sales_Order_View extends Mage_Core_Block_Template
{ 
  public $lastClass = "";

  public function __construct()
  {
    $this->order = Mage::registry('current_order');
    $this->setData('shipping_amount', $this->order['shipping_amount']); 
    $this->setData("tracking", Mage::getModel('envoimoinscher/emc_tracking')->getTracking($this->order['entity_id']));
  }

  public function getDocuments() 
  {
    return Mage::getModel('envoimoinscher/emc_documents')->getDocsToOrder($this->order['entity_id']);
  }

  public function getLabelLink()
  {
    $url = Mage::getModel('adminhtml/url');
    $url->setRouteName('envoimoinscher');
 	$url->setControllerName('sales');
 	$url->setActionName('print');
 	return $url->getUrl(null, array('order' => $this->order['entity_id']));
  }

  public function getOrderKey()
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $key = "ref_ope_eor";
    $trackingModel = Mage::getModel("envoimoinscher/emc_tracking");
    if($trackingModel->isEmcTracking((int)$moduleConfig["tracking_mode"]))
    {
      $key = "ref_emc_eor";
    }
    return $key;
  }

  public function getOrder()
  {
    $code = Mage::helper("envoimoinscher")->decomposeCode($this->order["shipping_method"]);
    return Mage::getModel('envoimoinscher/emc_orders')->getOrderWithService($this->order['entity_id'], $code["ope"], $code["srv"]);
  }

  public function getPrices()
  {
    return Mage::getModel('envoimoinscher/emc_orders')->getPrices($this->order['entity_id']);
  }

  public function getParcels()
  {
    return Mage::getModel('envoimoinscher/emc_orders_parcels')->getParcels($this->order['entity_id']);
  }
  
  public function getParcelPoint()
  {
    $point = Mage::getModel('envoimoinscher/emc_points')->getPoint($this->order['entity_id']);
    $address = ($this->order->getShippingAddress()->getData());
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $poiCl = new Env_ParcelPoint(array("user" => $moduleConfig["user"], "pass" => 
    $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    $poiCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $poiCl->getParcelPoint("dropoff_point", $point[0]['emc_operators_code_eo'].'-'.$point[0]['point_ep'], $address["country_id"]); 
    return $poiCl->points["dropoff_point"];
  }

  public function getClassName()
  {
    if($this->lastClass == "box-left")
    {
      $this->lastClass = "box-right";
      return "box-right";
    }
    else
    {
      $this->lastClass = "box-left";
      return "box-left";
    }
  }

  public function getClearDiv()
  {
    if($this->lastClass == "box-right")
    {
      return '<div class="clear"></div>';
    }
    return "";
  }

}