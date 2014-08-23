<?php
// CREATE TABLE `emc_services_rules` (
// `id_esr` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
// `emc_services_id_es` INT( 3 ) NOT NULL ,
// `type_esr` INT( 1 ) NOT NULL COMMENT '0 - amount, 1 - pourcent',
// `from_esr` DECIMAL( 7, 2 ) NOT NULL ,
// `to_esr` DECIMAL( 7, 2 ) NOT NULL ,
// `value_esr` DECIMAL( 7, 2 ) NOT NULL ,
// `from_date_esr` DATETIME NOT NULL ,
// `to_date_esr` DATETIME NOT NULL ,
// `geo_esr` INT( 1 ) NOT NULL COMMENT '0 - everywhere, 1 - only French orders, 2 - only internation orders',
// INDEX ( `emc_services_id_es` )
// ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
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
 * Boxtale_Envoimoinscher : sales controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_RulesController extends Mage_Adminhtml_Controller_Action 
{

  /**
   * Display configuration page of EnvoiMoinsCher.com marketing operations.
   * @access public 
   * @return void
   */
  public function configureAction() 
  {
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $rulDb = Mage::getModel("envoimoinscher/emc_services_rules");
    $block->setData('types', $rulDb->getTypesList());
    $block->setData('geo', $rulDb->getGeoList());
    $block->setData('formlink', $this->getUrl('envoimoinscher/rules/save'));
    $block->setData('ruleresult', (int)Mage::getSingleton('core/session')->getRulesResult());
    // $block2 = $layout->getBlock('buttonsRules');
    $this->renderLayout();
    Mage::getSingleton('core/session')->setRulesResult(-1);
  }

  /**
   * Saves new shipping rules.
   * @access public
   * @return void
   */
  public function saveAction()
  {
    if($this->getRequest()->isPost()) 
    {
      $result = 1;
      $postData = $this->getRequest()->getPost();
	  // print_r($postData['delete']);die();
      $helper = Mage::helper("emc_validator");
      $i = 0;
      foreach($postData['operators'] as $s => $service)
      {
        foreach($postData['type'][$service] as $k => $type)
        {
          if(!isset($postData['delete'][$service][$k]))
          {
            $configuration[$i] = array('amountFrom' => (float)$postData['amount_from'][$service][$k],
              'amountTo' => (float)$postData['amount_to'][$service][$k],
              'value' => (float)$postData['value'][$service][$k],
              'geo' => (int)$postData['geo'][$service][$k],
              'validFrom' => $postData['valid_from'][$service][$k],
              'validTo' =>  $postData['valid_to'][$service][$k],
              'type' => $type,
              'service' => $service,
            );
            // test only : if($helper->validateRule($configuration[$i]))
            if(!$helper->validateRule($configuration[$i]))
            {
              Mage::getSingleton('core/session')->setRulesData($postData);
              $result = 2;
              break;
            }
            $i++;
          }
        }
      }
    }
    if($result == 1)
    {
      Mage::getSingleton('core/session')->unsetRulesData();
      Mage::getModel('envoimoinscher/emc_services_rules')->addRules($configuration);
    }
    Mage::getSingleton('core/session')->setRulesResult($result);
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/rules/configure'));
  }
}