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

class Boxtale_Envoimoinscher_Model_Emc_Services_Rules extends Mage_Core_Model_Abstract 
{
  
  const TYPE_PRICE = 0;
  const TYPE_POURCENT = 1;
  
  const TYPE_GEO_BOTH = 0;
  const TYPE_GEO_FR = 1;
  const TYPE_GEO_INTE = 2;

  const DATE_TO = 'Y-m-d H:i';
  const DATE_FROM = 'd-m-Y H:i';

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_services_rules', 'id_esr');
  }

  /**
   * Get rule types.
   * @access public
   * @return array Rule types.
   */
  public function getTypesList()
  {
    return array(self::TYPE_PRICE => '€', self::TYPE_POURCENT => '%');
  }

  /**
   * Get rule geo types.
   * @access public
   * @return array Rule geo types.
   */
  public function getGeoList()
  {
    return array(self::TYPE_GEO_BOTH => 'les deux', self::TYPE_GEO_FR => 'France',
    self::TYPE_GEO_INTE => 'international');
  }

  /**
   * Inserts new rules into database.
   * @access public
   * @param array $rules Rules to add.
   * @return void
   */
  public function addRules($rules)
  {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services_rules');
    $connection->delete($tableName, array());

    $query = "INSERT INTO {$tableName} (emc_services_id_es, type_esr, from_esr, to_esr, value_esr, from_date_esr, to_date_esr, geo_esr)
    VALUES
    (:service, :type, :from, :to, :value, :fromDate, :toDate, :geo)";
    foreach($rules as $r => $rule)
    {
      $binds = array
                   (
                     'service' => (int)$rule['service'],
                     'type' => (int)$rule['type'],
                     'from' => (float)$rule['amountFrom'],
                     'to' => (float)$rule['amountTo'],
                     'value' => (float)$rule['value'],
                     'fromDate' => date(self::DATE_TO, strtotime($rule['validFrom'])),
                     'toDate' => date(self::DATE_TO, strtotime($rule['validTo'])),
                     'geo' => (int)$rule['geo']
                  );
      $connection->query($query, $binds);
    }
  }
  /**
   * Prepares rules to be displayed in the form.
   * @access public
   * @return array
   */
  public function getRulesToForm()
  {
    $services = array();
    $rows = $this->getCollection()->loadData()
                                 ->getData();
    foreach($rows as $r => $row)
    {
      $services['type'][$row['emc_services_id_es']][] = $row['type_esr'];
      $services['amount_from'][$row['emc_services_id_es']][] = $row['from_esr'];
      $services['amount_to'][$row['emc_services_id_es']][] = $row['to_esr']; 
      $services['value'][$row['emc_services_id_es']][] = $row['value_esr'];
      $services['geo'][$row['emc_services_id_es']][] = $row['geo_esr'];
      $services['valid_from'][$row['emc_services_id_es']][] = date(self::DATE_FROM, strtotime($row['from_date_esr']));
      $services['valid_to'][$row['emc_services_id_es']][] = date(self::DATE_FROM, strtotime($row['to_date_esr']));
    }
    return $services;
  }
  
  /**
   * Gets rules by service.
   * @access public
   * @param array $cond Conditions to where clause (country to delivery zone, price to BETWEEN price).
   * @return array Services rules.
   */
  public function getRulesByServices($cond)
  { 
    $rules = array();
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services');
    $collection = Mage::getModel('envoimoinscher/emc_services_rules')->getCollection()->getSelect()
    ->join(array('es' => $tableName), 'emc_services_id_es = es.id_es', array('opeCode' => 'emc_operators_code_eo', 'srvCode' => 'code_es'));
    if(isset($cond['country']))
    {
      if($cond['country'] == "FR") $sqlZone = self::TYPE_GEO_FR;
      else $sqlZone = self::TYPE_GEO_INTE;
      $collection->where('geo_esr = '.$sqlZone.' OR geo_esr = '.self::TYPE_GEO_BOTH);
    }
    if(isset($cond['price']))
    {
      $collection->where('from_esr <= '.(float)$cond['price'].' AND to_esr >= '.(float)$cond['price']);
    }
    $collection->where('from_date_esr <= NOW() AND to_date_esr >= NOW()');
    $rows = $collection->query()->fetchAll();
// Mage::Log('Found SQL query '.$collection->__toString());
    foreach($rows as $r => $row)
    {
      $rules[$row['opeCode'].'_'.$row['srvCode']] = $row;
    }
    return $rules;
  }
  
  /**
   * Get new shipping amount.
   * @access public
   * @param array $rule Rules data.
   * @param float $shippingPrice 
   * @param string $key Key in rules array.
   * @return float New shipping amount.
   */
  public function getShippingAmount($rule, $shippingPrice, $key)
  {
    $reduc = 0.0;
    if($rule['type_esr'] == self::TYPE_PRICE)   
    {
      $reduc = (float)$rule['value_esr'];
    }
    elseif($rule['type_esr'] == self::TYPE_POURCENT)
    {
      $reduc = round((($rule['value_esr']*$shippingPrice)/100), 2);
    }
    if($reduc > 0)
    {
      $reducs = Mage::getSingleton('core/session')->getEmcDiscountRules();
      $reducs[$key] = $reduc;
      Mage::getSingleton('core/session')->setEmcDiscountRules($reducs);
    }
    $price = $shippingPrice - $reduc;
    return ($price < 0) ? 0 : $price;
  }
}