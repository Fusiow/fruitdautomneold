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
 * Boxtale_Envoimoinscher : operators controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_OperatorsController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Action which handles operators table.
   * @access public
   * @return void
   */
  public function tableAction() 
  {
    // load layout and put view values into 
    $this->loadLayout();
    $layout = $this->getLayout();
    $blockCnt = $layout->getBlock('content');
    $this->renderLayout();
  }

  /** 
   * Edit operator informations.
   * @access public
   * @return void
   */
  public function editAction() 
  {
    $opeId = $this->getRequest()->getParam('code');

    // get operator info and configuration array
    $ope = Mage::getModel('envoimoinscher/emc_operators')->load($opeId)->getData();
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");

    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $block->setData('ope', $ope);
    $block->setData('issogep', $ope['code_eo'] == 'SOGP' ? true : false);
    $block->setData('editurl', $this->getUrl('envoimoinscher/operators/update', array('code' => $opeId)));
    $block->setData('parcelpoint', $moduleConfig['parcel.point']);
    $block->setData('moduleconfig', $moduleConfig);
    $block->setData('isedited', Mage::getSingleton('core/session')->getOpeAction());
    $this->renderLayout();
    Mage::getSingleton('core/session')->setOpeAction(false);
  }

  /** 
   * Save modified informations.
   * @access public
   * @return void
   */
  public function updateAction()
  {
    if($this->getRequest()->isPost()) 
    {
      $opeId = $this->getRequest()->getParam('code');
      $postData = $this->getRequest()->getPost();
      // update pricing type
      Mage::getModel('envoimoinscher/emc_operators')->load($opeId)->addData(
      array('name_store_eo' => $postData['opeName'], 'desc_store_eo' => $postData['opeDesc']))->save();
      // if($opeId == 'SOGP')
      // {
        // $conDb = Mage::getModel('core/config');
        // $conDb ->saveConfig('carriers/envoimoinscher/parcel.point', $postData['opePoint'], 'default', 0);
      // }
    }
    Mage::getSingleton('core/session')->setOpeAction(true);
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/operators/edit', array('code' => $opeId)));
  }

}