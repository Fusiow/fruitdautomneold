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
 * Boxtale_Envoimoinscher : sales - buttons.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
class Boxtale_Envoimoinscher_Block_Adminhtml_Buttons_Sale extends Mage_Adminhtml_Block_Widget_Container
{

  public function __construct()
  {
    parent::__construct();
    $this->_addButton('save', array(
    'label'     => 'Expédier',
    'onclick'   => 'editForm.submit();',
    'class'     => 'save',
    ), 1);
    $this->_addButton('back', array(
    'label'     => Mage::helper('adminhtml')->__('Back'),
    'onclick'   => 'setLocation(\'' . $this->getUrl('envoimoinscher/sales/index') . '\')',
    'class'     => 'back',
    ), -1);
  } 

}