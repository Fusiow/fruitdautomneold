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
 * Boxtale_Envoimoinscher : labels controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_LabelsController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Make labels downloadables. Normally, we receive only one label by order.
   * @access public
   * @deprecated
   * @return void
   */
  public function downloadAction() 
  {
    // $orderId = (int)$this->getRequest()->getParam('order');
    // $labels = Mage::getModel('envoimoinscher/emc_documents')->getLabelsToOrder($orderId);
    // if(count($labels) == 0)
    // {
      // $this->loadLayout();
      // $this->renderLayout();
    // }
    // else
    // {
      // if(count($labels) > 1) 
      // {
// // TODO : if we will have multiple labels 
      // }
      // $path = explode("/", $labels[0]["link_ed"]);
      // $filename = explode('?', $path[count($path)-1]);
      // header("Content-type: application/pdf");
      // header('Content-Description: File Transfer');
      // header("Content-Disposition: attachment; filename=\"".$filename[0]."\"");
      // ob_clean();
      // flush();
      // readfile($labels[0]["link_ed"]);
    // }
  }

  public function checkAction()
  {
    $orderId = (int)$this->getRequest()->getPost('orderId');
    $orderData = Mage::getModel('envoimoinscher/emc_orders')->getOrderById($orderId);
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $orsClass = new Env_OrderStatus(array("user" => $moduleConfig["user"], "pass" => 
      $moduleConfig["mdp"], "key" => $moduleConfig["cle"])
    );
    $orsClass->server = $orderData[0]["base_url_eor"];
    if($orderData[0]["base_url_eor"] == "")
    {
      $orsClass->setEnv(strtolower($moduleConfig['environment']));
    }
    $orsClass->setGetParams(array("module" => "Magento", "version" => "1.0.4"));
    $orsClass->getOrderInformations($orderData[0]["ref_emc_eor"]);
    ob_end_clean();
    if(!$orsClass->curlError)
    {
      if((bool)$orsClass->orderInfo["labelAvailable"])
      {
        Mage::getModel('envoimoinscher/emc_documents')->updateLabelState($orderId, 1);
        $trackingModel = Mage::getModel('envoimoinscher/emc_tracking');
        Mage::getModel('envoimoinscher/emc_orders')->updateOpeReference($orderId, $orsClass->orderInfo["opeRef"]);
      }
      $orsClass->orderInfo["error"] = 0;
      $orsClass->orderInfo["labelUrl"] = $orderData[0]["link_ed"];
      $orsClass->orderInfo["labelAvailable"] = (int)$orsClass->orderInfo["labelAvailable"];
      echo json_encode($orsClass->orderInfo);
      die();
    }
    echo json_encode(array("error" => 1));
    die();




  }

}