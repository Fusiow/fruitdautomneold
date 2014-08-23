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

class Boxtale_Envoimoinscher_PointsController extends Mage_Core_Controller_Front_Action 
{

  /**
   * Load parcel points.
   * @access public
   * @return void
   */
  public function getAction()
  {
    $address = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress();
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $poiCl = new Env_ParcelPoint(array("user" => $moduleConfig["user"], "pass" => 
    $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    $poiCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $poiCl->constructList = true;
    $opeCode = "";
$params = explode("_", $this->getRequest()->getParam('code'));
$method = "getPoints".$params[1];
    foreach(Mage::getSingleton('core/session')->$method() as $point)
    {
      $poiCl->getParcelPoint("dropoff_point", $point, $address->getCountryId());
      $ex = explode("-", $point);
      $opeCode = trim($ex[0]);
    }
    $this->getResponse()->setBody(
    $this->getLayout()->createBlock('core/template')
    ->setTemplate('envoimoinscher/points/get.phtml')
    ->setData('points', $poiCl->points["dropoff_point"])
    ->setData('ope', $opeCode)
    ->setData('id', time())
    ->setUseAjax(true)
    ->toHtml());
  }

} 