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
 * Boxtale_Envoimoinscher : observer.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */

class Boxtale_Envoimoinscher_Model_Observer
{

  /**
   * Pattern used to validate parcel point before the database insert.
   * @access private
   * @var string
   */
  private $pointPattern = '/[(A-Za-z0-9\-)+]/i';


  /**
   * Checks if user exists for this Magento store in EnvoiMoinsCher database. If not,
   * edits configuration file and put the error notice.
   * @param Varien_Event_Observer $observer Observer object
   * @return void
   */
  public function checkExists($observer)
  { 
    // get configuration informations
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $storeValues = Mage::getStoreConfig("general/store_information");
    $options = array(CURLOPT_URL => Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment'])."/verifier_utilisateur.html", CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query(array('login' => $moduleConfig['user'], 'key' => $moduleConfig['cle'], 'store' => Mage::getUrl(),
      'platform' => 'Magento', 'tested' => date('Y-m-d H:i:s'))), CURLOPT_RETURNTRANSFER => 1);
    // if(Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']) == 'https://test.envoimoinscher.com/')
    // {
      $options[CURLOPT_SSL_VERIFYPEER] = false; 
      $options[CURLOPT_SSL_VERIFYHOST] = 0; 
    // }
    $req = curl_init();
    curl_setopt_array($req, $options);
    $result = curl_exec($req);
    curl_close($req);
    $dom = new DOMDocument;
    $dom->load(dirname(__FILE__)."/../etc/system.xml");
    $s = simplexml_import_dom($dom);
    if(trim($result) != 1)
    {
      // api connection problem was detected (wrong credentials) 
      $s->sections->carriers->groups->envoimoinscher->fields->user->comment = "! Vos données de connexion ne sont pas correctes";
    }
    else
    {
      $s->sections->carriers->groups->envoimoinscher->fields->user->comment = "";
    }
    $returned = $s->asXML(); 
    file_put_contents(dirname(__FILE__)."/../etc/system.xml", $returned); 
  }

  /**
   * Synchronizes Magento configuration with EnvoiMoinsCher ones. 
   * @access public
   * @param Varien_Event_Observer $observer Observer instance
   * @return void
   */
  public function updateMailsConfig($observer)
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $userClass = new Env_User(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
    $userClass->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $mailsLabel = 1;
    $mailsNotif = 1;
    $mailsBill = 1;
    if(!isset($moduleConfig["mails_label"]) || (int)$moduleConfig["mails_label"] == 0) $mailsLabel = "";
    if(!isset($moduleConfig["mails_notif"]) || (int)$moduleConfig["mails_notif"] == 0) $mailsNotif = "";
    if(!isset($moduleConfig["mails_bill"]) || (int)$moduleConfig["mails_bill"] == 0) $mailsBill = "";
    $userClass->postEmailConfiguration(array("label" => $mailsLabel, "notification" => $mailsNotif, "bill" => $mailsBill));
  }

  /**
   * Synchronizes EnvoiMoinsCher e-mail configuration with Magento ones.
   * @access public
   * @param Varien_Event_Observer $observer Observer instance
   * @return void
   */
  public function getMailsConfig($observer)
  {
    if(Mage::app()->getRequest()->getControllerName() == "system_config" && Mage::app()->getRequest()->getParam("section") == "carriers" && Mage::getSingleton("core/session")->getMailsDone() != 1)
    {
      $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
      $userClass = new Env_User(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
      $userClass->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
      $userClass->getEmailConfiguration();
      $mailLabel = 1;
      $mailNotif = 1;
      $mailBill = 1;
      if(isset($userClass->userConfiguration["emails"]["label"]) && $userClass->userConfiguration["emails"]["label"] == "false") $mailLabel = 0;
      if(isset($userClass->userConfiguration["emails"]["notification"]) && $userClass->userConfiguration["emails"]["notification"] == "false") $mailNotif = 0;
      if(isset($userClass->userConfiguration["emails"]["bill"]) && $userClass->userConfiguration["emails"]["bill"] == "false") $mailBill = 0;
      $conDb = Mage::getModel('core/config');
      $conDb->saveConfig('carriers/envoimoinscher/mails_label', $mailLabel, 'default', 0);
      $conDb->saveConfig('carriers/envoimoinscher/mails_notif', $mailNotif, 'default', 0);
      $conDb->saveConfig('carriers/envoimoinscher/mails_bill', $mailBill, 'default', 0);
      Mage::getSingleton("core/session")->setMailsDone(1);


      Mage::app()->getResponse()->setRedirect(Mage::getUrl("adminhtml/system_config/edit/section/carriers", array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("system_config","edit"))));
    }
    else
    {
      Mage::getSingleton("core/session")->unsMailsDone();
    }
  }

  /**
   * For command automatic mode,  customer orders his shipment after 
   * accepted the last step on the store. In this moment we get the data about his order
   * (weight, addresses, shipment type) and call EnvoiMoinsChers' API.
   * It's executed only for immediate payment (like credit card, PayPal).
   * @param Varien_Event_Observer $observer Observer object
   * @return void
   */
  public function orderShipment($observer)
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");  
    $orderSess = Mage::getSingleton('checkout/session');
    $orderId = $orderSess->getLastOrderId();
    $orderModel = Mage::getModel('sales/order');
    $order = $orderModel->load($orderId); 
    $payment = $order->getPayment()->getData();

    // If parcel points delivery, insert parcel points informations for this order
    $point = explode('-', Mage::getSingleton('core/session')->getParcelPoint()); 
    // Log problem and send mail to administrator
    if(count($point) > 0 && ctype_alnum($point[0]) && ctype_alnum($point[1]))
    {
      Mage::getModel('envoimoinscher/emc_points')->setData(array('sales_flat_order_entity_id' => $orderId,
      'point_ep' => $point[1], 'emc_operators_code_eo' => trim($point[0])))->save();
    }
    $data = $order->getData();
    // Update order shipment method
    $shipInfo = Mage::helper("envoimoinscher")->decomposeCode($data['shipping_method']);
    
    $isEmc = (bool)(strpos($data['shipping_method'], "envoimoinscher") == 0);

    // Check if discount amount has to be applied
    $rules = Mage::getSingleton('core/session')->getEmcDiscountRules();
    if(isset($rules[$shipInfo['ope'].'_'.$shipInfo['srv']]) && $isEmc)
    {
Mage::Log('[EMC] Discount amount found'.$amount);
      $amount = $order->discount_amount+(float)$rules[$shipInfo['ope'].'_'.$shipInfo['srv']];
      $order->discount_amount = $amount;
      $order->save();
    }

    if($moduleConfig['order_mode'] == 'automatique' && strpos($data['shipping_method'], "envoimoinscher") !== false)
    {
      $mail = new Zend_Mail('UTF-8');        
      if($payment['method'] != 'checkmo')
      {
        $allItems = $order->getAllItems();
        $address = $order->getShippingAddress()->getData();
        $helper = Mage::helper("envoimoinscher");
        // $shipInfo = $helper->decomposeCode($data['shipping_method']);
        $storeName = $helper->getCompanyName($moduleConfig['company']);
  
        // get mandatory fields for this operator (will be removed when we will finish other recuperation of parcel points [not by ViaMichelin])
        $opeRow = Mage::getModel('envoimoinscher/emc_operators')->load($shipInfo['ope'])->getData();
        $mandatory = (array)unserialize($opeRow['mandatory_eo']);

        // get dimensions by order weight
        $dimDb = Mage::getModel("envoimoinscher/emc_dimensions");
        $dimensions = $dimDb->getByWeight($data['weight']);

        // order informations
        $quotInfo = array(
				"collecte" => $helper->setCollectDate((int)$moduleConfig['pickup_date']), 
        "delai" => "aucun", 
				"code_contenu" => $moduleConfig["content"],
				"type_emballage.emballage" => $moduleConfig["wrapping"],
          "url_tracking" => Mage::getUrl('envoimoinscher/tracking/update', array('code' => $data['protect_code'], 'order_id' => $data['entity_id'])),
        "operateur" => $shipInfo['ope'], 
				"service" => $shipInfo['srv'],  
				"valeur" => $data['subtotal'],
	      "assurance.selection" => "off", 
				"description" => $helper->constructDesc($allItems),
        "version" => Mage::helper('envoimoinscher')->getModuleInfoToApi("version"), 
				"module" => Mage::helper('envoimoinscher')->getModuleInfoToApi("name")
        );
        foreach($mandatory as $field)
        {
          $quotInfo[$field] = $moduleConfig[$field];
        }
        // Chronopost code (fixed: CHRP-POST)
        if(in_array('depot.pointrelais', $mandatory))
        {
          $quotInfo['retrait.pointrelais'] = Mage::getSingleton('core/session')->getParcelPoint();
          $quotInfo['depot.pointrelais'] = $shipInfo['ope'].'-'.$moduleConfig['depot.pointrelais'];
          if($shipInfo['ope'] == 'CHRP' && $quotInfo['depot.pointrelais'] == '')
          {
            $quotInfo['depot.pointrelais'] = 'CHRP-POST';
          }
        }
        $cotCl = new Env_Quotation(array("user" => $moduleConfig["user"], "pass" => 
          $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));      
				// get module helper
				$helper = Mage::helper("envoimoinscher");
				
				$tracking = $helper->getTrackinData();
				$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
									
        $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
        $cotCl->setPerson("expediteur", array("civilite" => $moduleConfig["civility"], "prenom" => $moduleConfig["first_name"], "nom" => $moduleConfig["last_name"], 
          "email" => $moduleConfig["mail_account"], "tel" => $moduleConfig["telephone"], "infos" => $moduleConfig["complementary"],
          "pays" => "FR", "code_postal" => $moduleConfig["postal_code"], "ville" => $moduleConfig["city_name"], "type" => "entreprise", 
          "adresse" => $moduleConfig["address"], "societe" => $storeName));
        $cotCl->setPerson("destinataire", array("prenom" => $address["firstname"], "nom" => $address["lastname"], "civilite" => "M", // civility is not important, we can pass whatever we want
          "email" => $data["customer_email"], "tel" => $address["telephone"],
          "pays" => $address['country_id'], "code_postal" => $address['postcode'], "ville" => $address['city'], "type" => "particulier", 
          "adresse" => $address['street']));

        // if delivery country is not France, we have to join the pro forma
        $totalWeight = $data['weight'];
        if($address['country_id'] != 'FR')
        {
          $quotInfo['raison'] = 'sale';
          $cotCl->setProforma($helper->makeProforma($allItems));
          // for avoid validation error, package weight must be bigger than items weight
          $totalWeight = $totalWeight + 0.1;
        }

        $cotCl->setType($moduleConfig["type"], array(1 => array("poids" => $totalWeight, "longueur" => $dimensions[0]['length_ed'], 
          "largeur" => $dimensions[0]['width_ed'], "hauteur" => $dimensions[0]['height_ed'])));

        $orderPassed = $cotCl->makeOrder($quotInfo, true);
        if(!$cotCl->curlError && !$cotCl->respError && $orderPassed) 
        {
          Mage::getModel('envoimoinscher/emc_orders')->makeEmcOrder($orderId, $data, $cotCl->order);
          $mail->setSubject("[CMD AUTOMATIQUE EnvoiMoinsCher.com] Une nouvelle commande réalisée");
          $mail->setBodyHtml("La commande automatique réalisée pour la commander numéro $orderId");
        }
        else
        {
          // Log problem and send mail to administrator
          Mage::log("[AUTOMATIC ORDER ERROR] Erreur pendant la réalisation de la commande automatique EnvoiMoinsCher.com : 
          Erreur de la requête : ".implode(', ', $cotCl->respErrorsList)."
          Erreur CURL : ".$cotCl->curlErrorText);
          $mail->setSubject("[CMD AUTOMATIQUE EnvoiMoinsCher.com] Erreur de réalisation");
          $mail->setBodyHtml("La commande automatique n'a pas été réalisée pour la commander numéro $orderId . Une erreur s'est produite.");
        }
      }
      elseif($payment['method'] == 'checkmo')
      {
        Mage::log("[AUTOMATIC ORDER ERROR] Erreur pendant la réalisation de la commande automatique EnvoiMoinsCher.com :
        Le client a décidé de payer en différé, la commande n'a pas pu être réalisée.
        Le numéro de la commande $orderId");
        $mail->setSubject("[CMD AUTOMATIQUE EnvoiMoinsCher.com] Erreur de réalisation");
        $mail->setBodyHtml("La commande automatique n'a pas été réalisée pour la commander numéro $orderId . Le client a choisi le mode de paiement en différé et le mode automatique fonctionne
        uniquement pour la commande payée immédiatement (carte bancaire, PayPal).");
      }

      $mail->setFrom($moduleConfig['mail_account'], $moduleConfig['first_name'].' '.$moduleConfig['last_name']);
      $mail->addTo($moduleConfig['mail_account'], $moduleConfig['first_name'].' '.$moduleConfig['last_name']);
      try 
      {
        $mail->send();
      }
      catch(Exception $ex) 
      {
        Mage::log("[SENDING MAIL ERROR] Erreur pendant l'envoi d'un e-mail pour confirmation de la commande automatique"); 
      }
    }
  }

  /**
   * Saves parcel point for the quote. We are sure that for every order a parcel point is defined.
   * @param Varien_Event_Observer $observer Observer object
   * @return void
   */
  public function saveParcelPoint($observer)
  {
    $quoteId = (int)Mage::getSingleton('checkout/session')->getQuoteId();
    $point = Mage::getSingleton('core/session')->getParcelPoint();
Mage::log('Boxtale/Envoimoinscher/Model/Observer.php : sauvegarde du point - '.$point);
    if(trim($point) != '' && preg_match($this->pointPattern, $point) && $quoteId > 0)
    {
      // delete row with parcel point if it already exists for this quote
      $tableName = Mage::getSingleton('core/resource')->getTableName('envoimoinscher/emc_points_tmp');
      $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
      $condition = array($connection->quoteInto('sales_flat_quote_entity_id = ?', $quoteId));
      $connection->delete($tableName, $condition);
      // insert new parcel point for this quote
      Mage::getModel('envoimoinscher/emc_points_tmp')->setData(array('sales_flat_quote_entity_id' => $quoteId,
      'point_ept' => $point))->save();
    }
    elseif(trim($point) != '' && !preg_match($this->pointPattern, $point))
    {
      Mage::log('Boxtale/Envoimoinscher/Model/Observer.php : le point relais a un format invalide - '.$point);
    }
  }

}