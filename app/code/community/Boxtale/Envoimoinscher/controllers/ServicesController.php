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

class Boxtale_Envoimoinscher_ServicesController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Action which handles services table.
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
    $srvId = (int)$this->getRequest()->getParam('id');

    // get operator info and configuration array
    $service = Mage::getModel('envoimoinscher/emc_services')->load($srvId)->getData();
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");

    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $block->setData('service', $service);
    $block->setData('editurl', $this->getUrl('envoimoinscher/services/update', array('id' => $srvId)));
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
      $srvId = $this->getRequest()->getParam('id');
      $postData = $this->getRequest()->getPost();
      // update pricing type
      Mage::getModel('envoimoinscher/emc_services')->load($srvId)->addData(
      array('label_store_es' => $postData['srvName'], 'desc_store_es' => $postData['srvDesc']
      , 'tracking_es' => $postData['srvTracking']))->save();
    }
    Mage::getSingleton('core/session')->setOpeAction(true);
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/services/edit', array('id' => $srvId)));
  }

}