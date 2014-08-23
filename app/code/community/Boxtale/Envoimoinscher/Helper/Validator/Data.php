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
 * Boxtale_Envoimoinscher : helper.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */


class Boxtale_Envoimoinscher_Helper_Validator_Data extends Mage_Core_Helper_Abstract
{
  /**
   * Validates shipping rule before database insert.
   * @access public
   * @return boolean True if rule ok, false otherwise.
   */
  public function validateRule($rule)
  {
    $validRegex = "/\d\d\-\d\d-\d\d\d\d \d\d:\d\d/";
    if(!preg_match($validRegex, trim($rule['validFrom'])) || !preg_match($validRegex, trim($rule['validTo'])))
    {
      return false;
    }
    return true;
  }
}