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
 * Boxtale_Envoimoinscher : shipping dimensions.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Dimensions extends Mage_Core_Model_Abstract 
{ 

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_dimensions');
  }

  
  /** 
   * Method loads dimensions to edit.
   * @return array Dimensions list.
   */ 
  public function getDimensions() 
  {
    $arr = array();
    $collection = $this->getCollection()->loadData()->setOrder("weight_ed", "ASC");
    return $collection->getData();
  }

  /** 
   * Retreives dimensions by passed weight.
   * @return array Dimensions database row.
   */
  public function getByWeight($weight)
  {
    return $this->getCollection()
                ->addFieldToFilter("weight_ed", array('gteq' => $weight))
                ->addFieldToFilter("weight_from_ed", array('lt' => $weight))
                ->setOrder("weight_ed", "ASC")
                ->setPageSize(1)
                ->loadData()
                ->getData();
  }

}

?>