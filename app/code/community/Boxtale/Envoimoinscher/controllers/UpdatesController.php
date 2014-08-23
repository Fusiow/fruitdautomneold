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
 * Boxtale_Envoimoinscher : plugin updates controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_UpdatesController extends Mage_Adminhtml_Controller_Action 
{

  /**
   * Lists updates to install.
   * @access public 
   * @return void
   */
  public function listAction() 
  {
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('list');
    // check for updates (actually we make a simple parse of updates directory; don't use Magento upgrades - too hard for our users)
    $files = scandir(Mage::getBaseDir('app').'/code/local/Boxtale/Envoimoinscher/updates');
    $desc = array();
    for($f = 2; $f < count($files); $f++)
    {
      require_once(Mage::getBaseDir('app').'/code/local/Boxtale/Envoimoinscher/updates/'.$files[$f]);
      $desc[] = $description;
    }
		// update offers if asked to
		$offers_updated = array();
		if (isset($_GET['offers']))
		{
			$offers_updated = $this->updateOffers();
		}
		
    $block->setData('quantity', (count($files)-2));
    $block->setData('description', implode('<br />', $desc));
    $block->setData('result', Mage::getSingleton('core/session')->getUpdatesResult());
    $block->setData('urlexecute', $this->getUrl('envoimoinscher/updates/do'));
    $block->setData('urloffersexecute', $this->getUrl('envoimoinscher/updates/list').'?offers=1');
    $block->setData('offersupdated', $offers_updated);
    $this->renderLayout();
    Mage::getSingleton('core/session')->setUpdatesResult('');
  }
 
  /**
   * Executes update command.
   * @access public
   * @return void
   */
  public function doAction() 
  {
		$path = Mage::getBaseDir('app').'/code/local/Boxtale/Envoimoinscher/updates/';
		$files = scandir($path);
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		for($f = 2; $f < count($files); $f++)
		{
			require_once($path.$files[$f]);
			foreach($queries as $q => $query)
			{
				$tableSql = Mage::getSingleton('core/resource')->getTableName($tableName[$q]);
				$connection->query(str_replace('{TABLE_NAME}', $tableSql, $query));
			}
			@unlink($path.$files[$f]);
		}
		Mage::getSingleton('core/session')->setUpdatesResult(true);
		$this->getResponse()->setRedirect($this->getUrl('envoimoinscher/updates/list'));
  }

  /**
   * Executes operators updates (description modified, new operator or service added).
   * @access public
   * @return String JSON message
   */
  /*public function operatorsAction()
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $serClass = new Env_Service(array("user" => $moduleConfig["user"], "pass" => 
                               $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    $serClass->setEnv(strtolower($moduleConfig['environment']));
    $serClass->setParam(array("module" => "Magento", "version" => "1.0.4"));
    $serClass->setGetParams();
    $serClass->getServices();
    $offers = Mage::getModel('envoimoinscher/emc_services')->listServices();
    $installed = array();
    $checksums = array();
    $offersJson = array("added" => array(), "updated" => array(), "deleted" => array());
    $deleted = 0;
    $added = 0;
    $updated = 0;
    foreach($offers as $o => $offer)
    {
      $installed[$offer["opeCode"]."_".$offer["value"]] = $offer;
      $checksums[$offer["opeCode"]."_".$offer["value"]] = sha1($offer["desc"].$offer["desc_store"].$offer["zones"].$offer["family"]);
    }
    foreach($serClass->carriers as $c => $carrier)
    {
      foreach($carrier["services"] as $s => $service)
      {
        $code = $c."_".$service["code"];
        $exists = (bool)(isset($installed[$code]));
        $serviceInfos = array("label_backoffice" => "", "label_store" => "", 'offer_zoning' => Boxtale_Envoimoinscher_Model_Emc_Services::ZONE_FRANCE, "family_es" => "");
        if(isset($service["apiOptions"]["magento"]))
        {
          $serviceInfos = array("label_backoffice" => html_entity_decode($service["apiOptions"]["magento"]["label_backoffice"]),
          "label_store" => html_entity_decode($service["apiOptions"]["magento"]["label_store"]),
          "offer_zoning" => (int)$service["apiOptions"]["magento"]["offer_zoning"], "family_es" => (int)$service["apiOptions"]["magento"]["offer_family"]);
        }
        $srvChecksum = sha1($serviceInfos["label_backoffice"].$serviceInfos["label_store"].$serviceInfos["offer_zoning"].$serviceInfos["family_es"]);
        if(!$exists && $service["is_pluggable"])
        {
          // install new service and remove from $installed array
// echo "adding ".$carrier["code"]."_".$service["code"]."<br /><br />";
          $service["srvInfos"] = $serviceInfos;
          Mage::getModel('envoimoinscher/emc_services')->insertService($carrier, $service);
          $added++;
          $offersJson["added"][] = $service["label"];
        }
        elseif($exists && !$service["is_pluggable"]) 
        { 
// echo "deleting 1 ".$carrier["code"]."_".$service["code"]."<br /><br />";
          // uninstall carrier
          Mage::getModel('envoimoinscher/emc_services')->deleteService($service["code"], $carrier["code"]);
          $deleted++;
          $offersJson["deleted"][] = $service["label"];
        }
        elseif($exists && $checksums[$code] != $srvChecksum)
        {
          // new data available, must update
// echo "updating ".$carrier["code"]."_".$service["code"]."<br /><br />";
          $upData = array("desc_es" => $serviceInfos["label_backoffice"],
          "desc_store_es" => $serviceInfos["label_store"],
          "zones_es" =>  $serviceInfos["offer_zoning"], "family_es" => $serviceInfos["family_es"]);
          Mage::getModel('envoimoinscher/emc_services')->updateService($upData, $service["code"], $carrier["code"]);
          $updated++;
          $offersJson["updated"][] = $service["label"];
          unset($installed[$code]);
        }
        elseif($exists && isset($installed[$code]))
        {
// echo "exists, do nothing ".$carrier["code"]."_".$service["code"]."<br /><br />";
          unset($installed[$code]);
        }
      }
    }
   // clean up old services in Magento database
    foreach($installed as $code => $offer)
    {
// echo "deleting 2 ".$code."<br /><br />";
      $parts = explode('_', $code);
      Mage::getModel('envoimoinscher/emc_services')->deleteService($parts[1], $parts[0]);
      $deleted++;
      $offersJson["deleted"][] = $offer["label"];
    }
    ob_end_clean();
    header("Content-Type : application/json");
    echo json_encode(array("added" => $added, "updated" => $updated, "deleted" => $deleted,
        "addedOffers" => implode(",", $offersJson["added"]),
        "updatedOffers" => implode(",", $offersJson["updated"]),
        "deletedOffers" => implode(",", $offersJson["deleted"]))
    );
    die();
  }*/

  public function checkAction()  
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $result = array(); 
    $updates = (array)json_decode(file_get_contents(Mage::helper('envoimoinscher')->getSiteUrl($moduleConfig["environment"])."/api/check_updates.html?module=".Mage::helper('envoimoinscher')->getModuleInfoToApi("name")."&version=".Mage::helper('envoimoinscher')->getModuleInfoToApi("version")));
    foreach($updates as $u => $update)
    {
      $info = (array)$update;
      $result[] = array("version" => $u, "name" => $info["name"], "description" => $info["description"],
      "url" => Mage::helper('envoimoinscher')->getSiteUrl($moduleConfig["environment"]).$info["url"]); 
    }
    ob_end_clean();
    header("Content-Type : application/json");
    echo json_encode($result);
    die();
  }

	private function updateOffers()
	{		
		$result = array();
		$moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");

		// on recupere les services present
		$modelServices = Mage::getModel("envoimoinscher/emc_services");
		$modelOperators = Mage::getModel("envoimoinscher/emc_operators");
		$srv_save = $services =  $modelServices->listServices();
		$ope_save = $operators = $modelOperators->listOperators();
		
		// on recupere les services depuis le serveur envoimoinscher
		$lib = new Env_CarriersList(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    $helper = Mage::helper("envoimoinscher");
		$tracking = $helper->getTrackinData();
		$lib->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		$lib->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
		$lib->loadCarriersList($tracking['platform'],$tracking['module_version']);
				
		if($lib->curlError || $lib->respError) { 
			$result['errors'] = array();
			foreach($lib->respErrorsList as $m => $message) { 
				$result['errors'][] = $message["message"];
			}
		}
		
		if (isset($result['errors']))
		{
			return $result;
		}
		
		$ope_no_change = array();
		$ope_to_delete = array();
		$ope_to_update = array();
		$ope_to_insert = array();
		$srv_no_change = array();
		$srv_to_delete = array();
		$srv_to_update = array();
		$srv_to_insert = array();
		
		$op_found = -1;
		$srv_found = -1;
		
		// on tri les transporteurs a rajouter, supprimer, modifier et ceux restant identique
		$last_ope_seen = ""; // on evite les doublons
		foreach($lib->carriers as $carrier)
		{
			// on regarde si l'operateur est different
			if ($last_ope_seen != $carrier["ope_code"])
			{
				$last_ope_seen = $carrier["ope_code"];			
				// on compare l'operateur avec celui de la liste
				$op_found = -1;
				foreach($operators as $id => $operator)
				{
					if($operator["value"] == $carrier["ope_code"])
					{
						$op_found = $id;
						if ($operator["label"] != $carrier["ope_name"])
						{
							$ope_to_update[] = $carrier;
						}
						else
						{
							$ope_no_change[] = $carrier;
						}
						break;
					}
				}
				if ($op_found == -1)
				{
					$ope_to_insert[] = $carrier;
				}
				else
				{
					unset($operators[$op_found]);
				}
			}
			
			// on compare le service avec celui de la liste
			$srv_found = -1;
			foreach($services as $id => $service)
			{
				if($service["opeCode"] == $carrier["ope_code"] && $service["value"] == $carrier["srv_code"])
				{
					$srv_found = $id;
					// service trouve, on regarde s'il est different
					if ($service["label"] != $carrier["srv_name"] ||
							$service["desc"] != $carrier["label_store"] ||
							$service["desc_store"] != $carrier["description"] ||
							$service["label_store"] != $carrier["description_store"] ||
							$service["pickup_point"] != $carrier["parcel_pickup_point"] ||
							$service["dropoff_point"] != $carrier["parcel_dropoff_point"] ||
							$service["family"] != $carrier["family"] ||
							$service["zones"] != $carrier["zone"]
							)
					{
						$srv_to_update[] = $carrier;
					}
					else
					{
						$srv_no_change[] = $carrier;
					}
					break;
				}
			}
			if ($srv_found == -1)
			{
				$srv_to_insert[] = $carrier;
			}
			else
			{
				unset($services[$srv_found]);
			}
		}
		
		$srv_to_delete = $services;
		$ope_to_delete = $operators;
		
		// On met à jour la base
		
		// Requête insert operateurs
		if (count($ope_to_insert) > 0)
		{
			foreach($ope_to_insert as $operator)
			{
				$modelOperators->insertOpe(array(
					'label' => $operator['ope_name'],
					'code'  => $operator['ope_code']
				));
			}
		}
		// Requête insert services
		if (count($srv_to_insert) > 0)
		{
			foreach($srv_to_insert as $service)
			{
				$modelServices->insertService(
					array(
					'label' => $service['ope_name'],
					'code'  => $service['ope_code']
					),
					array(
					'code' => $service['srv_code'],
					'desc' => $service['label_store'],
					'descstore' => $service['description'],
					'label' => $service['srv_name'],
					'labelstore' => $service['description_store'],
					'pickuppoint' => $service['parcel_pickup_point'],
					'dropoffpoint' => $service['parcel_dropoff_point'],
					'zones' => $service['zone'],
					'family' => $service['family']
					)
				);
			}
		}
		
		// Requête update operateurs
		if (count($ope_to_update) > 0)
		{
			foreach($ope_to_update as $operator)
			{
				$modelOperators->updateOpe($operator['ope_code'],
					array(
						'name_eo' => $operator['ope_name']
					)
				);
			}
		}
		// Requête update services
		if (count($srv_to_update) > 0)
		{
			foreach($srv_to_update as $service)
			{
				$modelServices->updateService(
					array(
					'label_es' => $service['srv_name'],
					'desc_es' => $service['label_store'],
					'desc_store_es' => $service['description'],
					'label_store_es' => $service['description_store'],
					'is_parcel_point_es' => $service['parcel_pickup_point'],
					'is_parcel_dropoff_point_es' => $service['parcel_dropoff_point'],
					'zones_es' => $service['zone'],
					'family_es' => $service['family']
					),$service['srv_code'],$service['ope_code']
				);
			}
		}
		
		// Requête delete operateurs
		if (count($ope_to_delete) > 0)
		{
			foreach($ope_to_delete as $operator)
			{
				$modelOperators->deleteOperatorByCode($operator['value']);
			}
		}
		// Requête delete services
		if (count($srv_to_delete) > 0)
		{
			foreach($srv_to_delete as $service)
			{
				$modelServices->deleteService($service['value'],$service['opeCode']);
			}
		}
		
		if (count($srv_to_insert) > 0)
		{
			$result["offers_added"] = array();
			foreach($srv_to_insert as $service)
			{
				$result["offers_added"][] = $service["srv_name"];
			}
		}
		if (count($srv_to_update) > 0)
		{
			$result["offers_updated"] = array();
			foreach($srv_to_update as $service)
			{
				$result["offers_updated"][] = $service["srv_name"];
			}
		}
		if (count($srv_to_delete) > 0)
		{
			$result["offers_deleted"] = array();
			foreach($srv_to_delete as $service)
			{
				$result["offers_deleted"][] = $service["label"];
			}
		}
		
		return $result;
	}
	
}