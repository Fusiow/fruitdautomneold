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
 * Boxtale_Envoimoinscher : delivery informations controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_DeliveryController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Edit operator informations.
   * @access public
   * @return void
   */
  public function editAction() 
  {
    $orderId = $this->getRequest()->getParam('order_id');
    $data = $this->getRequest()->getPost(); print_r($data);
    
    $order = Mage::getModel('sales/order')->load($orderId);
    $address = $order->getShippingAddress(); //->getId();

    $newData = array(
      "firstname" => $data["prenom"],
      "lastname" => $data["nom"],
      "company" => $data["societe"],
      "street" => $data["adresse"],
      "city" => $data["ville"],
      "postcode" => $data["code_postal"],
      "telephone" => $data["tel"] 
    );
    $address->addData($newData);
    $address->save();
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/sales/send', array('order_id' => $orderId)));
  }

}