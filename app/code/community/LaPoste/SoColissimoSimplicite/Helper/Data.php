<?php
/**
 * LaPoste_SoColissimoSimplicite
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * URL du formulaire de soumission à l'accès au front de So Colissimo
     *
     * @var string
     */
    const FORM_URL = 'socolissimosimplicite/form/send';

    /**
     * URL appelée en cas de succès suite à la sélection de la livraison dans l'interface So Colissimo
     *
     * @var string
     */
    const SUCCESS_URL = 'socolissimosimplicite/form/success';

    /**
     * URL appelée en cas d'échec suite à l'accès au front de So Colissimo
     *
     * @var string
     */
    const FAILURE_URL = 'socolissimosimplicite/form/failure';

    /**
     * Numéro de version à envoyer obligatoirement à la plateforme
     *
     * @var string
     */
    const NUM_VERSION = '4.0';

    /**
     * Encodage utilisé pour envoyer les données à la plateforme
     *
     * @var string
     */
    const ENCODING_TYPE = 'UTF-8';

    /**
     * Langue affichée par défaut dans l'interface So Colissimo
     *
     * @var string
     */
    const DEFAULT_LANG = 'FR';

    /**
     * Disponibilité de la plateforme
     *
     * @var bool
     */
    protected $_serviceAvailable;

    /**
     * Retourne le numéro de version à soumettre à la plateforme
     *
     * @return string
     */
    public function getNumVersion()
    {
        return self::NUM_VERSION;
    }

    /**
     * Retourne l'encodage utilisé pour transmate les données à la plateforme
     *
     * @return string
     */
    public function getEncodingType()
    {
        return self::ENCODING_TYPE;
    }

    /**
     * Retourne l'url complète de la page contenant le formulaire à soumettre pour l'accès au front de So Colissimo
     *
     * @return string
     */
    public function getFormUrl()
    {
        return Mage::getUrl(self::FORM_URL);
    }

    /**
     * Retourne l'url complète appelée en cas de succès suite à la sélection de la livraison dans l'interface So Colissimo
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return Mage::getUrl(self::SUCCESS_URL);
    }

    /**
     * Retourne l'url complète appelée en cas d'échec suite à l'accès au front de So Colissimo
     *
     * @return string
     */
    public function getFailureUrl()
    {
        return Mage::getUrl(self::FAILURE_URL);
    }

    /**
     * Retourne la langue à transmettre à la plateforme So Colissimo
     *
     * @return string
     */
    public function getLanguage()
    {
        $availableLangs = array_keys(Mage::getStoreConfig('carriers/socolissimosimplicite/available_languages'));

        // transformation de "xx_XX" en "XX" (exemple : fr_FR -> FR)
        $lang = substr(Mage::app()->getLocale()->getLocaleCode(), 3);

        return in_array($lang, $availableLangs) ? $lang : self::DEFAULT_LANG;
    }

    /**
     * Retourne le montant par défaut des frais d'expédition (calculé dans le carrier)
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param string                         $shippingType
     * @param bool                           $cleanSession
     * @return float
     */
    public function getShippingAmount($shippingAddress, $shippingType, $cleanSession = false)
    {
        $amounts = Mage::getSingleton('checkout/session')->getData('socolissimosimplicite_available_shipping_amounts');

        // supprime les frais d'expédition de la session si demandé
        if ($cleanSession) {
            Mage::getSingleton('checkout/session')->unsetData('socolissimosimplicite_available_shipping_amounts');
        }

        return $shippingAddress->getFreeShipping() === '1' ? 0 : $amounts[$shippingType];
    }

    /**
     * Retourne le numéro de téléphone de l'utilisateur connecté
     * Seuls les numéros de mobile sont acceptés, et la validation du numéro est différente selon le pays
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return string
     */
    public function getPhoneNumber($shippingAddress)
    {
        $country = $shippingAddress->getCountryId();
        $phone = $shippingAddress->getTelephone();
        $isValid = true;

        switch ($country) {
            case 'FR':
                $firstTwoNumbers = substr($phone, 0, 2);
                $isValid = $firstTwoNumbers === '06' || $firstTwoNumbers === '07';
                break;

            case 'BE':
                $firstFourNumbers = substr($phone, 0, 4);
                $isValid = $firstFourNumbers === '+324';
                break;
        }

        return $isValid ? $phone : '';
    }

    /**
     * Retourne le code du mode de livraison tel qu'utilisé lors du passage d'une commande (code transporteur + mode)
     *
     * @return string
     */
    public function getRateCode()
    {
        $shippingMethod = new LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod();

        // le code du transporteur et le code du mode de livraison sont les mêmes
        $carrierCode = $shippingMethod->getCarrierCode();
        $methodCode = $shippingMethod->getCarrierCode();

        return $carrierCode . '_' . $methodCode;
    }

    /**
     * Vérifie si le carrier est activé ou non
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) Mage::getStoreConfig('carriers/socolissimosimplicite/active');
    }

    /**
     * Retourne l'identifiant d'accès au FO de So Colissimo Simplicité
     *
     * @return string
     */
    public function getAccountId()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/account');
    }

    /**
     * Retourne la clé de cryptage nécessaire à la génération de la signature pour l'accès au FO de So Colissimo Simplicité
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/encryption_key');
    }

    /**
     * Retourne l'url du front de So Colissimo Simplicité
     *
     * @return string
     */
    public function getUrlFo()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/url_fo');
    }

    /**
     * Retourne le message à afficher lors du choix de mode de livraison, sous le titre
     *
     * @return string
     */
    public function getSelectMessage()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/selectmessage');
    }

    /**
     * Retourne le message à afficher lors de la redirection vers le front de So Colissimo Simplicité
     *
     * @return string
     */
    public function getRedirectMessage()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/redirectmessage');
    }

    /**
     * Retourne l'url de vérification de la disponibilité de So Colissimo Simplicité
     *
     * @return string
     */
    public function getServiceIsAvailableUrl()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/url_service_is_available');
    }

    /**
     * Retourne si la vérification de la disponibilité de So Colissimo Simplicité doit être effectuée
     *
     * @return bool
     */
    public function getServiceIsAvailable()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/active_service_is_available') == '1';
    }

    /**
     * Retourne le message à afficher si le service n'est pas disponible
     *
     * @return string
     */
    public function getServiceNotAvailableMessage()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/not_available_message');
    }

    /**
     * Retourne l'url vers laquelle l'internaute est redirigée lors d'une erreur
     * (total quote incorrect, variable de session manquante...)
     *
     * @return string
     */
    public function getRedirectUrlOnError()
    {
        return Mage::getUrl(Mage::getStoreConfig('carriers/socolissimosimplicite/redirect_url_on_error'));
    }

    /**
     * Retourne le tableau associant les codes de civilité de So Colissimo avec les valeurs pouvant être définies dans Magento
     * Dans la config, la correspondance est fournir en JSON, le tableau correspondant est retourné
     *
     * @return array
     */
    public function getMapPrefix()
    {
        $map = Mage::getStoreConfig('carriers/socolissimosimplicite/mapprefix');

        return json_decode($map, true);
    }

    /**
     * Retourne les codes des points relais commerçants
     *
     * @return array
     */
    public function getPickupPointCodes()
    {
        $codes = Mage::getStoreConfig('carriers/socolissimosimplicite/pickup_codes');

        return is_array($codes) ? array_keys($codes) : array();
    }

    /**
     * Retourne les codes des bureaux de poste
     *
     * @return array
     */
    public function getPostOfficeCodes()
    {
        $codes = Mage::getStoreConfig('carriers/socolissimosimplicite/post_office_codes');

        return is_array($codes) ? array_keys($codes) : array();
    }

    /**
     * Retourne les frais de livraison correctement formatés pour la soumission auprès de So Colissimo
     *
     * @param float $shippingPrice
     * @param int $maxDecimalDigit
     * @return float
     */
    public function getFormatedShippingPrice($shippingPrice, $maxDecimalDigit = 2)
    {
        $formatedPrice = number_format($shippingPrice, $maxDecimalDigit, '.', '');

        return $formatedPrice;
    }

    /**
     * Retourne le poids total de la commande correctement formaté pour la soumission auprès de So Colissimo
     *
     * @param float $weight
     * @return int
     */
    public function getFormatedWeight($weight)
    {
         $weight = str_replace(',', '.', $weight);
         // conversion des kg en g
         $weight = $weight * 1000;
         $formatedWeight = number_format($weight, 0, '.', '');

         return $formatedWeight;
    }

    /**
     * Retourne le code civilité de So Colissimo correspondant au code civilité donné
     *
     * @param string $prefixMagento
     * @return string
     */
    public function getPrefixForSoColissimo($prefixMagento)
    {
        $prefixSoCo = '';
        $mapPrefix = $this->getMapPrefix();
        if (in_array($prefixMagento, $mapPrefix)) {
            $prefixSoCo = array_search($prefixMagento, $mapPrefix);
        }

        return $prefixSoCo;
    }

    /**
     * Retourne la civilité correspondant au code civilité au format So Colissimo donné
     *
     * @param string $prefixSoCo
     * @return string
     */
    public function getPrefixForMagento($prefixSoCo)
    {
        $prefixMagento = '';
        $mapPrefix = $this->getMapPrefix();
        if (isset($mapPrefix[$prefixSoCo])) {
            $prefixMagento = $mapPrefix[$prefixSoCo];
        }

        return $prefixMagento;
    }

    /**
     * Vérifier que la plateforme socolissimo est disponible
     *
     * @return bool
     */
    public function checkServiceAvailability()
    {
        if ($this->_serviceAvailable === null) {
            $available = true;

            if ($this->getServiceIsAvailable() && $this->getServiceIsAvailableUrl() != '') {
                // exécuter la requête GET et récupérer l'état du service
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $this->getServiceIsAvailableUrl());
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_HEADER, false);
                try {
                    $output = curl_exec($c);
                    $available = (trim($output) === '[OK]');
                } catch (Exception $e) {
                    Mage::logException($e);
                    $available = false;
                }
                curl_close($c);
            }

            $this->_serviceAvailable = $available;
        }

        return $this->_serviceAvailable;
    }

    /**
     * Retourne les données à envoyer à So Colissimo
     *
     * @param string                         $orderId
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return array
     */
    public function getFieldsToSend($orderId, $shippingAddress)
    {
        $defaultShippingAmount = $this->getShippingAmount($shippingAddress, 'default');
        $pickupShippingAmount = $this->getShippingAmount($shippingAddress, 'pickup');

        return array(
            'pudoFOId' => $this->getAccountId(),
            'ceName' => $shippingAddress->getLastname(),
            'dyForwardingCharges' => $this->getFormatedShippingPrice($defaultShippingAmount),
            'dyForwardingChargesCMT' => $this->getFormatedShippingPrice($pickupShippingAmount),
            'trClientNumber' => $shippingAddress->getCustomerId(),
            'trOrderNumber' => $shippingAddress->getQuoteId(),
            'orderId' => $orderId,
            'numVersion' => $this->getNumVersion(),
            'ceCivility' => $this->getPrefixForSoColissimo($shippingAddress->getPrefix()),
            'ceFirstName' => $shippingAddress->getFirstname(),
            'ceCompanyName' => $shippingAddress->getCompany(),
            'ceAdress1' => $this->_getShippingAddressStreet($shippingAddress, 2),
            'ceAdress2' => $this->_getShippingAddressStreet($shippingAddress, 3),
            'ceAdress3' => $this->_getShippingAddressStreet($shippingAddress, 1),
            'ceAdress4' => $this->_getShippingAddressStreet($shippingAddress, 4),
            'ceZipCode' => $shippingAddress->getPostcode(),
            'ceTown' => $shippingAddress->getCity(),
            'ceEmail' => $shippingAddress->getEmail(),
            'cePhoneNumber' => $this->getPhoneNumber($shippingAddress),
            'dyWeight' => $this->getFormatedWeight($shippingAddress->getWeight()),
            'trReturnUrlKo' => $this->getFailureUrl(),
            'trReturnUrlOk' => $this->getSuccessUrl(),
            'CHARSET' => $this->getEncodingType(),
            'cePays' => $shippingAddress->getCountryId(),
            'trInter' => $shippingAddress->getCountryId() === 'FR' ? '0' : '2',
            'ceLang' => $this->getLanguage(),
            'encryptionKey' => $this->getEncryptionKey(),
        );
    }

    /**
     * Retourne la ligne n° $line des informations (rue, étage, immeuble, etc.) de l'adresse de livraison de la commande en cours
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param int                            $line
     * @return string
     */
    protected function _getShippingAddressStreet($shippingAddress, $line = 1)
    {
        $arrayStreet = $shippingAddress->getStreet();
        $streetLine = '';
        if (0 < $line && $line <= count($arrayStreet)) {
            $streetLine = $arrayStreet[$line - 1];
        }

        return $streetLine;
    }

    /**
     * Vérifie si l'utilisateur est revenu de la plateforme So Colissimo
     *
     * @return bool
     */
    public function isSocoComplete()
    {
        $shippingPriceCalculations = Mage::getStoreConfig('carriers/socolissimosimplicite/shipping_price_calculations');
        $hasChosenShippingAmount = Mage::getSingleton('checkout/session')->hasData('socolissimosimplicite_chosen_shipping_amount');
        $isSocoComplete = false;

        if ($hasChosenShippingAmount && is_array($shippingPriceCalculations)) {
            $controllerName = Mage::app()->getRequest()->getControllerName();
            $actionName = Mage::app()->getRequest()->getActionName();
            foreach ($shippingPriceCalculations as $requestData) {
                if (array_key_exists('controller', $requestData) && array_key_exists('action', $requestData)) {
                    if ($controllerName === $requestData['controller'] && $actionName === $requestData['action']) {
                        $isSocoComplete = true;
                        break;
                    }
                }
            }
        }

        return $isSocoComplete;
    }

    /**
     * Nettoie la session du tunnel de commande
     *
     * @param bool $cleanAll
     * @return void
     */
    public function clearCheckoutSession($cleanAll = true)
    {
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        if ($checkoutSession) {
            $checkoutSession->unsetData('socolissimosimplicite_checkout_onepage_nextstep');
            $checkoutSession->unsetData('socolissimosimplicite_checkout_onepage_transactionid');
            $checkoutSession->unsetData('socolissimosimplicite_shipping_data');
            $checkoutSession->unsetData('socolissimosimplicite_from_socolissimo');
            $checkoutSession->unsetData('socolissimosimplicite_new_address_from_socolissimo');
            $checkoutSession->unsetData('socolissimosimplicite_billing_address');
            $checkoutSession->unsetData('socolissimosimplicite_shipping_address');
            // nettoie les variables servant à mémoriser le mode/prix choisi dans l'IFrame
            $checkoutSession->unsetData('socolissimosimplicite_chosen_delivery_mode');
            $checkoutSession->unsetData('socolissimosimplicite_chosen_shipping_amount');

            if ($cleanAll) {
                // nettoie le sous-total de la quote
                $checkoutSession->unsetData('socolissimosimplicite_quote_subtotal');

                // nettoie les frais d'expédition (en principe déjà fait par le formulaire d'envoi des données à la plateforme)
                $checkoutSession->unsetData('socolissimosimplicite_available_shipping_amounts');
            }
        }
    }

    /**
     * Retourne le format du prix affiché à l'étape livraison du tunnel de commande
     * pour la méthode de livraison So Colissimo
     *
     * @return string
     */
    public function getShippingPriceFormat()
    {
        return Mage::getStoreConfig('carriers/socolissimosimplicite/price_format');
    }
}
