<?php
/** 
 * EnvoiMoinsCher API carrier class.
 * 
 * It can be used to load informations about carriers and theirs services. 
 * @package Env
 * @author EnvoiMoinsCher <dev@envoimoinscher.com>
 * @version 1.0
 */

class Env_Carrier extends Env_WebService {

  /** 
   * Contains carriers array.
	 *
	 * <samp>
	 * Structure :<br>
	 * $carriers[code] 	=> array(<br>
	 * &nbsp;&nbsp;['label'] 				=> data<br>
	 * &nbsp;&nbsp;['code'] 				=> data<br>
	 * &nbsp;&nbsp;['logo'] 				=> data<br>
	 * &nbsp;&nbsp;['logo_modules'] => data<br>
	 * &nbsp;&nbsp;['description'] 	=> data<br>
	 * &nbsp;&nbsp;['address'] 			=> data<br>
	 * &nbsp;&nbsp;['url'] 					=> data<br>
	 * &nbsp;&nbsp;['tracking']			=> data<br>
	 * &nbsp;&nbsp;['tel'] 					=> data<br>
	 * &nbsp;&nbsp;['cgv'] 					=> data<br>
	 * )
	 * </samp>
   *  @access public
   *  @var array
   */
  public $carriers = array();

  /** 
   *  Function loads all carriers.
   * @return Void
   *  @access public
   */
  public function getCarriers() { 
    $this->setOptions(array("action" => "/api/v1/carriers",
	));
    $this->doCarrierRequest();
  }

  /** 
   * Function executes carrier request and prepares the $listPoints array.
   *  @access private
   * @return Void
   */
  private function doCarrierRequest() {
    $source = $this->doRequest();
		
		/* Uncomment if ou want to display the XML content */
		//echo '<textarea>'.$source.'</textarea>';
		
		/* We make sure there is an XML answer and try to parse it */
    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {
				
				/* The XML file is loaded, we now gather the datas */
      $carriers = $this->xpath->query("/operators/operator");
      foreach($carriers as $c => $carrier) {
					$result = $this->parseCarrierNode($carrier);
					/* We usr the 'code' data as index (maybe using the $c index is better) */
					$code = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
					$this->carriers[$result['code']] = $result;
				}
      }
    }
  }

	protected function parseCarrierNode($carrier)
	{
		/* We usr the 'code' data as index (maybe using the $c index is better) */
		$code = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
		$result = array(
			'label' => $this->xpath->query('./label',$carrier)->item(0)->nodeValue,
			'code' => $this->xpath->query('./code',$carrier)->item(0)->nodeValue, 
			'logo' => $this->xpath->query('./logo',$carrier)->item(0)->nodeValue,
			'logo_modules' => $this->xpath->query('./logo_modules',$carrier)->item(0)->nodeValue,
			'description' => $this->xpath->query('./description',$carrier)->item(0)->nodeValue,
			'address' => $this->xpath->query('./address',$carrier)->item(0)->nodeValue,
			'url' => $this->xpath->query('./url',$carrier)->item(0)->nodeValue,
			'tracking' => $this->xpath->query('./tracking_url',$carrier)->item(0)->nodeValue,
			'tel' => $this->xpath->query('./telephone',$carrier)->item(0)->nodeValue,
			'cgv' => $this->xpath->query('./cgv',$carrier)->item(0)->nodeValue);
		return $result;
  }

}
?>