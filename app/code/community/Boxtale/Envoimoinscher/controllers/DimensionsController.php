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
 * Boxtale_Envoimoinscher : dimensions controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_DimensionsController extends Mage_Adminhtml_Controller_Action 
{

  /** 
   * Define dimensions used to get a cotation.
   * @access public
   * @return void
   */
  public function tableAction() 
  {
    // init DB classes
    $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
    $helper = Mage::helper("envoimoinscher");    

    // load layout and put view values into 
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('dimensionsLine');
    $block->setData('dimensions', $dimDb->getDimensions());
    $block->setData('showdimmessage', (bool)Mage::getSingleton('core/session')->getDimsAction());
    $this->renderLayout();
    Mage::getSingleton('core/session')->setDimsAction(false);
  }

  /** 
   * Method which saves dimensions modifications.
   * @access public
   * @access public
   * @return void
   */
  public function saveAction() 
  {
    if($this->getRequest()->isPost()) 
    {
      $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
      // truncate scale table
      $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services_has_zones');
      $write = Mage::getSingleton('core/resource')->getConnection('core_write'); 
      $write->query("TRUNCATE TABLE $tableName");

      $postData = $this->getRequest()->getPost();
      $fromValue = 0;
      foreach($dimDb->getDimensions() as $dimension)
      {
        $data = array("weight_from_ed" => $fromValue, "weight_ed" => (int)$postData['weight_'.$dimension['id_ed']], 
        "length_ed" => (int)$postData['length_'.$dimension['id_ed']], "width_ed" => (int)$postData['width_'.$dimension['id_ed']], 
        "height_ed" => $postData['height_'.$dimension['id_ed']]
        );
        $dimDb->load($dimension['id_ed'])->addData($data)->save();
        $fromValue = $data['weight_ed'];
      } 
    }
    Mage::getSingleton('core/session')->setDimsAction(true);
    $this->getResponse()->setRedirect($this->getUrl('envoimoinscher/dimensions/table'));
  }

}