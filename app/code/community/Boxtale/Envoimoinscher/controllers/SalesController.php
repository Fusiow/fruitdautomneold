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
 * Boxtale_Envoimoinscher : sales controller.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
class Boxtale_Envoimoinscher_SalesController extends Mage_Adminhtml_Controller_Action 
{

  /**
   * Display EnvoiMoinsCher.com orders table.
   * @access public 
   * @return void
   */
  public function indexAction() 
  {
    $helper = Mage::helper("emc_sender");
   
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $block->setData('orderref', Mage::getSingleton('core/session')->getOrderRef());
if(($showBlock = $helper->isSendBlock()))
{
  $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
  $massModes = Mage::getModel('envoimoinscher/emc_mass');
  $blockSend = $layout->getBlock('sendship');
  $blockSend->setData('show', $showBlock);
  $blockSend->setData('isautomatic', 1);
  $blockSend->setData('isindex', 1);
  $blockSend->setData('total', $helper->getTotal());
  $blockSend->setData('done', $helper->getDone());
  $blockSend->setData('end', (int)$helper->wasEnd());
  $blockSend->setData('withcheck', (int)$massModes->mustCheck((int)$moduleConfig["mass_mode"]));
}
    $this->renderLayout();
Mage::getSingleton('core/session')->unsOrderRef();
Mage::getSingleton('core/session')->unsOrderError();
  }

  /**
   * Send order to EnvoiMoinsCher.com
   * @access public
   * @return void
   */
  public function sendAction()
  {
    $this->loadLayout();
    $layout = $this->getLayout();
    $block = $layout->getBlock('content');
    $orderId = (int)$this->getRequest()->getParam('order_id');
    $alreadyPassed = false;
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
$errorMessage = Mage::getSingleton('core/session')->getOrderError();
    // don't allow to pass an order twice
    if(!Mage::getModel('envoimoinscher/emc_orders')->existsOrder($orderId))
    {
      // prepare data
      $helper = Mage::helper("emc_sender");
      $helper->setOrderId($orderId);
      $helper->prepareOrder($this->getRequest()->getPost());
      // if post request, do order; if not, make only the quotation
      if($this->getRequest()->isPost())
      {
$helper->setSource(Mage::getSingleton('core/session')->getMassSource());
        $helper->order();
        $isDoOthers = $helper->isDoOthers();
        if($isDoOthers && $helper->isMassShipment())
        {
          $nextOrderId = $helper->getNextOrderId();
          if($helper->isErrorSource() && !$helper->isSuccessOrder())
          {
            $nextOrderId = $orderId;
          }
          elseif(Mage::getSingleton('core/session')->getMassSource() == "")
          {
            Mage::getSingleton('core/session')->unsOrderError();
            Mage::getSingleton('core/session')->unsEmcOrderData();           
          }
          $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/send', array('order_id' => $nextOrderId, 'key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","send"))));
        }
        elseif(!$isDoOthers && $helper->isMassShipment())
        {
          $helper->setEnd();
          $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/index', array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","index"))));
        }
        else
        {
          if($helper->isSuccessOrder())
          {
            Mage::getSingleton('core/session')->setOrderRef($helper->getOrderInfo('ref'));
            // add reference number
            $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/index', array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","index"))));
          }
          else
          {
            Mage::getSingleton('core/session')->setOrderError($helper->getErrorMessage());
            $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/send', array('order_id' => $orderId, 'key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","send"))));          
          }
        }
      }
      else 
      { 
        $errorType = "order";
        $cotationInfo = $helper->getOrderData(array());
        // library class
        $cotCl = new Env_Quotation(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
        // get module helper
				$helper_tracking = Mage::helper("envoimoinscher");
				
				$tracking = $helper_tracking->getTrackinData();
				$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
				
				
        $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
        $cotCl->setPerson("expediteur", $cotationInfo["shipper"]);
        $cotCl->setPerson("destinataire", $cotationInfo["receiver"]);
        $cotCl->setType($moduleConfig["type"], $cotationInfo["shipment"]);
        $cotCl->getQuotation($helper->getOnlyQuotationParams());
        $cotCl->getOffers(false);
        if(!$cotCl->curlError && !$cotCl->respError)
        {
          // find operator and service 
          foreach($cotCl->offers as $offer)
          {
            if($offer['operator']['code'] == $cotationInfo["params"]["operateur"] && $offer['service']['code'] == $cotationInfo["params"]["service"])
            {
              $offerChoosen = $offer;
              break;
            }
          }
          $errorMessage = Mage::getSingleton('core/session')->getOrderError();
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
          $errorType = "quote";
        }
$parcelWeight = $helper->getWeight();
$dbOrder = $helper->getDbOrder();
        // render layout
        $block->setData('emcurl', Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']));
        $block->setData('offer', $offerChoosen);
        $block->setData('offers', $cotCl->offers);
        $block->setData('errormessage', $errorMessage);
        $block->setData('errortype', $errorType);
        $block->setData('weight', $parcelWeight);
        $block->setData('orderid', $orderId);
$block->setData("alreadypassed", 0);
        $block->setData('secret', Mage::getSingleton('adminhtml/url')->getSecretKey("sales","replace"));
        $block->setData('customer', $cotationInfo["receiver"]);
$block->setData('customerofferlabel', $dbOrder["shipping_description"]);
$block->setData('isnotemc', (bool)(strpos($dbOrder["shipping_method"], "envoimoinscher_") === false));
        $blockSend = $layout->getBlock('sendship');
$blockSend->setData('show', (int)$helper->isSendBlock());
$blockSend->setData('isautomatic', 0);
$blockSend->setData('total', $helper->getTotal());
$blockSend->setData('orderid', $orderId);
$blockSend->setData('hasmoreorders', (bool)(($helper->getTotal() - $helper->getDone()) > 1));
$blockSend->setData('done', $helper->getDone());
$blockSend->setData('end', (int)$helper->wasEnd());
$blockTable = $layout->getBlock('offerTable');
$blockTable->setData('offer', $offerChoosen);
        $blockForm = $layout->getBlock('formMandatory');
        $blockForm->setData('offer', $offerChoosen);
        $blockForm->setData('orderid', $orderId);
        $blockForm->setData('params', $cotationInfo["params"]);
$blockForm->setData('multiparcel', (int)Mage::getModel('envoimoinscher/emc_orders_parcels')->isMultiParcel($moduleConfig["multi_parcel"]));
$blockForm->setData('weight', $parcelWeight);
$blockForm->setData('shipment', $cotationInfo['shipment']);
        $blockForm->setData('proforma', $cotationInfo["proforma"]);
        $blockForm->setData('customer', $cotationInfo["receiver"]);
        $blockForm->setData('shipper', $cotationInfo["shipper"]);
        $this->renderLayout();
$helper->cleanMemorizedData();
Mage::getSingleton('core/session')->unsOrderError();
      }
    }
    else
    {
$block->setData("alreadypassed", 1);
      $this->renderLayout();
    }
  }

  /**
   * Print multiple shipment labels
   * @access public
   * @return void
   */
  public function printAction()
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    if(count($this->getRequest()->getPost('order_ids')) > 0)
    {
      $orderIds = array();
      foreach($this->getRequest()->getPost('order_ids') as $orderId)
      {
        $orderIds[] = (int)$orderId;
      }
    }
    else
    {
      $orderIds = array((int)$this->getRequest()->getParam('order'));
    }
    $references = Mage::getModel('envoimoinscher/emc_orders')->getOrdersByArray($orderIds);
    foreach($references as $reference)
    {
      $refs[] = $reference['ref_emc_eor'];
    }
    $options = array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']).'/documents?type=bordereau&envoi='.implode(',', $refs),
      CURLOPT_CAINFO => Mage::getBaseDir('lib').'/ca/ca-bundle.crt', CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_HTTPHEADER => array("Authorization: ".base64_encode($moduleConfig["user"].":".$moduleConfig["mdp"])) 
    );
    if(Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']) == 'https://test.envoimoinscher.com/')
    {
      $options[CURLOPT_SSL_VERIFYPEER] = false; 
      $options[CURLOPT_SSL_VERIFYHOST] = 0; 
    }
    $req = curl_init();
    curl_setopt_array($req, $options); 
    $result = curl_exec($req);    
    $curlInfo = curl_getinfo($req);
    curl_close($req); 
    header("Content-type: application/pdf");
    header('Content-Disposition: attachment; filename="bordereaux.pdf"');
    echo $result;
    die();
  }


  /**
   * Replace one offer by another one.
   * @access public
   * @return void
   */
  public function replaceAction()
  {
    $orderId = (int)$this->getRequest()->getParam('order_id');
    $newOffer = $this->getRequest()->getParam('offer');
    if($orderId > 0 && trim(str_replace('_', '',$newOffer)) != '')
    {
      // make new offer code
      $offerCode = "envoimoinscher_envoimoinscher_".$newOffer;
      $shipInfo = Mage::helper("envoimoinscher")->decomposeCode($offerCode);
      $offerRow = Mage::getModel("envoimoinscher/emc_services")->getByOpeSrv($shipInfo['ope'], $shipInfo['srv']);
      Mage::getModel('sales/order')->load($orderId)->addData(
      array('shipping_description' => $offerRow["label_es"]." (".$offerRow["operator"].")", 'shipping_method' => $offerCode))->save();
      // redirect to order page
      $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/send', array('order_id' => $orderId, 'key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","send"))));
    }
  }

  public function initAction()
  {
    $type = $this->getRequest()->getParam("type");
    $params = $this->getRequest()->getParams();
    $helper = Mage::helper("emc_sender");
    $helper->constructOrders($params["order_ids"]);
    $emcMas = Mage::getModel("envoimoinscher/emc_mass");
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $massModes = Mage::getModel('envoimoinscher/emc_mass');
    Mage::getSingleton('core/session')->setMassSource($this->getRequest()->getParam("source"));
    if($massModes->mustCheck($moduleConfig["mass_mode"]) || $type == "forceCheck")
    {
      $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/send', array('order_id' => $params["order_ids"][0], 'key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","send"))));
    }
    elseif(!$massModes->mustCheck($moduleConfig["mass_mode"]))
    {
      $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/index', array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","index"))));      
    }
  }

  public function sendMassAction()
  {
    $orderId = (int)$this->getRequest()->getParam('order_id');
    $result = array("result" => 0, "doOthers" => 0);
    $helper = Mage::helper("emc_sender");
    if($orderId > 0) $helper->cancelOrders();

    $helper->setOrderId($orderId);
    if($helper->getOrderId() > 0)
    {
      if(!($orderExists = Mage::getModel('envoimoinscher/emc_orders')->existsOrder($helper->getOrderId())))
      {
        $helper->prepareOrder($this->getRequest()->getPost());
        $helper->order();
      }
      elseif($orderExists)
      {
        $helper->skipOrder();
      }
      $isDoOthers = $helper->isDoOthers();
      if($this->getRequest()->getQuery('ajax') == 1 || $this->getRequest()->isXmlHttpRequest())
      {
        $result["result"] = 1;
        $result["doOthers"] = (int)$isDoOthers;
      }
    }
    Mage::getSingleton('core/session')->unsOrderError();
    Mage::getSingleton('core/session')->unsEmcOrderData();
    echo json_encode($result);
    die();
  }

  public function resultAction()
  {
    Mage::getSingleton('core/session')->unsOrderError();
    Mage::getSingleton('core/session')->unsEmcOrderData();
    $helper = Mage::helper("emc_sender");
    echo json_encode($helper->getFinalResult());
    die();
  }

  public function cancelAction()  
  {
    Mage::getSingleton('core/session')->unsOrderError();
    Mage::getSingleton('core/session')->unsEmcOrderData();
    $helper = Mage::helper("emc_sender");
    $helper->cancelOrders();
    $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/index', array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","index"))));
  }

  public function skipAction()
  {
    $helper = Mage::helper("emc_sender");
    $helper->setOrderId($this->getRequest()->getParam("skipped"));
    $helper->skipOrder();
    $helper->setOrderId(0);
    if($helper->isDoOthers())
    {
      $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/send', array('order_id' => $helper->getOrderId(), 'key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","send"))));
    }
    else
    {
      $this->getResponse()->setRedirect(Mage::getUrl('envoimoinscher/sales/index', array('key' =>  Mage::getSingleton('adminhtml/url')->getSecretKey("sales","index"))));
    }
  }
  
  public function getNewOffersAction()
  {
    $moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
    $params = $this->getRequest()->getPost();
    $params["parcel"] = array();
    if(isset($params["parcels"]) && trim($params["parcels"]) != "")
    {
      foreach(explode(";", $params["parcels"]) as $p => $parcel)
      {
        $params["parcel"][] = (float)$parcel;
      }
      unset($params["parcels"]);
    }
    $helper = Mage::helper("emc_sender");
    $helper->setOrderId((int)$this->getRequest()->getParam('order'));
    $helper->prepareOrder($params);
    $cotationInfo = $helper->getOrderData(array());
    // library class
    $cotCl = new Env_Quotation(array("user" => $moduleConfig["user"], "pass" => $moduleConfig["mdp"], "key" => $moduleConfig["cle"]));
		// get module helper
		$helper = Mage::helper("envoimoinscher");
		
		$tracking = $helper->getTrackinData();
		$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		
    $cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
    $cotCl->setPerson("expediteur", $cotationInfo["shipper"]);
    $cotCl->setPerson("destinataire", $cotationInfo["receiver"]);
    $cotCl->setType($moduleConfig["type"], $cotationInfo["shipment"]);
    $cotCl->getQuotation($helper->getOnlyQuotationParams());
    $cotCl->getOffers(false);
    $offerChoosen = array();
    // find operator and service 
    foreach($cotCl->offers as $offer)
    {
      if($offer['operator']['code'] == $cotationInfo["params"]["operateur"] && $offer['service']['code'] == $cotationInfo["params"]["service"])
      {
        $offerChoosen = $offer;
        break;
      }
    }
    $layout = $this->getLayout();
    $update = $layout->getUpdate();
    $update->load('envoimoinscher_sales_get_new_offers');
    $layout->generateXml();
    $layout->generateBlocks();
    $blockTable = $layout->getBlock('offerTable');
    $blockTable->setData('offer', $offerChoosen);
    $blockTable->setData('fromchangeweight', 1);
    $blockTable->setData('urlsend', $this->getUrl('envoimoinscher/sales/send', array('order_id' => $helper->getOrderId())));
    $blockTable->setData('orderid', $helper->getOrderId());
    $output = $layout->getOutput();
    $this->getResponse()->setBody($output);
  }

}