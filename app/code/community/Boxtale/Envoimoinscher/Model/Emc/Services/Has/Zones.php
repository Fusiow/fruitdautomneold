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
 * Boxtale_Envoimoinscher : operators' zones.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Services_Has_Zones extends Mage_Core_Model_Abstract 
{

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_services_has_zones');
  } 

  /** 
   * Get all prices. Returned array is regrouped by zones.
   * @return array Prices list.
   */ 
  public function getPrices() 
  {
    $arr = array();
    $collection = $this->getCollection()->loadData()->setOrder("emc_services_id_es ASC, emc_zones_id_ez ASC, value_eshz", "ASC");
    foreach($collection->getData() as $pricing) 
    {
      $n = count($arr[$pricing['emc_services_id_es']][$pricing['emc_zones_id_ez']]);
      $arr[$pricing['emc_services_id_es']][$pricing['emc_zones_id_ez']][$n] = array(
        'value' => $pricing['value_eshz'], 'price' => $pricing['price_eshz'], 'type' => $pricing['type_eshz'],
        'profitability' => $pricing['profitability_eshz']
      );
    }
    return $arr;
  }

  /** 
   * Get correspoding price (by weight or by order price).
   * @param float $price Order price.
   * @param int $weight Order weight.
   * @param array $services Array with services ids.
   * @param string $zone Delivery zone (FRAN for France or INTE for international)
   * @return array Price informations.
   */ 
  public function getByWeightOrPrice($price, $weight, $services, $zone)
  {
    $scales = $this->getCollection()->getSelect()
    ->where("emc_services_id_es IN (".$services.")")
    ->where("emc_zones_id_ez = $zone")
    ->where("(type_eshz = 0 AND value_from_eshz < ".(float)$weight." AND value_eshz >= ".(float)$weight.")  OR (type_eshz = 1 AND value_from_eshz < ".(float)$price." AND value_eshz >= ".(float)$price.")");
    $prices = array();
    foreach($scales->query() as $scale)
    {
      $prices[$scale['emc_services_id_es']] = array('price' => $scale['price_eshz'],
        'profitability' => $scale['profitability_eshz']);
    }
    return $prices;
  }

}
