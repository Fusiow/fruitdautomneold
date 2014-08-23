<?php
/*
    This file is part of EnvoiMoinsCher's shipping plugin for Prestashop.

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

class Boxtale_Envoimoinscher_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
  public function getCarrierName($code)
  {
    if($code == "envoimoinscher") return "";
    return parent::getCarrierName($code);
  }
  
  // @Override
  // Escaping method for HTML entities : with the native method we can't show 
  // "select your parcel point" link, placed after comment <!-- additional --> 
  public function escapeHtml($string)
  {
    $parts = explode("<!-- additional -->", $string);
    return parent::escapeHtml($parts[0])."<!-- additional --> ".$parts[1];
  }   
	
}