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
 * Boxtale_Envoimoinscher : availables operators.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Operators extends Mage_Core_Model_Abstract 
{ 

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_operators');
  }


  /** 
   * Used to show operators list in the configuration page.
   * @return array Operators list.
   */ 
  public function toOptionArray() 
  {
    $arr = array();
    $collection = $this->getCollection()->loadData()->setOrder("name_eo", "ASC");
    foreach($collection->getData() as $o => $operator) 
    {
      $arr[] = array('value' => $operator['code_eo'], 'label' => $operator['name_eo']);
    }
    return $arr;
  }

  /** 
   * List all operators.
   * @return array Operators list.
   */ 
  public function listOperators() 
  {
    $arr = array();
    $collection = $this->getCollection()->loadData()->setOrder("name_eo", "ASC");
    foreach($collection->getData() as $o => $operator) 
    {
      $arr[$operator['code_eo']] = array(
			'id' => $operator['id_eo'], 
			'value' => $operator['code_eo'], 
			'label' => $operator['name_eo'],
      'mandatory' => $operator['mandatory_eo']
      );
    }
    return $arr;
  }

public function getOpeByCode($code)
{
    return $this->getCollection()
                ->addFieldToFilter("code_eo", array('eq' => $code))
                ->setPageSize(1)
                ->loadData()
                ->getData();
}


  public function insertOpe($ope)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $write = Mage::getSingleton("core/resource")->getConnection("core_write");
    $query = "INSERT INTO {$tableName} (name_eo, code_eo, mandatory_eo) VALUES
    (:name, :code, :mandatory)";
    $binds = array(
    'name'      => $ope['label'],
    'code'     => $ope['code'],
    'mandatory'   => "");
    $write->query($query, $binds);
    return $write->lastInsertId();
  }

	public function updateOpe($opeCode,$data)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $connection = Mage::getSingleton("core/resource")->getConnection("core_write");
    $condition = array(
        $connection->quoteInto('code_eo = ?', $opeCode)
    );
    $connection->update($tableName, $data, $condition);
  }
	
  public function deleteOperatorByCode($opeCode)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $condition = array($connection->quoteInto('code_eo = ?', $opeCode));
    $connection->delete($tableName, $condition);
  }

}

?>