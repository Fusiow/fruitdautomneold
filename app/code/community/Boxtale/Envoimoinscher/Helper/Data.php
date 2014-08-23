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


class Boxtale_Envoimoinscher_Helper_Data extends Mage_Core_Helper_Abstract
{

  /**
   * Proformas' labels translations.
   * @access protected
   * @var array
   */
  protected $_proforma = array("sale" => "vente", "repair" => "réparation", "return" => "retour", 
  "gift" => "cadeau, don", "sample" => "echantillon, maquette" , "personnal" => "usage personnel", 
  "document" => "documents inter-entreprises", "other" => "autre"
  );

 /**
  * Fields used to generate mandatory form
  * @access protected
  * @var array
  */ 
  protected $_fields = array("colis.description" => array("type" => "input", "maxlength" => "255", 'helper' => ''),
  "colis.valeur" => array("type" => "input", "maxlength" => "10", 'helper' => ''), 
  "civilite" => array("type" => array("select", "content" => array(array("nom" => "civility", "source" => "this/getCivilities"))), 'helper' => ''), 
  "nom" => array("type" => "input", "maxlength" => "10", 'helper' => ''), 
  "prenom" => array("type" => "input", "maxlength" => "10", 'helper' => ''), 
  "email" => array("type" => "input", "maxlength" => "10", 'helper' => ''),
  "disponibilite.HDE" => array("type" => "select", "content" => array(array("source" => "this/getDispo", "params" => "Start")), 'helper' => ''), 
  "disponibilite.HLE" => array("type" => "select", "content" => array(array("source" => "this/getDispo", "params" => "End")), 'helper' => ''),
  "retrait.pointrelais" => array("type" => "input", "maxlength" => "15", "helper" => "<p class=\"note\">Sélectionnez <a href=\"#\" onclick=\"javascript: openPopupEmc('DES'); return false;\">le code du point de proximité</a></p>"),
  "depot.pointrelais" => array("type" => "input", "maxlength" => "15", "helper" => "<p class=\"note\">Sélectionnez <a href=\"#\" onclick=\"javascript: openPopupEmc('EXP'); return false;\">le code du point de proximité</a></p>"),
  "envoi.raison" => array("type" =>  "select", "content" => array(array("source" => "this/getReasons")), 'helper' => ''), 
  );

  protected $_days = array(1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi',
  6 => 'samedi', 7 => 'dimanche');

  protected $_sites = array("TEST" => "http://test.envoimoinscher.com", "PROD" => "http://www.envoimoinscher.com");

 /**
  * Collect all the tracking data
  * @access public
  * @var array tracking data
  */
	public function getTrackinData()
	{
		include_once 'app/Mage.php';
		$config = Mage::getConfig()->loadModulesConfiguration('config.xml')->getNode('modules/Boxtale_Envoimoinscher')->asArray();
		$result["platform"] = "magento";
		$result["platform_version"] = Mage::getVersion();
		$result["module_version"] = $config['version'];
		return $result;
	}
  
 /**
  * Make collect date. We can't collect on Sunday.
  * @access public
  * @var string Collect date
  */
  public function setCollectDate($delay) 
  {
    $time = strtotime(date('Y-m-d'));
    $days = $delay*24*60*60;
    $result = $time + $days;
    if(date('N', $result) == 7)
    {
      $result = $result+(24*60*60);
    }
    return date('Y-m-d', $result);
  }

  /** Method which make pricing for configured operators.
   *  @param array $dbPrices Prices getted from the database, already configured.
   *  @param array $usedOpe Operators used by the store.
   *  @param array $defaultDims Default dimensions, used if price isn't made for one destination (= one line)
   *  @return array Array with all prices, used by block template.
   */ 
  public function setPrices($dbPrices, $usedOpe, $defaultDims) 
  {
    $zones = array("FRAN", "INTE");
    $prices = array();
    foreach($usedOpe as $operator) 
    {
      foreach($zones as $zone)
      {
        $prices[$operator][$zone] = (array)$dbPrices[$operator][$zone];
        // fill up empty lines
        for($i = count($dbPrices[$operator][$zone]); $i < 5; $i++)
        {
          $prices[$operator][$zone][$i] = array(
            'weight_from' => 0, 'weight_to' => 0,
            'length' => $defaultDims[$i][0], 'width' => $defaultDims[$i][1], 'height' => $defaultDims[$i][2],
            'price' => 0
          ); 
        }
      }
    }
    return $prices;
  }

  /**
   * Make one array with presented services (without doubles).
   * @access public
   * @param array $filterSrv Services list.
   * @return array Array with all operators.
   */
  public function makeCommonServices($filterSrv) 
  {
    $services = array();
    $i = 0;
    while(count($filterSrv) > 0)
    {
      if(!in_array($filterSrv[$i], $services) && $filterSrv[$i] != '')
      {
        $services[] = $filterSrv[$i];
        $keys = array_keys($filterSrv, $filterSrv[$i]);
        foreach($keys as $key)
        {
          unset($filterSrv[$key]);
        }
      }
      $i++;
    }
    return $services;
  }

  /** 
   * Decompose shippment type to choosen operator and service.
   * First 4 letters are equal to operator code.
   * @access public
   * @param string $code Shippment code.
   * @return array Array with operator ("ope" key) and service ("srv" key)
   */
  public function decomposeCode($code)
  {
    $shiDb = new Boxtale_Envoimoinscher_Model_Carrier_ShippingMethod;
    $code = explode("_", str_replace($shiDb->getCarrierCode()."_", "", $code));
    return array('ope' => $code[0], 'srv' => $code[1]);
  }

  /** 
   * Put mandatory informations into a string.
   * @access public
   * @param array $mandatory Mandatory informations array.
   * @return string String with mandatory informations.
   */
  public function prepareMandatory($mandatory)
  {
    $infos = array();
    foreach($mandatory as $information)
    {
      $infos[] = $information['label'];
    }
    return implode(', ', $infos);
  }

  /** 
   * Prepare and output mandatory form field.
   * @param string Mandatory field code.
   * @param string Default value.
   * @param string Type for input field.
   * @return string Mandatory field.
   */
  public function outputMandatory($field, $default, $type = "text")
  {
    if(array_key_exists($field['code'], $this->_fields))
    { 
      $spec = $this->_fields[$field['code']];
      $uniqId = rand(0, 3333).time();
      if((preg_match("/pointrelais/i", $field["code"]) && isset($field["array"][0])&& ($matchedPoint = preg_match("/POST/i", $field["array"][0]))) || ($spec['type'] == 'input' && $type == "hidden"))
      {
        if(isset($matchedPoint) && $matchedPoint == true)
        {
          $default = "POST";
        }
        $spec['type'] = 'hidden';
        $fieldForm = '<input type="hidden" name="'.$field['code'].'" value="'.$default.'" />';
      }      
      elseif($spec['type'] == 'input' && $type == "text")
      {
        if(preg_match("/pointrelais/i", $field["code"]))
        {
          $dataDef = explode("-", $default);
          if(count($dataDef) > 1) $default = $dataDef[count($dataDef)-1];
          else $default = $dataDef[1];
        }
        $fieldForm = '<input type="text" name="'.$field['code'].'" value="'.$default.'" maxlength="'.$spec['maxlength'].'" class="input-text" />';
      }
      elseif($spec['type'] == 'select')
      {
        $fieldForm = $this->prepareSelect($field['code'], $spec['content'], $default);
      }
      $fieldHtml = '<label for="field_'.$uniqId.'">'.ucfirst($field['label']).'</label>';
      return array('label' => $fieldHtml, 'type' => $spec['type'], 'field' => $fieldForm, 'helper' => $spec['helper']);
    }
  }

  /** 
   * Utilitary method to get shipping reason.
   * @access private
   * @return array Array with reasons.
   */
  private function getReasons()
  {
    $cotCl = new Env_Quotation(array());
		// get module helper
		$helper = Mage::helper("envoimoinscher");
		
		$tracking = $helper->getTrackinData();
		$cotCl->setPlatformParams($tracking['platform'],$tracking['platform_version'],$tracking['module_version']);										 
		
    return $cotCl->getReasons($this->_proforma);
  }

  /** 
   * Utilitary method to get hours.
   * @access private
   * @return array Array with hours.
   */
  private function getHours()
  {
    $arr = array();
    for($i = 0; $i < 24; $i++)
    {
      $arr[$i] = $i;
    }
    return $arr;
  }

  /** 
   * Utilitary method to get minutes.
   * @access private
   * @return array Array with minutes.
   */
  private function getMinutes()
  {
    $arr = array();
    for($i = 0; $i < 60; $i++)
    {
      $arr[$i] = $i;
    }
    return $arr;
  }

  /** 
   * Utilitary method to get civilities.
   * @access private
   * @return array Array with civilities.
   */
  private function getCivilities($params)
  {
    $civDb = new Boxtale_Envoimoinscher_Model_Emc_Civilities;
    return $civDb->toOptionArray();
  }

  /** 
   * Utilitary method to get disponibilites for collection.
   * @access private
   * @param array $param Array with type (start or end).
   * @return array Array with disponibilites.
   */
  private function getDispo($param)
  {
    $class = "Boxtale_Envoimoinscher_Model_Emc_Dispo_{$param[0]}";
    $disDb = new $class;
    return $disDb->toOptionArray();
  }

  /** 
   * Utilitary method to make html selects.
   * @access private
   * @param string $code Code used to field name.
   * @param array $infos HTML informations to select.
   * @return string String with HTML.
   */
  private function prepareSelect($code, $infos, $default)
  {
    foreach($infos as $i => $info)
    {
      $html .= '<select name="'.$code.''.$info['nom'].'">';
      // get informations to options
      $source = explode('/', $info['source']);
      if($source[0] == 'this')
      {
        $params = (array)$info['params'];
        $options = $this->$source[1]($params);
        foreach($options as $o => $option)
        {
          $selected = '';
          if($o == $default)
          {
            $selected = 'selected="selected"';
          }
          $html .= '<option value="'.$o.'" '.$selected.'>&nbsp;'.$option.'&nbsp;</option>';
        }
      }
      $html .= '</select> '.$info['after'].' ';
    }
    return $html;
  }  

  /** 
   * Map post values to be used by formMandatory block.
   * @access public
   * @param array $data HTML informations to select.
   * @return array Array with new informations.
   */
  public function mapFromPostToApi($data)
  {
    return array("colis.description" => $data['colis_description'], "colis.valeur" => $data['colis_valeur'],  
	"destinataire.civilite" => array($data['destinataire_civilite_civility']), 
    "disponibilite.HDE" => $data['disponibilite_HDE'],
    "disponibilite.HLE" => $data['disponibilite_HLE'],
    "collecte" => $data['collecte'], "envoi.raison" => $data['envoi_raison']
    );
  }

  /** 
   * Convert url entities to plain text.
   * @access public
   * @param string $url Url to decode.
   * @return .
   */
  public function decodeUrl($url)
  {
    return mb_convert_encoding(urldecode($url), 'UTF-8');
  }

  /** 
   * Loop which prepares items description (data used as shipment description).
   * @param Mage_Sales_Model_Order_Item $items Array of orders' items.
   * @return string String with sold objects, separated by ','.
   */
  public function constructDesc($items)
  {
    foreach($items as $item)
    {
      $data = $item->getData();
      $itemStr[] = $data['name'];
    }
    return implode(',', $itemStr);
  }

  /**
   * Prepares pro forma array.
   * @param Mage_Sales_Model_Order_Item $items Array of orders' items.
   * @return array Proformas array.
   */
  public function makeProforma($items)
  {
    $s = 1;
    foreach($items as $item)
    {
      $data = $item->getData();
      $proforma[$s] = array('description_en' => $this->translateGoogle($data['name']),
      'description_fr' => $data['name'],  'nombre' => $item->getQtyToShip(), 'valeur' => $data['price'], 
      'origine' => "FR", 'poids' => $data['weight']);
      $s++;
    }
    return $proforma;
  }

  /**
   * Utilitary method to translate some text.
   * @param string Text to translate.
   * @return string Translated text if not error. If error occured, it returns text to translate.
   */
  public function translateGoogle($text)
  {
    header("Content-Type: text/html;charset=utf-8");
    $data = file_get_contents("http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=".urlencode($text)."&langpair=fr|en");
    $data = json_decode($data);
    if((int)$data->responseStatus == 200)
    {
      return $data->responseData->translatedText;
    }
    else 
    {
      return $text;
    }
  }

  /**
   * Checks availability of EnvoiMoinsCher.com site. If http code is different than 200, we show 
   * a notice in the backend.
   * @return boolean True if ok, false if service isn't available.
   */
  public function checkAvailability()
  {
    $ch=curl_init(); 
    curl_setopt ($ch, CURLOPT_URL, "http://www.envoimoinscher.com");
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    $page=curl_exec($ch);
    if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
    {
      return false;
    }
    else
    {
      return true;
    }
  }

  /**
   * Prepares parcel points schedule.
   * @access public
   * @param array $schedule Unordered list with informations.
   * @return array array with ordered data.
   */
  public function setDay($schedule)
  {
    $dispo = array();
    if($schedule['open_am'] != '' && $schedule['close_am'] != '')
    {
      $dispo[] = array('from' => $schedule['open_am'], 'to' => $schedule['close_am']);
    }
    if($schedule['open_pm'] != '' && $schedule['close_pm'] != '')
    {
      $dispo[] = array('from' => $schedule['open_pm'], 'to' => $schedule['close_pm']);
    }
    if(count($dispo) > 0)
    {
      return array('day' => $this->_days[$schedule['weekday']], 'hours' => $dispo);
    }
    return array('day' => array(), 'hours' => array());
  }

  /**
   * Get store name.
   * @param string $emcValue Store name.
   * @return array Proformas array.
   */
  public function getCompanyName($emcValue)
  {
    if($emcValue == '')
    {
      if(($emcValue = Mage::getStoreConfig("general/store_information/name")) == '')
      {
        $emcValue = $_SERVER['SERVER_NAME'];
      }
    }
    return $emcValue;
  }

  /**
   * Get store name.
   * @param string $emcValue Store name.
   * @return array Proformas array.
   */
  public function getLabel($stateCode)
  {
    $state = '';
    if($stateCode != '')
    {
      $state = Mage::getModel('envoimoinscher/emc_tracking')->getTrackingLabel($stateCode);
    }
    return $state;
  }

  /**
   * Parses schedule for parcel points.
   * @access public
   * @param array $schedules Daily schedules (opening, closing hours).
   * @return array Final schedules.
   */
  public function parseSchedule($schedules)
  {
    $day = array();
    foreach($schedules as $s => $schedule) 
    {
      $dayArr = $this->setDay($schedule);
      if(count($dayArr['hours']) > 0)
      {
        $fromTo = array();
        foreach($dayArr['hours'] as $hour) 
        { 
          $fromTo[] = 'de '.$hour['from'].' à '.$hour['to'].'';
        }
        $day[] = '<b>'.$dayArr['day'].'</b> <br />'.implode('<br />', $fromTo);
      }
    }
    return $day;
  }

  /**
   * Prepares module's informations to API calls.
   * @access public
   * @param string $k Key to return.
   * @return string API parameter.
   */
  public function getModuleInfoToApi($k)
  {
    $modules = Mage::getConfig()->getNode('modules')->children();
    $version = "";
    if($modules->Boxtale_Envoimoinscher->version != null) $version = (string)$modules->Boxtale_Envoimoinscher->version;
    $v = array("name" => "magento", "version" => $version);
    return $v[$k];
  }

  public function getDeliveryLabel($key)
  {
    $labels = array("civilite" => "Civilité", "prenom" => "Prénom", "nom" => "Nom", "adresse" => "Adresse",
    "code_postal" => "Code postal", "ville" => "Ville", "infos" => "Informations supplémentaires",
    "tel" => "Numéro de téléphone", "societe" => "Nom de la société", "email" => "E-mail");
    if(!isset($labels[$key])) return "";
    return $labels[$key];
  }

  public function constructServicesString($france, $international)
  {
    $services = "";
    if($france != "") $services .= $france;
    if($france != "" && $international != "") $services .= ",";
    if($international != "") $services .= $international;
    return $services;
  }

  public function normalizeToFloat($value)
  {
    return (float)str_replace(",", ".", $value);
  }

public function getSiteUrl($key)
{
  return $this->_sites[strtoupper($key)];
}

}