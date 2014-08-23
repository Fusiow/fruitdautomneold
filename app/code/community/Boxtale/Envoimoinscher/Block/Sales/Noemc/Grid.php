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
 * Boxtale_Envoimoinscher : sales grid.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_Block_Sales_Noemc_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('sales_noemc_grid');
    $this->setUseAjax(false);
    $this->setDefaultSort('created_at');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setFilterVisibility(false);
  }

  protected function _getCollectionClass()
  {
    return 'sales/order_grid_collection';
  }

  protected function _prepareCollection()
  {
    $salesOrder = Mage::getSingleton('core/resource')->getTableName('sales/order');
    $emcOrders = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders');
    $emcOrdersErrors = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders_errors');
    $shipmentOrder = Mage::getSingleton('core/resource')->getTableName('sales/shipment');
    $collection = Mage::getResourceModel($this->_getCollectionClass());
    $this->setCollection($collection); 
    $this->_preparePage(); 
    $collection->getSelect()
    ->join(array('or' => $salesOrder), 'or.entity_id  = main_table.entity_id', 
      array('entity' => 'entity_id', 'ship_price' => 'shipping_amount', 'label_service' => 'shipping_description'))
    ->joinLeft(array('sor' => $shipmentOrder), 'sor.order_id  = main_table.entity_id', 
      array())
    ->joinLeft(array('eoe' => $emcOrdersErrors), 'eoe.sales_flat_order_entity_id = main_table.entity_id', 
      array())
    ->joinLeft(array('eor' => $emcOrders), 'eor.sales_flat_order_entity_id = main_table.entity_id', 
      array())
    ->where('or.shipping_method NOT LIKE "envoimoinscher_%" AND or.state NOT IN("canceled", "fraud", "holded", "closed", "complete") AND sor.order_id IS NULL AND eoe.sales_flat_order_entity_id IS NULL AND eor.sales_flat_order_entity_id IS NULL')
    ->order('or.increment_id DESC'); 
    return $this;
  }

  protected function _prepareColumns()
  { 
    // parent::_prepareColumns();  
    $this->addColumn('real_order_id', array(
      'header'=> Mage::helper('sales')->__('Order #'),
      'width' => '80px',
      'type'  => 'text',
      'index' => 'entity',
      'filter'   => false, 
      'sortable' => false,
    ));
    $this->addColumn('shipping_name', array(
      'header' => 'Nom destinataire',
      'index' => 'shipping_name',
      'filter'   => false, 
      'sortable' => false,
      'width' => '210px',
    ));
    $this->addColumn('status', array(
      'header' => Mage::helper('sales')->__('Status'),
      'index' => 'status',
      'type'  => 'options',
      'width' => '300px',
      'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
      'filter'   => false, 
      'sortable' => false,
    ));
    $this->addColumn('ship_price', array('header' => 'Coût transport  <br />client (TTC, €)',
      'index' => 'ship_price',
      'filter'   => false, 
      'sortable' => false,
      'type'  => 'currency',
      'currency' => 'order_currency_code',
      'width' => '70px',
      'align' => 'right'
    ));  
    $this->addColumn('grand_total', array(
      'header' => 'Montant total',
      'index' => 'grand_total',
      'type'  => 'currency',
      'currency' => 'order_currency_code',
      'filter'   => false, 
      'sortable' => false,
      'width' => '70px',
      'align' => 'right'
    )); 
    $this->addColumn('label_service', array('header'=> 'Offre',
      'getter' => 'getId',
      'filter'   => false, 
      'sortable' => false,
      'width' => '260px',
      'renderer'  => 'Boxtale_Envoimoinscher_Block_Renderers_Service')
    ); 
     $this->addColumn('order_view',array('header'=> 'Fiche de <br />commande', 
      'filter'   => false,
      'sortable' => false,
      'width' => '50px',
      'type' => 'action',
      'getter' => 'getId',
      'actions' => array(array(
        'caption' => Mage::helper('sales')->__('View'),
        'url'     => array('base'=> 'adminhtml/sales_order/view'),
        'field'   => 'order_id' 
      ))
    ));
    $this->addColumn('action', array('header' => '', 
      'getter' => 'getId',
      'renderer'  => 'Boxtale_Envoimoinscher_Block_Renderers_Sending',
      'filter'   => false, 
      'sortable' => false)); 
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('sales_flat_order_entity_id');
    $this->getMassactionBlock()->setFormFieldName('order_ids');
    $this->getMassactionBlock()->setUseSelectAll(false);
    $this->getMassactionBlock()->addItem('print_shipmentpdf', array(
    'label'=> Mage::helper('sales')->__('Expédier'),
    'url'  => $this->getUrl('envoimoinscher/sales/init', array("type" => "forceCheck", "source" => "noemc")),
    ));
    return $this;
  }

  public function getGridUrl()
  {
    return $this->getUrl('envoimoinscher/sales/index', array('_current'=>true));
  }

  public function getRowUrl($row)
  {
    if(Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) { 
      return $this->getUrl('envoimoinscher/sales/send', array('order_id' => $row->getId()));
    }
    return false;
  }

  public function addRssList($url, $label) {}
  public function addExportType($url, $label) {}
  public function getMainButtonsHtml() {}
  public function getSearchButtonHtml() {}

}