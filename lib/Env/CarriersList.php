<?php
/** 
 * EnvoiMoinsCher API carriers list class.
 * 
 * The class is used to obtain informations about all available carriers.
 * @package Env
 * @author EnvoiMoinsCher <dev@envoimoinscher.com>
 * @version 1.0
 */

class Env_CarriersList extends Env_WebService {

  /** 
   * Public variable represents offers array. 
	 * <samp>
	 * Structure :<br>
	 * $carriers[x]	=> array(<br>
	 * &nbsp;&nbsp;['ope_code'] => data<br>
	 * &nbsp;&nbsp;['ope_name'] => data<br>
	 * &nbsp;&nbsp;['srv_code'] => data<br>
	 * &nbsp;&nbsp;['label_store'] => data<br>
	 * &nbsp;&nbsp;['description'] => data<br>
	 * &nbsp;&nbsp;['description_store'] => data<br>
	 * &nbsp;&nbsp;['family'] => data<br>
	 * &nbsp;&nbsp;['zone'] => data<br>
	 * &nbsp;&nbsp;['parcel_pickup_point'] => data<br>
	 * &nbsp;&nbsp;['parcel_dropoff_point'] => data<br>
	 * )
	 * </samp>
   * @access public
   * @var array
   */
  public $carriers = array();
	
  /** 
   * Public function which receives the carriers list. 
   * @access public
   * @param String $channel platform used (prestashop, magento etc.).
   * @param String $version platform's version.
   * @return true if request was executed correctly, false if not
   */
  public function loadCarriersList($channel,$version) {
		$this->param["channel"] = strtolower($channel);
		$this->param["version"] = strtolower($version);
    $this->setGetParams(array());
    $this->setOptions(array('action' => '/api/v1/carriers_list'));
    if ($this->doSimpleRequest()){
			$this->getCarriersList();
			return true;
		}
		return false;
  }

  /** 
   * Function which gets carriers list details.
   * @access private
   * @return false if server response isn't correct; true if it is
   */
  private function doSimpleRequest() {
    $source = parent::doRequest();	
		/* Uncomment if ou want to display the XML content */
		//echo "<textarea>".print_r($source,true)."</textarea>";	
		
		/* We make sure there is an XML answer and try to parse it */
    if($source !== false) {
      parent::parseResponse($source);
      return (count($this->respErrorsList) == 0);
    }
    return false;
  }

  /** 
   * Function load all carriers
   * @access public
   * @param bool $onlyCom If true, we have to get only offers in the "order" mode.
   * @return Void
   */
  public function getCarriersList() {
		$this->carriers = array();
    $operators = $this->xpath->query('/operators/operator');
    foreach($operators as $operator) {
			$ope_code = $this->xpath->query('./code',$operator)->item(0)->nodeValue;
			$ope_name = $this->xpath->query('./name',$operator)->item(0)->nodeValue;
			$ope_carriers = $this->xpath->query('./services/service',$operator);
			foreach($ope_carriers as $carrier) {
				$id = count($this->carriers);
				$this->carriers[$id]["ope_code"] = $ope_code;
				$this->carriers[$id]["ope_name"] = $ope_name;
				$this->carriers[$id]["srv_code"] = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["srv_name"] = $this->xpath->query('./label',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["label_store"] = $this->xpath->query('./label_store',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["description"] = $this->xpath->query('./description',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["description_store"] = $this->xpath->query('./description_store',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["family"] = $this->xpath->query('./family',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["zone"] = $this->xpath->query('./zone',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["parcel_pickup_point"] = $this->xpath->query('./parcel_pickup_point',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["parcel_dropoff_point"] = $this->xpath->query('./parcel_dropoff_point',$carrier)->item(0)->nodeValue;
			}
    }
  }


}
?>