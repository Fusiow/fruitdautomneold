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
 * Boxtale_Envoimoinscher : anchor's table renderer.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
 
class Boxtale_Envoimoinscher_Block_Renderers_Sending extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{   
  public function render(Varien_Object $row)
  {
    $data = $row->getData(); 
    if(isset($data['ref_emc']))
    {
	  $bordLabel = 'Bordereau';
      if($data['parcels'] > 1) $bordLabel = 'Bordereaux';
      if((int)$data['label_state'] == 0)
      {
        return '<span id="info'.$row['entity_id'].'">'.$bordLabel.' en cours de génération <input type="hidden" name="bord[]" id="bord'.$data['entity'].'" class="bordLoad" value="'.$data['entity'].'" /></span> <a href="'.$this->getUrl('envoimoinscher/sales/print', array('order' => $row['entity_id'])).'" id="labelLink'.$row['entity_id'].'" style="display:none;">télécharger '.strtolower($bordLabel).'</a>';
      }
      return '<a href="'.$this->getUrl('envoimoinscher/sales/print', array('order' => $row['entity_id'])).'">télécharger '.strtolower($bordLabel).'</a>';
    }
    return '<a href="'.$this->getUrl('envoimoinscher/sales/send', array('order_id' => $row['entity_id'])).'">expédier</a>';
  }
}

?>