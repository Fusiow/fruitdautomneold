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
 * Boxtale_Envoimoinscher : block with error notifications.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
class Boxtale_Envoimoinscher_Block_Notifications extends Mage_Adminhtml_Block_Template 
{

  /**
   * Determines if notice has to be shown.
   * @return void
   */
  public function showNotification()
  {
    if(!$this->checkAvailability())
    {
      $layout = Mage::getSingleton('core/layout');
      $head   = $layout->getBlock('notifications');
      $block = $this->getLayout()->createBlock('Mage_Core_Block_Template',
      'blockname', array('template' => 'boxtale/envoimoinscher/notifications/block.phtml'));
      $head->append($block);
    }
  }

  /**
   * Checks availability of EnvoiMoinsCher.com . If site isn't available, shows notice on backend.
   * This actions is executed every 5 pages.
   * @return boolean True if service is available, false if not
   */
  public function checkAvailability()
  {
    $count = Mage::getSingleton('core/session')->getAvailCounter();
    if($count == 5)
    {
      Mage::getSingleton('core/session')->setAvailCounter(0);
      return Mage::helper('envoimoinscher')->checkAvailability();
    }
    else
    {
      Mage::getSingleton('core/session')->setAvailCounter(($count+1));
      return true;
    }
  }


}

?>