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

class Boxtale_Envoimoinscher_Model_Emc_Dispo_End extends Mage_Core_Model_Abstract 
{

  protected $_dispo = array('17:00' => '17:00', '17:15' => '17:15', '17:30' => '17:30', '17:45' => '17:45', '18:00' => '18:00', '18:15' => '18:15', '18:30' => '18:30', '18:45' => '18:45',
  '19:00' => '19:00', '19:15' => '19:15', '19:30' => '19:30', '19:45' => '19:45', '20:00' => '20:00', '20:15' => '20:15', '20:30' => '20:30', '20:45' => '20:45', 
  '21:00' => '21:00');

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_dispo_end');
  }

  /** 
   * Order delay priorities. 
   * @return array List with priorities.
   */
  public function toOptionArray() 
  {
    return $this->_dispo;
  }

  public function getDispo($key)
  {
    return $this->_dispo[$key];
  }

}
?>