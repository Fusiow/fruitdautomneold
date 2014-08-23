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

class Boxtale_Envoimoinscher_Model_Emc_Services extends Mage_Core_Model_Abstract 
{

  const ZONE_FRANCE = 2;
  const ZONE_INTERNATIONAL = 1;
  const ZONE_EUROPE = 3;
  const PRICING_API = 0;
  const PRICING_SCALE = 1;
  const PROF_NOTSHOW = 0;
  const PROF_SHOWAPI = 1;
  const PROF_SHOWSCALE = 2;
  const FAM_SMALL_PARCELS = 1;
  const FAM_EXPRESS = 2;

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {
    $this->_init('envoimoinscher/emc_services', 'id_es');
  }


  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  // public function toOptionArray() 
  // {
    // $arr = array();
    // $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection()->getSelect()
    // ->join(array('eo' => 'emc_operators'), 'emc_operators_code_eo = eo.code_eo', array('operator' => 'name_eo'))
    // ->order("id_es", "ASC"); 
    // foreach($collection->query()  as $s => $service) 
    // {
      // $arr[] = array('value' => $service['id_es'], 'label' => "".$service['label_es']." (".$service['operator'].")");
    // }
    // return $arr;
  // } 

  /**
   * Returns services for specified zone. 
   * @access private
   * @return array List with services.
   */
  public function getServices($where)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $arr = array();
    $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection()->getSelect()
    ->join(array('eo' => $tableName), 'emc_operators_code_eo = eo.code_eo', array('operator' => 'name_eo'))
    ->where($where)
    ->order(array("eo.name_eo", "label_es"), array("ASC", "ASC")); 
    foreach($collection->query()  as $s => $service) 
    {
      $arr[] = array('value' => $service['id_es'], 'label' => "".$service['operator']." (".$service['label_es'].")");
    }
    return $arr;
  }

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showFrance() 
  {
    return $this->getServices('zones_es = '.self::ZONE_FRANCE);
  } 

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showInternational() 
  {
    return $this->getServices('zones_es != '.self::ZONE_FRANCE);
  }

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showFranceSmall() 
  {
    return $this->getServices('family_es = '.self::FAM_SMALL_PARCELS.' AND zones_es = '.self::ZONE_FRANCE);
  } 

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showInternationalSmall() 
  {
    return $this->getServices('family_es = '.self::FAM_SMALL_PARCELS.' AND zones_es != '.self::ZONE_FRANCE);
  }

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showFranceExpress() 
  {
    return $this->getServices('family_es = '.self::FAM_EXPRESS.' AND zones_es = '.self::ZONE_FRANCE);
  } 

  /** 
   * Used to show services list in the configuration page.
   * @return array Services list.
   */ 
  public function showInternationalExpress() 
  {
    return $this->getServices('family_es = '.self::FAM_EXPRESS.' AND zones_es != '.self::ZONE_FRANCE);
  }

  /** 
   * List all services.
   * @return array Services list.
   */ 
  public function listServices() 
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $arr = array();
    $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection()->getSelect()
    ->join(array('eo' => $tableName), 'emc_operators_code_eo = eo.code_eo', array('operator' => 'name_eo'))
    ->order("id_es", "ASC"); 
    foreach($collection->query() as $s => $service)
    {
      $arr[$service['id_es']] = array(
			'id' => $service['id_es'], 
			'value' => $service['code_es'], 
			'label' => $service['label_es'],
      'desc' => $service['desc_es'], 
			'opeCode' => $service['emc_operators_code_eo'],
      'zones' => $service['zones_es'], 
			'pricing' => $service['price_type_es'], 
			'label_store' => $service['label_store_es'],
      'desc_store' => $service['desc_store_es'], 
			'operator' => $service['operator'], 
			'family' => $service['family_es'],
			'pickup_point' => $service['is_parcel_point_es'], 
			'dropoff_point' => $service['is_parcel_dropoff_point_es']
      );
    }
    return $arr;
  }

  /** 
   * List all services.
   * @return array Services list.
   */ 
  public function getServicesListIn($list, $type = 'command') 
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $arr = array();
    $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection()->getSelect()
    ->join(array('eo' => $tableName), 'emc_operators_code_eo = eo.code_eo', array('operator' => 'name_eo', 'code' => 'code_eo'))
    ->where('id_es IN('.$list.')'); 
    // $collection = $this->getCollection()->loadData()->setOrder("label_es", "ASC");
    foreach($collection->query() as $s => $service)
    {
      if($type == 'command')
      {
        $arr[$service['id_es']] = $service['code'].$service['code_es'];
      }
      elseif($type == 'list')
      {
        $arr[$service['id_es']] = $service;
      }
    }
    return $arr;
  }

  /** 
   * Used to show informations about service and his operator.
   * @return array List with informations.
   */ 
  public function getByOpeSrv($ope, $srv) 
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_operators');
    $arr = array();
    $collection = Mage::getModel('envoimoinscher/emc_services')->getCollection()->getSelect()
    ->join(array('eo' => $tableName), 'emc_operators_code_eo = eo.code_eo', array('operator' => 'name_eo', 'mandatory_eo' => 'mandatory_eo'))
    ->where('code_es = ?', $srv)
    ->where('emc_operators_code_eo = ?', $ope);
    $result = $collection->query()->fetchAll();
    return $result[0];
  } 

  public function insertService($ope, $srv)
  {
    // check ope first 
    $opeData = Mage::getModel('envoimoinscher/emc_operators')->getOpeByCode($ope['code']);
    if(!isset($opeData[0]['id_eo']))
    {
      $opeData = array(0 => array('id_eo' => Mage::getModel('envoimoinscher/emc_operators')->insertOpe($ope)));
    }
    // insert new service
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services');
    $write = Mage::getSingleton("core/resource")->getConnection("core_write");
    $query = "INSERT INTO {$tableName} (code_es, emc_operators_code_eo, label_es,
    desc_es, desc_store_es, label_store_es, price_type_es, is_parcel_point_es, is_parcel_dropoff_point_es, zones_es, family_es)
    VALUES
    (:code, :opecode, :label, :desc, :descstore, :labelstore, :pricing, :pickuppoint, :dropoffpoint, :zones, :family)";
    $binds = array(
    'code'      => $srv['code'],
    'opecode'     => $ope['code'],
    'label'     => $srv['label'],
    'desc'     => $srv['desc'],
    'descstore'     => $srv['descstore'],
    'labelstore'     => $srv['labelstore'],
    'pricing'     => Boxtale_Envoimoinscher_Model_Emc_Services::PRICING_API,
    'pickuppoint'     => $srv["pickuppoint"],
    'dropoffpoint'     => $srv["dropoffpoint"],
    'zones'   => $srv['zones'],
    'family' => $srv["family"]);
    $write->query($query, $binds);
  }

  public function updateService($data, $srvCode, $opeCode)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services');
    $connection = Mage::getSingleton("core/resource")->getConnection("core_write");
    $condition = array(
        $connection->quoteInto('code_es = ?', $srvCode),
        $connection->quoteInto('emc_operators_code_eo = ?', $opeCode)
    );
    $connection->update($tableName, $data, $condition);
  }

  public function deleteService($srvCode, $opeCode)
  {
    $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_services');
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $condition = array(
        $connection->quoteInto('code_es = ?', $srvCode),
        $connection->quoteInto('emc_operators_code_eo = ?', $opeCode)
    );
    $connection->delete($tableName, $condition);

    // if no more offers for this $opeCode, remove the carrier too
    if($this->countServicesByOpe($opeCode) == 0)
    {
      Mage::getModel('envoimoinscher/emc_operators')->deleteOperatorByCode($opeCode);
    }
  }

  public function countServicesByOpe($opeCode)
  {
    return $this->getCollection()->addFieldToFilter("emc_operators_code_eo", array('eq' => $opeCode))->count();
  }

}

?>