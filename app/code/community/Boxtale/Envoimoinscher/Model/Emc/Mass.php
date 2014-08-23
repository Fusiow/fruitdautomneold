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
 * Boxtale_Envoimoinscher : civilities container.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Mass extends Mage_Core_Model_Abstract 
{
  const WITHOUT_CHECK = 0;
  const WITH_CHECK = 1;

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_mass');
  }

  /** 
   * Check modes. 
   * @return array List with modes.
   */
  public function toOptionArray() 
  {
    return array(self::WITH_CHECK => "Avec vérification de chaque envoi", self::WITHOUT_CHECK => "Sans vérification des envois");
  }

  /**
   * Check if we can send all orderswith or without checking it.
   * @access public
   * @param int $config Config value (0 or 1, as both constants)
   * @return bool True if we cech all orders, false otherwise
   */ 
  public function mustCheck($config)
  {
    return ($config == self::WITH_CHECK);
  }
}
?>