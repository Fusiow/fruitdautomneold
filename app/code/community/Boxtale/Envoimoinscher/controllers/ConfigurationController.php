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
 * Boxtale_Envoimoinscher : configuration controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_ConfigurationController extends Mage_Adminhtml_Controller_Action 
{     

  /**
   * Configuration method. It checks if the module is launched for the first time and preconfigure it with
   * default data.
   * If it's not the first launch, we pass directly to shippment configuration page.
   * @access public
   * @return void
   */
  public function configureAction() {
    // if this plugin is launched for the first time, we fill up with the default values
    $moduleConfig = Mage::getStoreConfig('carriers/envoimoinscher');
    if((int)$moduleConfig['first_launch'] == 0)
    {
      $this->loadLayout(array('default'));
      $this->renderLayout();
      $shippingValues = Mage::getStoreConfig('shipping');
      $storeValues = Mage::getStoreConfig('general/store_information');
      $emailValues = Mage::getStoreConfig('trans_email');
      $flatValues = Mage::getStoreConfig('carriers/flatrate');
      // open and parse xml file by setting the default values
      $dom = new DOMDocument;
      $dom->load(dirname(__FILE__).'/../etc/config.xml');
      $s = simplexml_import_dom($dom);
      $s->default->carriers->envoimoinscher->first_name = $emailValues['ident_general']['name'];
      $s->default->carriers->envoimoinscher->address = $storeValues['address'];
      $s->default->carriers->envoimoinscher->postal_code = $shippingValues['origin']['postcode'];
      $s->default->carriers->envoimoinscher->telephone = $storeValues['phone'];
      $s->default->carriers->envoimoinscher->mail_account = $emailValues['ident_general']['email'];
      $s->default->carriers->envoimoinscher->sallowspecific = $flatValues['sallowspecific'];
      $s->default->carriers->envoimoinscher->specificcountry = $flatValues['specificcountry'];
      $returned = $s->asXML();
      file_put_contents(dirname(__FILE__).'/../etc/config.xml', $returned);
      // update config test value
      $conDb = Mage::getModel('core/config');
      $conDb ->saveConfig('carriers/envoimoinscher/first_launch', 1, 'default', 0);
      $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/carriers"));
    }
    else 
    {
      $this->getResponse()->setRedirect($this->getUrl('adminhtml/system_config/edit/section/carriers'));
    }
  }
}