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

class Boxtale_Envoimoinscher_Model_Emc_Environment extends Mage_Core_Model_Abstract 
{
 
  protected $_hostsEmc = array("TEST" => array("alias" => "de test", "host" => "https://test.envoimoinscher.com/"),
  "PROD" => array("alias" => "de production", "host" => "https://www.envoimoinscher.com"));

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_environment');
  }

  /** 
   * Order delay priorities. 
   * @return array List with priorities.
   */
  public function toOptionArray() 
  {
    $hosts = array();
    foreach($this->_hostsEmc as $h => $host)
    {
      $hosts[$h] = $host['alias'];
    }
    return $hosts;
  }

  /** 
   * Gets host alias corresponding to alias expected by EnvoiMoinsCher API.
   * @return string API alias
   */
  public function getHost($key)
  {
    return $this->_hostsEmc[$key]['host'];
  }


} 

?>