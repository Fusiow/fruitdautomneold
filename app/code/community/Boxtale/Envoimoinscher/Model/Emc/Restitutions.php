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
 * Boxtale_Envoimoinscher : pricing types.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Boxtale_Envoimoinscher_Model_Emc_Restitutions extends Mage_Core_Model_Abstract 
{
  const TAXES_INCL = 1;
  const TAXES_EXCL = 2;
  
  /**
   * Returns pricing types returned by API.
   * @return array Pricing types.
   */
  public function getPricesTypes()
  {
    return array(self::TAXES_INCL => 'TTC', self::TAXES_EXCL => 'HT');
  }

  /**
   * Gets default price type.
   * @return int Default price type
   */
  public function getDefaultApiKey()
  {
    return self::TAXES_INCL;
  }
  
  /**
   * Gets offer's price type key.
   * @access public
   * @param int $type User's choice.
   * @return String Corresponding price keyname.
   */
  public function getApiKey($type)
  {
    $keyname = 'tax-exclusive';
    if($type == self::TAXES_INCL)
    {
      $keyname = 'tax-inclusive';
    }
    return $keyname;
  }

  /** 
   * It takes care of delivery price restitution : live or fixed (scale pricing) restitution.
   * @return array Restitution possibilities.
   */ 
  public function toOptionArray() {
    $arr = array();
    $arr["api"] = "prix réel";
    $arr["scale"] = "grille tarifaire";
    return $arr;
  }

}  
?>