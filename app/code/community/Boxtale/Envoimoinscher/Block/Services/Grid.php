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
 * Boxtale_Envoimoinscher : services grid.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_Block_Services_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('services_grid');
    $this->setUseAjax(false); 
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
  }

  protected function _prepareLayout() {}

  protected function _prepareCollection()
  {
    $helper = Mage::helper('envoimoinscher');
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $servicesFr = explode(',', $helper->constructServicesString($moduleConfig['services_fran_small'], $moduleConfig['services_fran_ex']));
    $servicesIn = explode(',', $helper->constructServicesString($moduleConfig['services_inte_small'], $moduleConfig['services_inte_ex']));
    $services = array_merge($servicesFr, $servicesIn);
    foreach($services as $service)
    {
      $srv[] = "'$service'";
    }
    $servicesSql = implode(',', $srv);
    $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection();
    $this->setCollection($collection);
    $collection->getSelect()
    ->where('id_es IN ('.$servicesSql.')');
    $this->setCollection($collection);
    return $this;
  }
 
  protected function _prepareColumns()
  { 
    parent::_prepareColumns();
    $this->addColumn('label_es',
    array('header'=> "Nom du service",
    'index' => 'label_es',
    'filter'   => false,
    'sortable' => false,
    'width' => '50px',
    ));
    $this->addColumn('label_store_es',
    array('header'=> "Nom du service (site)",
    'index' => 'label_store_es',
    'filter'   => false,
    'sortable' => false,
    'width' => '50px',
    ));
    $this->addColumn('desc_es',
    array('header'=> 'Description',
    'index' => 'desc_es',
    'filter'   => false,
    'sortable' => false,
    'width' => '150px'
    ));
    $this->addColumn('desc_store_es',
    array('header'=> 'Description (site)',
    'index' => 'desc_store_es',
    'filter'   => false,
    'sortable' => false,
    'width' => '150px'
    ));
    $this->addColumn('tracking_es',
    array('header'=> 'Lien suivi',
    'index' => 'tracking_es',
    'filter'   => false,
    'sortable' => false,
    'width' => '300px'
    ));
    $this->addColumn('action', array(
    'header' => 'Action',
    'type' => 'action',
    'getter' => 'getId',
    'actions' => array(array('caption' =>  'Editer',
      'url' => array('base'=>'envoimoinscher/services/edit'), 
      'field' => 'id')),
    'sortable' => false,
    'filter' => false
    ));
  }

 
  protected function _prepareMassaction() {}

  public function getRowUrl($row)
  {
    return $this->getUrl('envoimoinscher/services/edit', array('id' => $row->getId()));
  }
}