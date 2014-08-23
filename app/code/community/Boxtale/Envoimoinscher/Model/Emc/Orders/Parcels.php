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
 * Boxtale_Envoimoinscher : temporary data container.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Orders_Parcels extends Mage_Core_Model_Abstract 
{

  const MULTI_PARCEL = 1;

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_orders_parcels');
  }

  public function isMultiParcel($multiParcel)
  {
    return (bool)(self::MULTI_PARCEL == $multiParcel);
  }

  /** 
   * Retreives parcel lines for one order.
   * @return array Parcels
   */
  public function getParcels($orderId)
  {
    return $this->getCollection()
                ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                ->setOrder("number_eop", "DESC")
                ->loadData()
                ->getData();
  }

}
?>