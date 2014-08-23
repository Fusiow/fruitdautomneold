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
 * Boxtale_Envoimoinscher : EnvoiMoinsChers' orders.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Orders extends Mage_Core_Model_Abstract 
{ 

  /**
   * Tracking states used by order pages in frontend and backend.
   * @access protected
   * @var array
   */
  protected $_statesTracking = array('CMD' => 'commande passé', 'ENV' => 'commande envoyé',
  'ANN' => 'commande annulé', 'LIV' => 'commande livrée');

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_orders');
  }
  
  /**
   * Getter for tracking codes
   * @return array Tracking codes
   */
  public function getTrackingCodes()
  {
    return $this->_statesTracking;
  }

  /**
   * Checks if an order exists.
   * @return boolean True for existing order
   */
  public function existsOrder($orderId)
  {
    $order = $this->getCollection()->loadData()
							       ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                                   ->getData();
    if(count($order) > 0)
    {
      return true;
    }
    return false;
  }

  /**
   * Orders shipment by EnvoiMoinsChers' API. It does some supplementary thinks, like handle order status
   * or generate shipment labels which can be printed by store owner. 
   * @return void
   */
  public function makeEmcOrder($orderId, $order, $emcOrder)
  {
    // get order states and change it after sending the EnvoiMoinsCher.com order
    Mage::getModel("sales/order_status_history")->setData(array("parent_id" => $orderId, "is_visible_on_front" => 0,
    "status" => $order['status'], "is_customer_notified" => 0, "created_at" => date('Y-m-d H:i:s')))->save();
    Mage::getModel("sales/order")->load($orderId)->addData(array("status" => "traitement"))->save();

    // put EnvoiMoinsCher order informations
    Mage::getModel("envoimoinscher/emc_orders")->setData(array('sales_flat_order_entity_id' => $orderId,
    'emc_operators_code_eo' => $emcOrder["offer"]["operator"]["code"], 'price_ht_eor' => $emcOrder["price"]["tax-exclusive"], 'price_ttc_eor' => $emcOrder["price"]["tax-inclusive"],
    'ref_emc_eor' => $emcOrder['ref'], 'service_eor' => $emcOrder["service"]["label"], 'date_order_eor' => date('Y-m-d H:i:s'), 
    'parcels_eor' => count($order["shipment"]), 'date_collect_eor' => $emcOrder["collection"]["date"].' '.$emcOrder["collection"]["time"], 'date_del_eor' => $emcOrder["delivery"]["date"].' '.$emcOrder["delivery"]["time"], 'base_url_eor' => $order['serverUrl'],
    'code_eor' => $emcOrder["trackingCode"]
    ))->save();

    // insert parcels informations
    foreach($order["shipment"] as $s => $shipmentLine)
    {
      Mage::getModel("envoimoinscher/emc_orders_parcels")->setData(array('sales_flat_order_entity_id' => $orderId,
        'number_eop' => $s, 'weight_eop' => $shipmentLine["poids"], 'length_eop' => $shipmentLine["longueur"],
         'width_eop' => $shipmentLine['largeur'], 'height_eop' => $shipmentLine["hauteur"]
      ))->save();
    }
    
    // insert shipping label informations
    $docDb = Mage::getModel("envoimoinscher/emc_documents");
    foreach($emcOrder['labels'] as $label)
    {
	  $docDb->setData(array('sales_flat_order_entity_id' => $orderId, 'link_ed' => $label, 'type_ed' => 'label', 'state_ed' => 0))->save();
    }

    // if proforma
    if($emcOrder['proforma'] != '')
    {
      $docDb->setData(array('sales_flat_order_entity_id' => $orderId, 'link_ed' => $emcOrder['proforma'], 'type_ed' => 'proforma'))->save();
    }

    // remove all temporary data
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders_tmp');
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $condition = array($connection->quoteInto('emc_orders_entity_id = ?', $orderId));
    $connection->delete($tableName, $condition);
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders_errors');
    $condition = array($connection->quoteInto('sales_flat_order_entity_id = ?', $orderId));
    $connection->delete($tableName, $condition);
  }
  
  /**
   * Get prices for order.
   * @param $orderId int Order id.
   * @return void
   */
  public function getPrices($orderId)
  {
    $row = $this->getCollection()->loadData()
							     ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                                 ->getData();
    return array('ttc' => $row[0]['price_ttc_eor'], 'ht' => $row[0]['price_ht_eor']);
  }

  /**
   * Get prices for order.
   * @param $ids array Orders array.
   * @return array List with found orders.
   */
  public function getOrdersByArray($ids)
  {
    $row = $this->getCollection()->getSelect()
							     ->where('sales_flat_order_entity_id  IN('.implode(',' , $ids).')');
    return $row->query();
  }

  public function getOrderById($orderId) 
  {
    return   $this->getCollection()->loadData()
							       ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                                   ->getData();
  }

  public function getOrderWithService($orderId, $ope, $service)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services');
    $arr = array();
    $collection = Mage::getModel('envoimoinscher/emc_orders')->getCollection()->getSelect()
    ->join(array('es' => $tableName), '"'.$service.'" = es.code_es', array('tracking' => 'tracking_es'))
    ->where('sales_flat_order_entity_id = ?', $orderId);
    return $collection->query()->fetchAll();
  }

  public function updateOpeReference($orderId, $reference)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders');
    $connection = Mage::getSingleton("core/resource")->getConnection("core_write");
    $data = array("ref_ope_eor" => $reference);
    $condition = array($connection->quoteInto('sales_flat_order_entity_id = ?', $orderId));
    $connection->update($tableName, $data, $condition);
  }

}
?>