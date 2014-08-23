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
 * Boxtale_Envoimoinscher : categories container.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Boxtale_Envoimoinscher_Model_Emc_Wrapping extends Mage_Core_Model_Abstract 
{

  /** 
   * Default constructor.
   */
  protected function _construct() 
  {//include "log.php";log_emc(__FUNCTION__,__LINE__);
    $this->_init('envoimoinscher/emc_wrapping');
  }

  /** 
   * Fonction used to display all applicable wrappings.
   * @return array List with wrapping types.
   */
  public function toOptionArray() {
		$moduleConfig = Mage::getStoreConfig("carriers/envoimoinscher");
		
		if(isset($moduleConfig["user"])){
		
			$cotCl = new Env_Quotation(
				array(
					"user" => $moduleConfig["user"], 
					"pass" => $moduleConfig["mdp"], 
					"key" => $moduleConfig["cle"]
				)
			);
			$from = array(
				"pays" => "FR", 
				"code_postal" => "75002", 
				"ville" => "Paris", 
				"type" => "entreprise", 
				"societe" => "EnvoiMoinsCher", 
				"adresse" => "rue de la paix"
			);
			$to = array(
				"pays" => "FR", 
				"code_postal" => "75002", 
				"ville" => "Paris", 
				"type" => "particulier", 
				"societe" => "EnvoiMoinsCher", 
				"adresse" => "rue de la paix"
			);
			$date = new DateTime();
			$quotInfo = array(
				'collecte' => $date->format('Y-m-d'),
				'delai' => "aucun",
				'service' => "ColissimoAccess",
				'operator' => "POFR",
				'code_contenu' => 10120, 
				'valeur' => 20,
				'version' => Mage::helper('envoimoinscher')->getModuleInfoToApi("version"), 
				'module' => Mage::helper('envoimoinscher')->getModuleInfoToApi("name"));
			$cotCl->server = Mage::getModel('envoimoinscher/emc_environment')->getHost($moduleConfig['environment']);
			$cotCl->setPerson("expediteur", $from);
			$cotCl->setPerson("destinataire", $to);
			$cotCl->setType("colis", 
				array(
					1 => array(
						"poids" => 1, 
						"longueur" => 20, 
						"largeur" => 20, 
						"hauteur" => 20
					)
				)
			);
			$cotCl->getQuotation($quotInfo); 
			$cotCl->getOffers(false);
			
			$result = array("0" => "Veuillez choisir un type d'emballage");
			if (isset($cotCl->offers[0]['mandatory']['type_emballage.emballage']['array']))
			{
				$wrappingOptions = $cotCl->offers[0]['mandatory']['type_emballage.emballage']['array'];
				foreach($wrappingOptions as $option)
				{
					$splited = explode("-",$option,2);
					$result[$option] = $splited[1];
				}
			}
			
			return $result;
		
		}else{
			return false;
		}
  }

} 

?>