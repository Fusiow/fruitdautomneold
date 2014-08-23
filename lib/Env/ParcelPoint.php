<?php
/** 
 * EnvoiMoinsCher API parcel points class.
 * 
 * It can be used to load informations about one or more parcel points (for pickup and dropoff).
 * @package Env 
 * @author EnvoiMoinsCher <dev@envoimoinscher.com>
 * @version 1.0
 */

class Env_ParcelPoint extends Env_WebService {

  /** 
   *  Protected array which indicates the possibles root elements in the server reply document.
   *  @access protected
   *  @var array
   */
  protected $types = array('pickup_point', 'dropoff_point');

  /** 
   *  Public array with parcel points. It takes pickup_point or dropoff_point as the keys.
	 *
	 * <samp>
	 * Structure :<br>
	 * $points['pickup_point'|'dropoff_point'][x] => array(<br>
	 * &nbsp;&nbsp;['code'] 				=> data<br>
	 * &nbsp;&nbsp;['name'] 				=> data<br>
	 * &nbsp;&nbsp;['address'] 		=> data<br>
	 * &nbsp;&nbsp;['city'] 				=> data<br>
	 * &nbsp;&nbsp;['zipcode'] 		=> data<br>
	 * &nbsp;&nbsp;['country'] 		=> data<br>
	 * &nbsp;&nbsp;['description'] => data<br>
	 * &nbsp;&nbsp;['schedule'][x]	=> array(<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp;['weekday'] 		=> data<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp;['open_am'] 		=> data<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp;['close_am']		=> data<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp;['open_pm'] 		=> data<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp;['close_pm']	 	=> data<br>
	 * &nbsp;&nbsp;)<br>
	 * )
   *  @access public
   *  @var array
   */
  public $points = array();


  /** 
   *  Public boolean variable which specifies if the public $points variable will contain one 
   *  or more parcel points. 
   *  @access public
   *  @var array
   */
  public $constructList = false;
  
  /** 
   * Function load one parcel point. 
   *  @access public
   * @param $type : Parcel point type to load.
   * @param $code : Parcel point code composed by operator code and point id 
   * @param $country : Parcel point country.
   * @return Void
   */
  public function getParcelPoint($type = "", $code = "", $country = "FR") {
    if(in_array($type, $this->types)) {
      $this->setOptions(array("action" => "/api/v1/$type/$code/$country/informations",
	  )); 
      $this->doSimpleRequest($type);
    }
    else {
      $this->respError = true;
      $this->respErrorsList[0] = array("code" => "type_not_correct", "url" => "");
    }
  }
  
  /** 
   * Function executes parcel point request and prepares the $points array.
   *  @access private
   * @return Void
   */
  private function doSimpleRequest($type) {
    $source = parent::doRequest();
		
		/* Uncomment if ou want to display the XML content */
		//echo '<textarea>'.$source.'</textarea>';
		
		/* We make sure there is an XML answer and try to parse it */
    if($source !== false) {
			parent::parseResponse($source);
			
			$point = $this->xpath->query('/'.$type)->item(0);
      $pointDetail = array(
				'code' => $this->xpath->query('./code',$point)->item(0)->nodeValue,
        'name' =>  $this->xpath->query('./name',$point)->item(0)->nodeValue,
        'address' =>  $this->xpath->query('./address',$point)->item(0)->nodeValue,
        'city' =>  $this->xpath->query('./city',$point)->item(0)->nodeValue,
        'zipcode' =>  $this->xpath->query('./zipcode',$point)->item(0)->nodeValue,
        'country' =>  $this->xpath->query('./country',$point)->item(0)->nodeValue,
        'phone' =>  $this->xpath->query('./phone',$point)->item(0)->nodeValue,
        'description' => $this->xpath->query('./description',$point)->item(0)->nodeValue
      );
			
      /* We get open and close informations  */
      $schedule = array();
      foreach($this->xpath->query('./schedule/day',$point) as $d => $dayNode) {
        foreach($dayNode->childNodes as $c => $childNode) {
          if($childNode->nodeName != '#text') {
            $schedule[$d][$childNode->nodeName] = $childNode->nodeValue;
          }
        }
      }
      $pointDetail['schedule'] = $schedule;
			
			/* We store the data in the right array (defined by $type) */
      if($this->constructList) {
        if(!isset($this->points[$type]))
        {
          $this->points[$type] = array();
        }
        $this->points[$type][count($this->points[$type])] = $pointDetail;
      }
      else {
        $this->points[$type] = $pointDetail;
      }
    }
  }


}
?>