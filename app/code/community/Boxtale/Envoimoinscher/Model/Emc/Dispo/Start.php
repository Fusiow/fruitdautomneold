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

class Boxtale_Envoimoinscher_Model_Emc_Dispo_Start extends Mage_Core_Model_Abstract 
{

  protected $_dispo = array('12:00' => '12:00', '12:15' => '12:15', '12:30' => '12:30', '12:45' => '12:45', '13:00' => '13:00', '13:15' => '13:15', '13:30' => '13:30', '13:45' => '13:45',
  '14:00' => '14:00', '14:15' => '14:15', '14:30' => '14:30', '14:45' => '14:45', '15:00' => '15:00', '15:15' => '15:15', '15:30' => '15:30', '15:45' => '15:45', '16:00' => '16:00', 
  '16:15' => '16:15', '16:30' => '16:30', '16:45' => '16:45', '17:00' => '17:00');

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_dispo_start');
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