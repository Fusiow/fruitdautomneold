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
 * Boxtale_Envoimoinscher : categories container.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Categories extends Mage_Core_Model_Abstract 
{

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_categories');
  }

  /** 
   * Fonction used to display all applicable contents.
   * @return array List with shipping contents.
   */
  public function toOptionArray() {
    $arr = array(0 => '-- Veuillez choisir --');
    $collection = $this->getCollection()->loadData()
                       ->addFieldToFilter("emc_categories_id_eca", array("neq" => 0))
                       ->setOrder("name_eca", "ASC"); 
    foreach ($collection->getData() as $key => $value) {
      $arr[$value["id_eca"]] = $value["name_eca"];
    }
    return $arr;
  }

} 

?>