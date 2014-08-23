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
 * Boxtale_Envoimoinscher : scale tabs.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
class Boxtale_Envoimoinscher_Block_Rules_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{ 
  public function __construct()
  {
    parent::__construct();
	$this->setId('product_info_tabs');
    $this->setDestElementId('rules_form');// pour placer l'élément dans un container
  }

  protected function _prepareLayout()
  {
    $srvDb = Mage::getModel("envoimoinscher/emc_services");
    $rulDb = Mage::getModel("envoimoinscher/emc_services_rules");
    // $scaDb = Mage::getModel("envoimoinscher/emc_services_has_zones");
    // get all referenced operators
    $helper = Mage::helper('envoimoinscher');
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $srvFran = explode(',', $helper->constructServicesString($moduleConfig['services_fran_small'], $moduleConfig['services_fran_ex']));
    $srvInte = explode(',', $helper->constructServicesString($moduleConfig['services_inte_small'], $moduleConfig['services_inte_ex']));
    $srvCommons = $helper->makeCommonServices(array_merge($srvFran, $srvInte));
    $services = $srvDb->getServicesListIn(implode(', ', $srvCommons), 'list');
    $types = $rulDb->getTypesList();
    $geo = $rulDb->getGeoList();

    $rules = Mage::getSingleton('core/session')->getRulesData();
    if(count($rules) == 0)
    {
      $rules = $rulDb->getRulesToForm();
    }
    Mage::getSingleton('core/session')->setRulesData(array());
    foreach($services as $s => $service)
    {
      $quantity = 0;
      if(isset($rules['type'][$service['id_es']])) $quantity = count($rules['type'][$service['id_es']]);
      $this->addTab($s, array(
        'label'     => $service['label_es'],
        'content'   => $this->getLayout()->getBlock('rulesOperators')
                                         ->setData('service', $service)
                                         ->setData('types', $types)
                                         ->setData('geo', $geo)
                                         ->setData('rules', $rules)
                                         ->setData('quantity', $quantity)
                                         ->setTemplate('boxtale/envoimoinscher/rules/operators.phtml')
                                         ->toHtml()
      ));
    }
  }

}