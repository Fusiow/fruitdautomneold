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

class Boxtale_Envoimoinscher_Block_History_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('sales_grid');
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
    $emcOrders = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders');
    $emcOperators = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $emcDocuments = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_documents');
    $salesOrder = Mage::getSingleton('core/resource')->getTableName('sales/order');
    $collection = Mage::getResourceModel($this->_getCollectionClass());
    $this->setCollection($collection); 
    $this->_preparePage(); 
    $collection->getSelect()
    ->join(array('eo' => $emcOrders), 'eo.sales_flat_order_entity_id  = main_table.entity_id', 
      array('ref_emc' => 'ref_emc_eor', 'date_cmd_emc' => 'DATE_FORMAT(date_order_eor, "%d-%m-%Y à %H:%i")', 'date_coll_emc' => 'DATE_FORMAT(date_collect_eor, "%d-%m-%Y")', 'date_del_emc' => 'DATE_FORMAT(date_del_eor, "%d-%m-%Y")',
      'price_transp' => 'price_ttc_eor', 'srv_emc' => 'service_eor', 'parcels' => 'parcels_eor', 
      'time_diff' => '(UNIX_TIMESTAMP("'.date('Y-m-d H:i:s').'") - UNIX_TIMESTAMP(date_order_eor))'))
    ->join(array('eop' => $emcOperators), 'eo.emc_operators_code_eo = eop.code_eo', 
      array('ope_emc' => 'name_eo')) 
    ->join(array('or' => $salesOrder), 'or.entity_id  = main_table.entity_id', 
      array('entity' => 'entity_id', 'ship_price' => 'shipping_amount'))
    ->joinInner(array('ed' => $emcDocuments), 'ed.sales_flat_order_entity_id = main_table.entity_id',
      array('label_state' => 'state_ed'))
    ->where('or.shipping_method LIKE "envoimoinscher_%" AND eo.ref_emc_eor != ""')
    ->order('or.increment_id DESC')
    ->group(array('main_table.entity_id' )); 
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
    $this->addColumn('date_coll_emc', array('header'=> 'Date de <br />collecte prévue',
      'index' => 'date_coll_emc',
      'filter'   => false, 
      'sortable' => false,
      'width' => '50px'
    ));
    $this->addColumn('date_del_emc', array('header'=> 'Date de <br />livraison prévue',
      'index' => 'date_del_emc',
      'filter'   => false, 
      'sortable' => false,
      'width' => '50px'
    )); 
    $this->addColumn('status', array(
      'header' => Mage::helper('sales')->__('Status'),
      'index' => 'status',
      'type'  => 'options',
      'width' => '70px',
      'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
      'filter'   => false, 
      'sortable' => false,
    ));
 
    $this->addColumn('base_grand_total', array('header' => 'Coût transport <br />réel (TTC, €)',
      'index' => 'price_transp',
      'filter'   => false, 
      'sortable' => false,
      'type'  => 'currency',
      'currency' => 'order_currency_code',
      'width' => '50px',
      'align' => 'right'
    ));  
    $this->addColumn('ship_price', array('header' => 'Coût transport  <br />client (TTC, €)',
      'index' => 'ship_price',
      'filter'   => false, 
      'sortable' => false,
      'type'  => 'currency',
      'currency' => 'order_currency_code',
      'width' => '50px',
      'align' => 'right'
    ));  
    $this->addColumn('grand_total', array(
      'header' => 'Montant total',
      'index' => 'grand_total',
      'type'  => 'currency',
      'currency' => 'order_currency_code',
      'filter'   => false, 
      'sortable' => false,
    )); 
    $this->addColumn('ref_emc', array('header'=> 'Référence <br />EnvoiMoinsCher',
      'index' => 'ref_emc',
      'filter'   => false, 
      'sortable' => false,
      'width' => '90px'
    ));
    $this->addColumn('ope_emc', array('header'=> 'Transporteur <br /> (Offre)',
      'getter' => 'getId',
      'filter'   => false, 
      'sortable' => false,
      'renderer'  => 'Boxtale_Envoimoinscher_Block_Renderers_Service')
    );    
    $this->addColumn('created_at', array('header'=> 'Date commande <br />EnvoiMoinsCher',
      'index' => 'date_cmd_emc',
      'filter'   => false, 
      'sortable' => false,
      'width' => '50px'
    ));      
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
    'label'=> Mage::helper('sales')->__('Imprimer les bordereaux'),
    'url'  => $this->getUrl('envoimoinscher/sales/print', array('_current'=> false)),
    ));
    return $this;
  }

  public function getGridUrl()
  {
    return $this->getUrl('envoimoinscher/history/index', array('_current'=>true));
  }

  public function getRowUrl($row)
  {
    if(Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) { 
      return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
    }
    return false;
  }

  public function addRssList($url, $label) {}
  public function addExportType($url, $label) {}
  public function getMainButtonsHtml() {}
  public function getSearchButtonHtml() {}

}