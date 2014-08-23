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
 * Boxtale_Envoimoinscher : helper.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */


class Boxtale_Envoimoinscher_Helper_Sender_Data extends Mage_Core_Helper_Abstract
{

  private $orders, $orderInfo, $dbOrder = array();
  private $orderData = array(
    "shipper" => array(), "receiver" => array(), "params" => array(),
    "proforma" => array(), "shipment" => array()
  );
  private $orderId = 0;
  private $successOrder = false;
  private $errorMessage = "";
  private $source = "emc";

  protected $_postMapping = array("retrait_pointrelais" => "retrait.pointrelais", "depot_pointrelais" => "depot.pointrelais",
    "disponibilite_HDE" => "disponibilite.HDE", "disponibilite_HLE" => "disponibilite.HLE", "{type}_valeur" => "{type}.valeur",
    "{type}_description" => "{type}.description", "{type}_valeur" => "valeur_declaree.valeur", "collecte" => "collecte"
  );

  protected $_deliveryPostKeys = array("civilite", "prenom", "nom", "adresse", "ville", "infos", "code_postal", "societe",
  "email", "tel");
  
  protected $_obligatoryToQuote = array("collecte");
  private $trackingCode; // used to identify order when tracking data is submitted from EMC.com
  
  public function __construct()
  {
    $this->orders = Mage::getSingleton('core/session')->getMassShipment();
  }

  public function constructOrders($params)
  {
    $orders = array("orders" => array("todo" => $params, "done" => array()),
      "results" => array("total" => count($params), "ok" => 0, "errors" => 0, "skipped" => 0),
      "errors" => array()
    );
    Mage::getSingleton('core/session')->setMassShipment($orders);
    Mage::getSingleton('core/session')->setMassEnd(false);
  }
  
  public function prepareOrder($postParams = array())
  {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $helper = Mage::helper("envoimoinscher");
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    // get informations about order
    $salesOrder = Mage::getSingleton('sales/order');
    $ordDb = $salesOrder->load($this->orderId);
    // $order = $salesOrder->getData();
    $this->dbOrder = $salesOrder->getData();
    $address = $salesOrder->getShippingAddress()->getData();
    // get dimensions by order weight and compose parcels
    $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
    $baseWeight = $this->dbOrder["weight"];
    if(isset($postParams["weight"])) $baseWeight = $postParams["weight"];
    $parcelsPacks = array(1 => $baseWeight);
    $pa = 1;
    if(isset($postParams["parcel"]) && count($postParams["parcel"]) > 1)
    {
      foreach($postParams["parcel"] as $k => $postParcel)
      {
        $parcelsPacks[$pa] = (float)$postParcel;
        $pa++;
        unset($postParams["parcel"][$k]);
      }
    }
    $parcelLines = array();
    foreach($parcelsPacks as $k => $parcelPack)
    {
      $dimensions = $dimDb->getByWeight($parcelPack);
      $parcelLines[$k] = array("poids" => $parcelPack, "longueur" => $dimensions[0]['length_ed'], 
        "largeur" => $dimensions[0]['width_ed'], "hauteur" => $dimensions[0]['height_ed']
      );
    }
    $this->orderData["shipment"] = $parcelLines;

    $shipInfo = $helper->decomposeCode($this->dbOrder['shipping_method']);
    $opeSrvInfo = Mage::getModel('envoimoinscher/emc_services')->getByOpeSrv($shipInfo['ope'], $shipInfo['srv']);
    $defaultDelivery = array("pays" => $address['country_id'], "code_postal" => $address['postcode'], "ville" => $address['city'], 
    "type" => "particulier", "adresse" => $address["street"], "civilite" => "M", "prenom" => $address["firstname"], "nom" => $address["lastname"], 
    "email" => $this->dbOrder["customer_email"], "societe" => $address["company"], "tel" => $address["telephone"], "infos" => "");
    foreach($postParams as $postKey => $postValue)
    {
      if(in_array($postKey, $this->_deliveryPostKeys) && $postValue != "")
      {
        $defaultDelivery[$postKey] = $postValue; 
        unset($postParams[$postKey]);
      }
    }
    if($address["company"] != "")
    {
      $defaultDelivery["type"] = "entreprise";
    }
    $this->orderData["receiver"] = $defaultDelivery;
    // some default data
    $this->orderData["params"]["collecte"] = $helper->setCollectDate((int)$moduleConfig['pickup_date']);
    $this->orderData["params"]["delai"] = Mage::getModel('envoimoinscher/emc_delay')->getPriority($moduleConfig['priority']);
    $this->orderData["params"]["code_contenu"] = $moduleConfig["content"];
    $this->orderData["params"]["type_emballage.emballage"] = $moduleConfig["wrapping"];
    $this->orderData["params"]["valeur"] = (float)$this->dbOrder['subtotal'];
    $this->orderData["params"]["url_tracking"] = Mage::getUrl('envoimoinscher/tracking/update', array('code' => $this->getTrackingCode(), 'order_id' => $this->orderId));
    $this->orderData["params"][$moduleConfig["type"].".valeur"] = (float)$this->dbOrder['subtotal'];
    $this->orderData["params"][$moduleConfig["type"].".description"] = $helper->constructDesc($salesOrder->getAllItems());
    $this->orderData["params"]["version"] = Mage::helper('envoimoinscher')->getModuleInfoToApi("version");
    $this->orderData["params"]["module"] = Mage::helper('envoimoinscher')->getModuleInfoToApi("name");
    $this->orderData["params"]["operateur"] = $shipInfo['ope'];
    $this->orderData["params"]["service"] = $shipInfo['srv'];
    $this->orderData["params"]["assurance.selection"] = "off";
    $tmpDefault = $this->orderData["params"];
    $postRestParams = array();
    // data priority order : POST, SESSION, DB
    // AJAX request contains a POST parameter form_key
    if(count($postParams) > 1 && !isset($_GET["isAjax"]))
    {
      foreach($this->_postMapping as $postField => $apiField)
      {
        $fieldName = str_replace("{type}", $moduleConfig["type"], $postField);
        if(isset($postParams[$fieldName]))
        {
          $this->orderData["params"][str_replace("{type}", $moduleConfig["type"], $apiField)] = $postParams[$fieldName];
        }
        else
        {
          $postRestParams[$postField] = $postParams[$postField];
        }
      }
      unset($postParams);
    }
    elseif(count(Mage::getSingleton('core/session')->getEmcOrderData()) > 0)
    {
      $this->orderData = Mage::getSingleton('core/session')->getEmcOrderData();
      // some obligatory data for cotation (like pickup date) can be empty - fill it up
      foreach($this->_obligatoryToQuote as $quoteData)
      {
        if(!isset($this->orderData["params"][$quoteData]) || $this->orderData["params"][$quoteData] == "")
        {
          $this->orderData["params"][$quoteData] = $tmpDefault[$quoteData];
        }
      }
      unset($tmpDefault);
    }
    else
    {
      $deliveryPoint = "";
      if($opeSrvInfo['is_parcel_point_es'] == 1 || $shipInfo['ope'] == "LOCO" || $shipInfo['ope'] == "CHRP")
      {
        $row = Mage::getModel('envoimoinscher/emc_points')->getPoint($this->orderId);
        if(!isset($row[0]['point_ep']))
        {
          if(isset($row[0]['sales_flat_order_entity_id']))
          {
            // remove old and incomplete entry
            $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_points');
            $condition = array($connection->quoteInto('sales_flat_order_entity_id = ?', $this->orderId));
            $connection->delete($tableName, $condition);            
          }
          // first get associated quote by reserved_order_id field
          $quoteRow = Mage::getModel('sales/quote')->getCollection()->loadData()->addFieldToFilter("reserved_order_id", $ordDb->getIncrementId())->getData();
          $rowTmp = Mage::getModel('envoimoinscher/emc_points_tmp')->getPoint($quoteRow[0]['entity_id']);
          $point = explode('-', $rowTmp[0]['point_ept']);
          if(isset($point[1]) && trim($point[1]) != '')
          {
            $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_points_tmp');
            $condition = array($connection->quoteInto('sales_flat_quote_entity_id = ?', $quoteRow[0]['entity_id']));
            $connection->delete($tableName, $condition);
            Mage::getModel('envoimoinscher/emc_points')->setData(array('sales_flat_order_entity_id' => $this->orderId,
            'point_ep' => $point[1], 'emc_operators_code_eo' => trim($point[0])))->save();
            $deliveryPoint = $point[1];
          }
        }
        else
        {
          $deliveryPoint = $row[0]['point_ep'];
        }
        $pointCode = "depot.pointrelais_".strtolower($shipInfo["ope"]);
        if(isset($moduleConfig[$pointCode])) $this->orderData["params"]["depot.pointrelais"] = $moduleConfig[$pointCode];
        else $this->orderData["params"]["depot.pointrelais"] = 'POST';
        $this->orderData["params"]["retrait.pointrelais"] = $deliveryPoint;
      }
      $this->orderData["params"]["description"] = $helper->constructDesc($salesOrder->getAllItems());
      $this->orderData["params"]["valeur_declaree.valeur"] = (float)$this->dbOrder['subtotal'];
      $this->orderData["params"]["disponibilite.HDE"] = $moduleConfig["disponibilite.HDE"];
      $this->orderData["params"]["disponibilite.HLE"] = $moduleConfig["disponibilite.HLE"];
    }
    $this->orderData["params"]["type_emballage.emballage"] = $moduleConfig["wrapping"];
    $this->orderData["params"]["retrait.pointrelais"] = $shipInfo['ope'].'-'.$this->orderData["params"]["retrait.pointrelais"];
    $this->orderData["params"]["depot.pointrelais"] = $shipInfo['ope'].'-'.$this->orderData["params"]["depot.pointrelais"];
    // some informations can't be modified
    $this->orderData["shipper"] = array("civilite" => $moduleConfig["civility"], "prenom" => $moduleConfig["first_name"], "nom" => $moduleConfig["last_name"], 
    "email" => $moduleConfig["mail_account"], "tel" => $moduleConfig["telephone"], "infos" => $moduleConfig["complementary"],
    "pays" => "FR", "code_postal" => $moduleConfig["postal_code"], "ville" => $moduleConfig["city_name"], "type" => "entreprise", 
    "adresse" => $moduleConfig["address"], "societe" => $helper->getCompanyName($moduleConfig['company']));
    // constructs proforma
    if($address['country_id'] != "FR")
    {
      // sale state by default
      $this->orderData["params"]["raison"] = "sale";
      if(isset($postRestParams["envoi_raison"]))
      {
        $this->orderData["params"]["raison"] = $postRestParams["envoi_raison"];
      }
      $i = 1;
      $proformaPost = array();
      foreach($ordDb->getAllItems() as $item)
      {  
        $data = $item->getData();
        $descEn = $data["name"];
        $descFr = $data["name"];
        if(isset($postRestParams["desc_en_".$i]))
        {
          $descEn = $postRestParams["desc_en_".$i];
        }
        if(isset($postRestParams["desc_fr_".$i]))
        {
          $descFr = $postRestParams["desc_fr_".$i];
        }
        $proformaPost[$i] = array('description_en' => $descEn, 'description_fr' => $descFr, 
            'nombre' => $item->getQtyToShip(), 'valeur' => $data['price'], 'origine' => "FR", 'poids' => ((float)$data['weight']-0.1)
        );
        $i++;
      }
      $this->orderData["proforma"] = $proformaPost;
    }
  }
  
  public function order()
  {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    // get informations about order
    $salesOrder = Mage::getSingleton('sales/order');
    $ordDb = $salesOrder->load($this->orderId);
    $order = $salesOrder->getData();
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $cotCl = new Env_Quotation(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    // get module helper
		$helper = Mage::helper("envoimoinscher");
		
		$tracking = $helper->getTrackinData();
		$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		
    $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $cotCl->setPerson("expediteur", $this->orderData["shipper"]);
    $cotCl->setPerson("destinataire", $this->orderData["receiver"]);
    $cotCl->setType($moduleConfig["type"], $this->orderData["shipment"]);
    $order["shipment"] = $this->orderData["shipment"];
    if(count($this->orderData["proforma"]) > 0)
    {
      $cotCl->setProforma($this->orderData["proforma"]);
    }
// print_r($this->orderData["params"]);die();
    $orderPassed = $cotCl->makeOrder($this->orderData["params"], true);

    $doReinit = false;
    if($this->source != "errors" || (!$cotCl->curlError && !$cotCl->respError && $orderPassed))
    {
      $doReinit = true;
      $this->reinitOrdersArray();
    }
    if(!$cotCl->curlError && !$cotCl->respError && $orderPassed)
    {
      $order['serverUrl'] = $cotCl->server;
      $cotCl->order["trackingCode"] = $this->getTrackingCode();
      Mage::getModel('envoimoinscher/emc_orders')->makeEmcOrder($this->orderId, $order, $cotCl->order);
      if(ctype_alnum($this->orderData["params"]['retrait.pointrelais']))
      {
        $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_points');
        $connection->query('UPDATE '.$tableName.' SET point_ep = "'.$this->orderData["params"]['retrait.pointrelais'].'" WHERE sales_flat_order_entity_id = '.$this->orderId.'');
      }
      $this->orders["results"]["ok"]++;
      $this->successOrder = true;
      $this->orderInfo = array("ref" => $cotCl->order['ref']);
    }
    else 
    {
      $errorList = array();
      if($cotCl->curlErrorText != "")
      {
        $errorList[] = $cotCl->curlErrorText;
      }
      foreach($cotCl->respErrorsList as $error)
      {
        $errorList[] = $error["message"];
      }
      $errorMessage = implode("<br />", $errorList);
      Mage::getSingleton('core/session')->setEmcOrderData($this->orderData);
      $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_orders_errors');
      $condition = array($connection->quoteInto('sales_flat_order_entity_id = ?', $this->orderId));
      $connection->delete($tableName, $condition);
      Mage::getModel("envoimoinscher/emc_orders_errors")->setData(array('sales_flat_order_entity_id' => $this->orderId,
      'errors_eoe' => $errorMessage))->save();
      Mage::getSingleton('core/session')->setOrderError($errorMessage);
      $this->errorMessage = $errorMessage;
      $this->orders["results"]["errors"]++;
      $this->orders["errors"][] = array("id" => $this->orderId, "message" => implode("<br /> -", $errorList));
    }
    if($doReinit)
    {
      Mage::getSingleton('core/session')->setMassShipment($this->orders);
    }
  }

  public function skipOrder()
  {
    $this->reinitOrdersArray();
    $this->orders["results"]["skipped"]++;
    Mage::getSingleton('core/session')->setMassShipment($this->orders);
  }

  
  public function isSendBlock()
  {
    return (bool)(count($this->orders["orders"]["todo"]) > 0 || count($this->orders["orders"]["done"]) > 0);
  }

  public function isSuccessOrder()
  {
    return $this->successOrder;
  }
  
  public function getTotal()
  {
    return $this->orders["results"]["total"];
  }
  
  public function getDone()
  {
    return (int)($this->orders["results"]["ok"] + $this->orders["results"]["errors"] + $this->orders["results"]["skipped"]);
  }
  
  public function wasEnd()
  {
    return (bool)Mage::getSingleton('core/session')->getMassEnd();
  }
  
  public function setEnd()
  {
    Mage::getSingleton('core/session')->setMassEnd(true);
  }

  public function cancelOrders()
  {
    $this->cleanMemorizedData();
    Mage::getSingleton('core/session')->unsMassShipment();
    Mage::getSingleton('core/session')->unsMassEnd();
  }
  
  public function cleanMemorizedData()
  {
    Mage::getSingleton('core/session')->unsEmcOrderData();
  }
  
  public function setOrderId($orderId)
  {
    if($orderId > 0)
    {
      $this->orderId = $orderId;
    }
    elseif($orderId == 0 && count($this->orders["orders"]["todo"]) > 0)
    {
      $this->orderId = $this->orders["orders"]["todo"][0];
    }
  }

  public function getOrderInfo($key = "")
  {
    if($key != "" && isset($this->orderInfo[$key])) return $this->orderInfo[$key];
    return $this->orderInfo;
  }

  private function reinitOrdersArray()
  {
    if(isset($this->orders["orders"]["todo"]))
    {
      // order treated
      $key = array_keys($this->orders["orders"]["todo"], $this->orderId);
      // remove from todo
      unset($this->orders["orders"]["todo"][$key[0]]);
      // add to done
      $this->orders["orders"]["done"][] = $this->orderId;
      $ordersTodo = $this->orders["orders"]["todo"];
      $this->orders["orders"]["todo"] = array();
      $i = 0;
      foreach($ordersTodo as $t => $todoOrder)
      {
        $this->orders["orders"]["todo"][$i] = $todoOrder;
        $i++;
      }
      // independently of result, clean session data
      $this->cleanMemorizedData();
    }
  }
  
  public function getOrderId()
  {
    return $this->orderId;
  }
  
  public function getResult()
  {
    
  }

  public function getFinalResult()
  {
    $result = Mage::getSingleton('core/session')->getMassShipment();
    $this->cancelOrders();
    return $result;
  }

  public function setSource($value)
  {
    $this->source = $value;
  }

  public function isErrorSource()
  {
    return (bool)($this->source == "errors");
  }

  public function isDoOthers()
  {
    return (bool)(isset($this->orders["orders"]["todo"]) && count($this->orders["orders"]["todo"]) > 0);
  }

  public function isMassShipment()
  {
    return (bool)(isset($this->orders["orders"]["todo"]) && isset($this->orders["results"]["total"]));
  }
  
  public function getOrderData()
  {
    return $this->orderData;
  }

  public function getDbOrder()
  {
    return $this->dbOrder;
  }

  public function getErrorMessage()
  {
    return $this->errorMessage;
  }

  /**
   * Get id of next order to send.
   * @access public
   * @return int Next order id.
   */
  public function getNextOrderId()
  {
    return $this->orders["orders"]["todo"][0];
  }
  
  public function getOnlyQuotationParams()
  {
    $toUnset = array("operateur", "service", "raison", "disponibilite.HDE", "disponibilite.HLE", "depot.pointrelais", "retrait.pointrelais");
    $quoteParams = $this->orderData["params"];
    foreach($toUnset as $unset)
    {
      unset($quoteParams[$unset]);
    }
    return $quoteParams;
  }

  public function getWeight()
  {
    $weight = 0;
    foreach($this->orderData["shipment"] as $line)
    {
      $weight = $weight + $line["poids"];
    }
    return $weight;
  }
  
  private function getTrackingCode()
  {
    if($this->trackingCode == "") $this->trackingCode = sha1(time().rand(0, 1000));
    return $this->trackingCode;
  }
  
}