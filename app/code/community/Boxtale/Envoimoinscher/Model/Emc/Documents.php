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
 * Boxtale_Envoimoinscher : shipping documents (labels and pro forma).
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Documents extends Mage_Core_Model_Abstract 
{ 

  /**
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_documents', 'id_ed');
  } 

  /** 
   * Fonction retreives shipping documents (labels and documents like pro forma if exists) 
   * for EnvoiMoinsCher order.
   * @param int $orderId Order id.
   * @return array Labels array.
   */ 
  public function getDocsToOrder($orderId)
  {
    $documents = $this->getCollection()
                ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                ->setOrder("id_ed", "ASC")
                ->loadData()
                ->getData();
    $docs = array();
    foreach($documents as $d => $document)
    {
      $i = count($docs[$d]);
      $docs[$document['type_ed']][$i] = array('state' => $document['state_ed'], 'link' => $document['link_ed']);
    }
    return $docs;
  }

  /** 
   * Retreives shipping labels for EnvoiMoinsCher order.
   * @param int $orderId Order id.
   * @return array Labels array.
   */ 
  public function getLabelsToOrder($orderId)
  {
    return $this->getCollection()
                ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                ->addFieldToFilter("type_ed", 'label')
                ->setOrder("id_ed", "ASC")
                ->loadData()
                ->getData();
  }

  public function updateLabelState($orderId, $orderState)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_documents');
    $connection = Mage::getSingleton("core/resource")->getConnection("core_write");
    $data = array("state_ed" => $orderState);
    $condition = array($connection->quoteInto('sales_flat_order_entity_id = ?', $orderId));
    $connection->update($tableName, $data, $condition);
  }

}
?>