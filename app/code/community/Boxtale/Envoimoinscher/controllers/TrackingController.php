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
 * Boxtale_Envoimoinscher : tracking controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_TrackingController extends Mage_Core_Controller_Front_Action 
{

  /**
   * Update tracking state. Called by EnvoiMoinsCher.com with protection code.
   * URL exemple : http://magentoemc/index.php/envoimoinscher/tracking/update?order_id=96&code=82336b&envoi=&etat=LIV&date=2011-05-10&localisation=Denver,%20USA&text=Colis%20sera%20bien%20livr%E9%20d%27ici%2010%20jours
   * @access public
   * @return void
   */
  public function updateAction()
  {
    $filterStandard = new Zend_Filter_StripTags(array('script', 'b' , 'em', 'u' , 'i' , 'br', 'strong' , 'a', 'p' , 'div' , 'span'),
                                                array('href' , 'class' , 'style')); 
    $helper = Mage::helper('envoimoinscher');
    $orderId = (int)$this->getRequest()->getParam('order_id');
    $orderCode = $this->getRequest()->getParam('code');
    $trackingShip = $this->getRequest()->getParam('envoi');
    $trackingState = $this->getRequest()->getParam('etat');
    $trackingDate = $filterStandard->filter($helper->decodeUrl($this->getRequest()->getParam('date'))); 
    $trackingText = $filterStandard->filter($helper->decodeUrl($this->getRequest()->getParam('text')));
    $trackingLocal = $filterStandard->filter($helper->decodeUrl($this->getRequest()->getParam('localisation')));
    $trackingRefOpe = $filterStandard->filter($this->getRequest()->getParam('ref_ope_colis'));
    $trackingInfo = $filterStandard->filter($helper->decodeUrl($this->getRequest()->getParam('envoi_infoexterne')));

    // check if get code corresponds to code in the database
    $salesOrder = Mage::getModel('envoimoinscher/emc_orders');
    $order = $salesOrder->getOrderById($orderId);
    if($order[0]['code_eor'] == $orderCode)
    {
      // insert new tracking informations
      Mage::getModel('envoimoinscher/emc_tracking')->setData(array('sales_flat_order_entity_id' => $orderId, 
      'state_et' => $trackingState, 'date_et' => $trackingDate, 
      'text_et' => $trackingText, 'localisation_et' => $trackingLocal))->save();
    }
    else
    {
      Mage::log("[TRACKING ERROR] Code de sécurité n'est pas correct pour le suivi de la commande $orderId.
Paramètres utilisés : code => $orderCode. IP : {$_SERVER['REMOTE_ADDR']}.");
    }
  }

} 