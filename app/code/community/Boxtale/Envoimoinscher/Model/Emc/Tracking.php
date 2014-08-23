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
 * Boxtale_Envoimoinscher : tracking informations.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Tracking extends Mage_Core_Model_Abstract 
{ 

  /** 
   * Labels used in tracking page.
   * @access protected
   * @var array
   */
  protected $_labels = array('CMD' => "commande de livraison passée", 
  'ENV' => "commande envoyée",
  'ANN' => "commande de livraison annulée",
  'LIV' => "commande livrée" 
  );

  /**
   * Tracking types for configuration page.
   */
  const TRACK_EMC_TYPE = 1;
  const TRACK_OPE_TYPE = 2;

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_tracking', 'id_et');
  }

  /** 
   * Retreives tracking informations about one order.
   * @return array Tracking informations
   */
  public function getTracking($orderId)
  {
    return $this->getCollection()
                ->addFieldToFilter("sales_flat_order_entity_id", $orderId)
                ->setOrder("id_et", "DESC")
                ->loadData()
                ->getData();
  }

  /** 
   * Retreives tracking label.
   * @return string Tracking label
   */
  public function getTrackingLabel($code)
  {
    return $this->_labels[$code];
  }

  /**
   * Gets tracking types to configuration page.
   * @access public
   * @return array Tracking types.
   */
  public function getTrackingTypes()
  {
    return array(self::TRACK_EMC_TYPE => "EnvoiMoinsCher", self::TRACK_OPE_TYPE => "Transporteur");
  }

  /**
   * Checks if user has EMC tracking mode activated.
   * @access public
   * @param int $config Tracking configuration.
   * @return boolean True if EMC is activated, false otherwise.
   */
  public function isEmcTracking($config)
  {
    return (bool)($config == self::TRACK_EMC_TYPE);
  }

}
?>