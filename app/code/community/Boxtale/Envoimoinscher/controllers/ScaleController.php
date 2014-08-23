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
 * Boxtale_Envoimoinscher : pricing scale controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_ScaleController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Action which handles table pricing scale. 
   * @access public
   * @return void
   */
  public function tableAction() 
  {
    // init DB classes and Helper
    $opeDb = Mage::getModel("envoimoinscher/emc_operators");
    $scaDb = Mage::getModel("envoimoinscher/emc_operators_has_zones");
    $helper = Mage::helper("envoimoinscher");  

    // load layout and put view values into 
    $this->loadLayout();
    $layout = $this->getLayout();
    $blockCnt = $layout->getBlock('content');
    $blockCnt->setData('link', $this->getUrl('envoimoinscher/scale/save'));
    $blockCnt->setData('showscalemsg', (bool)Mage::getSingleton('core/session')->getScaleAction());
    $this->renderLayout();
    Mage::getSingleton('core/session')->setScaleAction(false);
  }

  /** 
   * Save scale pricing changes.
   * @access public
   * @return void
   */
  public function saveAction() 
  {
    if($this->getRequest()->isPost()) 
    {
      $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services_has_zones');
      $scaDb = Mage::getModel("envoimoinscher/emc_services_has_zones");
      // truncate scale table
      $write = Mage::getSingleton('core/resource')->getConnection('core_write'); 
      $write->query("TRUNCATE TABLE $tableName");

      $postData = $this->getRequest()->getPost();
	  // print_r($postData);

      // get database zones
      $zonDb = Mage::getModel("envoimoinscher/emc_zones");
      $zones = $zonDb->getZones();

      // get database operators
      $srvDb = Mage::getModel("envoimoinscher/emc_services");

      // make scale for each operator
      $helper = Mage::helper("envoimoinscher");
      $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
      $srvCommons = $helper->makeCommonServices(array_merge(explode(',', $helper->constructServicesString($moduleConfig['services_fran_small'], $moduleConfig['services_fran_ex'])), explode(',', $helper->constructServicesString($moduleConfig['services_inte_small'], $moduleConfig['services_inte_ex']))));
      foreach($srvCommons as $s => $service)
      {
        if($service != "")
        {
          // do it only for precized prices (not for real pricing)
          // if((int)$postData['type_'.$service] == 1)
          if((int)$postData['type_'.$service] == Boxtale_Envoimoinscher_Model_Emc_Services::PRICING_SCALE)
          {
            foreach($zones as $zone)
            {
              $fromValue = 0;
			  $typePrice = (int)$postData['def_'.$service.'_'.$zone['code_ez']];
              $profitability = (int)$postData['profitability_'.$service.'_'.$zone['code_ez']];
              for($i = 0; $i < $postData['lines_price_'.$service.'_'.$zone['code_ez']]; $i++)
              {
                $prefix = $service.'_'.$zone['code_ez'].'_'.$i;
                // if all fields are filled up, we can insert new line into scale database
                if($postData['value_'.$prefix] != "" && $postData['price_'.$prefix] != "")
                {
                  $data = array("emc_services_id_es" => $service, "emc_zones_id_ez" => $zone['id_ez'], 
                  "value_from_eshz" => $helper->normalizeToFloat($fromValue), "value_eshz" => $helper->normalizeToFloat($postData['value_'.$prefix]),
                  "price_eshz" => (float)$postData['price_'.$prefix], "type_eshz" => $typePrice,
                  "profitability_eshz" => $profitability);
                  $scaDb->setData($data)->save();
                  $fromValue = $postData['value_'.$prefix];
                }   
              }
            }
          }
          // update pricing type
          $srvDb->load($service)->addData(array('price_type_es' => (int)$postData['type_'.$service]))->save();
        }
      }
    }
    Mage::getSingleton('core/session')->setScaleAction(true);
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/scale/table'));
  }

}