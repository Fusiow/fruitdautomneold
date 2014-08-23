<?php
/**
 * Mode de livraison So Colissimo Simplicité
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod
    extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Identifiant unique du mode de livraison
     *
     * @var string
     */
    protected $_code = 'socolissimosimplicite';

    /**
     * Récolte et retourne les informations sur le mode de livraison utiles pour le passage d'une commande
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // on stoppe si le mode de livraison n'est pas activé dans la configuration
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // idem si la limite de poids ou la tranche de prix ne sont pas respectés
        if (is_numeric($this->getConfigData('min_order_total')) && $request->getPackageValue() < $this->getConfigData('min_order_total')) {
            return false;
        }

        if (is_numeric($this->getConfigData('max_order_total')) && $request->getPackageValue() > $this->getConfigData('max_order_total')) {
            return false;
        }

        if (is_numeric($this->getConfigData('max_weight')) && $request->getPackageWeight() > $this->getConfigData('max_weight')) {
            return false;
        }

        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result = Mage::getModel('shipping/rate_result');

        // ajout de la méthode qui sera affichée dans le tunnel de commande
        /* @var $method Mage_Shipping_Model_Rate_Result_Method */
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);

        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
        $helper = Mage::helper('socolissimosimplicite');

        if ($helper->isSocoComplete()) {
            // après la sortie de la plateforme socolissimo (IFrame), on utilise le prix transmis par socolissimo
            // on ne peut pas se baser sur le prix de l'adresse de livraison car son prix est réinitialisé
            // à l'étape du paiement dans certaines versions de Magento
            $price = $checkoutSession->getData('socolissimosimplicite_chosen_shipping_amount');

            // mettre à jour le libellé de la méthode de livraison
            $deliveryMode = $checkoutSession->getData('socolissimosimplicite_chosen_delivery_mode');
            if ($deliveryMode === 'DOM') {
                $methodTitle = $this->getConfigData('name_home');
            } elseif ($deliveryMode === 'RDV') {
                $methodTitle = $this->getConfigData('name_appointment');
            } elseif (in_array($deliveryMode, $helper->getPickupPointCodes())) {
                $methodTitle = $this->getConfigData('name_pickup');
            } elseif (in_array($deliveryMode, $helper->getPostOfficeCodes())) {
                $methodTitle = $this->getConfigData('name_post_office');
            } else {
                // mode de livraison incorrect, on affiche le libellé par défaut
                // ce cas pourrait par exemple se produire dans le cas de l'ajout d'un nouveau pays alors que ses codes
                // de point de retrait et de bureaux de poste ne sont pas renseigné dans le fichier config.xml
                $methodTitle = $this->getConfigData('name');
            }
        } else {
            // vérifier si la livraison So Colissimo est offerte et à partir de quel montant
            $minQuotePriceForFreeShipping = $this->getConfigData('minquotepriceforfree');
            $quotePriceWithDiscount = $request->getData('package_value_with_discount');

            $isFreeShipping = $request->getFreeShipping()
                || ($minQuotePriceForFreeShipping > 0 && $quotePriceWithDiscount >= $minQuotePriceForFreeShipping);

            if ($isFreeShipping) {
                $defaultPrice = $pickupPrice = $price = 0;
            } else {
                // calcul des frais d'expéditions
                $defaultPrice = $this->_getCalculatedPrice(
                    $checkoutSession->getQuote()->getShippingAddress(),
                    $this->getConfigData('amountbasetype'),
                    $this->getConfigData('amountcalculation')
                );

                // calcul des frais d'expédition commerçants (prend la valeur des frais par défaut si non renseignés
                $pickupPrice = $defaultPrice;
                $amountCalculationPickup = $this->getConfigData('amountcalculation_pickup');
                if ($amountCalculationPickup !== null && $amountCalculationPickup !== '' && $amountCalculationPickup !== false) {
                    $pickupPrice = $this->_getCalculatedPrice(
                        $checkoutSession->getQuote()->getShippingAddress(),
                        $this->getConfigData('amountbasetype_pickup'),
                        $amountCalculationPickup
                    );
                }

                if ($helper->checkServiceAvailability()) {
                    // prendre le prix le plus bas
                    $price = ($pickupPrice < $defaultPrice) ? $pickupPrice : $defaultPrice;
                } else {
                    // si la plateforme est indisponible, systématiquement prendre le prix normal
                    // il n'y aura pas d'affichage de l'IFrame, ce prix sera directement utilisé pour commander
                    $price = $defaultPrice;
                }
            }

            // sauvegarder les frais d'expédition dans la session (ils seront utilisés par la transaction et le form d'envoi)
            $checkoutSession->setData(
                'socolissimosimplicite_available_shipping_amounts',
                array(
                    'default' => $defaultPrice,
                    'pickup' => $pickupPrice,
                )
            );

            // utiliser le nom de méthode par défaut
            $methodTitle = $this->getConfigData('name');
        }

        // mémoriser le sous-total de la quote, cela permettra de s'assurer que l'internaute
        // ne l'ait pas modifié pendant qu'il était sur la plateforme So Colissimo
        $checkoutSession->setData('socolissimosimplicite_quote_subtotal', $checkoutSession->getQuote()->getSubtotal());

        // on attribue à la méthode les frais d'expédition les plus bas (c'est ce qui sera affiché dans le tunnel de commande)
        $method->setMethodTitle($methodTitle);
        $method->setPrice($price);
        $method->setCost($price);
        $result->append($method);

        return $result;
    }

    /**
     * Retourne les modes de livraison authorisés
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Retourne le montant des frais de livraison après éventuels calculs selon le mode de tarification choisi dans l'administration
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param string                         $amountBaseType
     * @param string                         $amountBaseCalculation
     * @return float
     */
    protected function _getCalculatedPrice($shippingAddress, $amountBaseType, $amountCalculation)
    {
        /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
        $helper = Mage::helper('socolissimosimplicite');

        // sinon calcul en fonction de la configuration
        switch ($amountBaseType) {
            case 'fixed':
                if (is_numeric($amountCalculation)) {
                    $calculatedPrice = $amountCalculation;
                } else {
                    throw new Exception($helper->__('SoColissimo : la valeur de configuration "Calcul des frais de livraison" fournie "%s" doit être un numérique de la forme 5.50', $amountCalculation));
                }
                break;
            case 'per_weight':
                $rules = json_decode($amountCalculation, true);
                if (is_null($rules) || empty($rules)) {
                    throw new Exception($helper->__('SoColissimo : la valeur de configuration "Calcul des frais de livraison" pour le type "tarif selon le poids" fournie "%s" n\'est pas dans le bon format, exemple {"0":"2.90","0.5":"4.50","5":"8","10":"14"}', $amountCalculation));
                } else {
                    try {
                        // tri par poids décroissant
                        krsort($rules, SORT_NUMERIC);

                        // récupération du poids total de la commande (en kilogrammes)
                        $totalWeight = $shippingAddress->getWeight();

                        // recherche du prix selon les fourchettes de tarifs données
                        $calculatedPrice = false;
                        foreach ($rules as $w => $p) {
                            $calculatedPrice = $p;
                            if ($w <= $totalWeight) {
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        throw new Exception($helper->__('SoColissimo : la valeur de configuration "Calcul des frais de livraison" pour le type "tarif selon le poids" fournie "%s" n\'est pas dans le bon format, exemple {"0":"2.90","0.5":"4.50","5":"8","10":"14"}', $amountCalculation));
                    }
                }
                break;
            case 'per_amount':
                $rules = json_decode($amountCalculation, true);
                if (is_null($rules) || empty($rules)) {
                    throw new Exception($helper->__('SoColissimo : la valeur de configuration "Calcul des frais de livraison" pour le type "tarif selon le sous-total" fournie "%s" n\'est pas dans le bon format, exemple {"0":"3","50":"5","100":"8","250":"0"}', $amountCalculation));
                } else {
                    try {
                        // tri par montant décroissant
                        krsort($rules, SORT_NUMERIC);

                        // recuperation du sous total HT du panier
                        $totalAmount = $shippingAddress->getSubtotal();

                        // recherche du prix selon les fourchettes de tarifs donnees
                        $calculatedPrice = false;
                        foreach ($rules as $w => $p) {
                            $calculatedPrice = $p;
                            if ($w <= $totalAmount) {
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        throw new Exception($helper->__('SoColissimo : la valeur de configuration "Calcul des frais de livraison" pour le type "tarif selon le sous-total" fournie "%s" n\'est pas dans le bon format, exemple {"0":"3","50":"5","100":"8","250":"0"}', $amountCalculation));
                    }
                }
                break;
            default:
                throw new Exception($helper->__('SoColissimo : la valeur de configuration "Type des frais de livraison" fournie "%s" n\'est pas disponible', $amountCalculation));
                break;
        }

        return $calculatedPrice;
    }

    /**
     * Retourne le code du mode de livraison (nécessaire pour les versions < 1.4)
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->_code;
    }
}
