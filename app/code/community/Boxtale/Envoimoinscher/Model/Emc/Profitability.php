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

class Boxtale_Envoimoinscher_Model_Emc_Profitability extends Mage_Core_Model_Abstract 
{
 
  protected $_profitabilities = array(0 => "Ne pas afficher l'offre", 1 => "Afficher l'offre au prix réel, dont le client sera facturé",
  2 => "Afficher quand même l'offre au prix fixe, dont le client sera facturé");

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_profitability');
  }

  /** 
   * Order delay priorities. 
   * @return array List with priorities.
   */
  public function toOptionArray() 
  {
    return $this->_profitabilities;
  }

  /** 
   * Gets priority alias corresponding to alias expected by EnvoiMoinsCher API.
   * @return string API alias
   */
  public function getPriority($key)
  {
    return $this->_priorities[(int)$key];
  }


} 

?>