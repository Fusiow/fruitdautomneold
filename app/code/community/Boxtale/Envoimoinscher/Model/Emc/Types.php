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

class Boxtale_Envoimoinscher_Model_Emc_Types extends Mage_Core_Model_Abstract 
{ 

  /** 
   * Package types used to get a cotation and order an offer.
   * @access protected
   * @var array
   */
  protected $_types = array('colis' => "colis",  'encombrant' => "encombrant", 'palette' => "palette", 
    'pli' => "pli" 
  );

  /** 
   * Returns array used by configuration page. 
   * @return array List with types.
   */
  public function toOptionArray() 
  {
    return $this->_types;
  }


}
?>