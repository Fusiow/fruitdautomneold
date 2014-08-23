<?php
/**
 * Observer So Colissimo
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Model_Observer
{
    /**
     * Modes de livraisons
     *
     * @var string
     */
    const MODE_RDV = 'RDV';
    const MODE_DOM = 'DOM';

    /**
     * Tableau de correspondance les champs simples retournés par SO COlissimo
     * et les champs d'une adresse de livraison dans Magento
     *
     * @var array
     */
    protected $_mapFields = array(
        'CENAME'              => 'lastname',
        'CEFIRSTNAME'         => 'firstname',
        'CETOWN'              => 'city',
        'CEZIPCODE'           => 'postcode',
        'CEEMAIL'             => 'email',
        'CEPAYS'              => 'country_id',
        'CEPHONENUMBER'       => 'telephone',
        'CECOMPANYNAME'       => 'company',
        'PRZIPCODE'           => 'postcode',
        'PRTOWN'              => 'city',
    );

    /**
     * Met à jour les données de livraison à partir des infos récupérées depuis So Colissimo
     * L'adresse de livraison est automatiquement réinitialisée avec celle par défaut du client
     * dès qu'il  est sur le tunnel de commande, c'est pourquoi cette mise à jour est effectué APRÈS cette réinitialisation
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setShippingAddressWithSoColissimoAddress($observer)
    {
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        $transactionId = $checkoutSession->getData('socolissimosimplicite_checkout_onepage_transactionid');
        $nextStep = $checkoutSession->getData('socolissimosimplicite_checkout_onepage_nextstep');
        $quoteTotalBeforeRedirection = $checkoutSession->getData('socolissimosimplicite_quote_subtotal');

        // on regarde s'il existe une transaction avec So Colissimo en session
        // et si on cherche à afficher le choix du mode de paiement
        if ($transactionId && $nextStep == 'payment') {
            // si l'utilisateur a modifié le total de la quote pendant qu'il était dans la plateforme So Colissimo
            // (via un autre onglet par exemple), on recharge le tunnel de commande (comportement natif de l'onglet paiement)
            if (!$checkoutSession->hasData('socolissimosimplicite_available_shipping_amounts')
                || !$checkoutSession->hasData('socolissimosimplicite_quote_subtotal')
                || $checkoutSession->getQuote()->getSubtotal() != $quoteTotalBeforeRedirection) {

                /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
                $helper = Mage::helper('socolissimosimplicite');
                $helper->clearCheckoutSession();
                Mage::app()->getFrontController()->getResponse()->setRedirect($helper->getRedirectUrlOnError());
            } else {
                /* @var $billingAddress Mage_Sales_Model_Quote_Address */
                $billingAddress = $checkoutSession->getQuote()->getBillingAddress();
                if ($billingAddress) {
                    // restaurer l'adresse de facturation qui a été réinitialisée à cause du rechargement de la page
                    $billingAddress->setData($checkoutSession->getData('socolissimosimplicite_billing_address'));
                    $checkoutSession->unsetData('socolissimosimplicite_billing_address');
                    $billingAddress->save();
                }

                /* @var $shippingAddress Mage_Sales_Model_Quote_Address */
                $shippingAddress = $checkoutSession->getQuote()->getShippingAddress();

                if ($shippingAddress) {
                    // restaurer l'adresse de livraison qui a été réinitialisée à cause du rechargement de la page
                    $shippingAddress->setData($checkoutSession->getData('socolissimosimplicite_shipping_address'));
                    $checkoutSession->unsetData('socolissimosimplicite_shipping_address');
                    // les données de l'adresse de livraison sont réinitialisées avec l'adresse par défaut du client
                    // lorsque l'on est sur le tunnel de commande, il faut donc mettre à jour les informations
                    // de livraison récupérées depuis So Colissimo
                    $shippingData = $checkoutSession->getData('socolissimosimplicite_shipping_data');
                    $this->_updateQuoteAndShippingAddress($shippingAddress, $shippingData);
                    $shippingAddress->save();
                }
            }
        }
    }

    /**
     * Nettoie la session du tunnel de commande
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function clearCheckoutSession($observer)
    {
        Mage::helper('socolissimosimplicite')->clearCheckoutSession();
    }

    /**
     * Sauvegarde les donnees de la commande propre a So Colissimo
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addSocoAttributesToOrder($observer)
    {
        try {
            /* @var $checkoutSession Mage_Checkout_Model_Session */
            $checkoutSession = Mage::getSingleton('checkout/session');
            $shippingData = $checkoutSession->getData('socolissimosimplicite_shipping_data');

            // on ne fait le traitement que si le mode d'expedition est socolissimo
            // (et donc qu'on a recupere les donnees de socolissimo)
            if (isset($shippingData) && count($shippingData) > 0) {
                if (isset($shippingData['DELIVERYMODE'])) {
                    $observer->getEvent()->getOrder()->setSocoProductCode($shippingData['DELIVERYMODE']);
                }

                if (isset($shippingData['CEDELIVERYINFORMATION'])) {
                    $observer->getEvent()->getOrder()->setSocoShippingInstruction($shippingData['CEDELIVERYINFORMATION']);
                }

                if (isset($shippingData['CEDOORCODE1'])) {
                    $observer->getEvent()->getOrder()->setSocoDoorCode1($shippingData['CEDOORCODE1']);
                }

                if (isset($shippingData['CEDOORCODE2'])) {
                    $observer->getEvent()->getOrder()->setSocoDoorCode2($shippingData['CEDOORCODE2']);
                }

                if (isset($shippingData['CEENTRYPHONE'])) {
                    $observer->getEvent()->getOrder()->setSocoInterphone($shippingData['CEENTRYPHONE']);
                }

                if (isset($shippingData['PRID'])) {
                    $observer->getEvent()->getOrder()->setSocoRelayPointCode($shippingData['PRID']);
                }

                if (isset($shippingData['CODERESEAU'])) {
                    $observer->getEvent()->getOrder()->setSocoNetworkCode($shippingData['CODERESEAU']);
                }

                if (isset($shippingData['CECIVILITY'])) {
                    $observer->getEvent()->getOrder()->setSocoCivility($shippingData['CECIVILITY']);
                }

                if (isset($shippingData['CEPHONENUMBER'])) {
                    $observer->getEvent()->getOrder()->setSocoPhoneNumber($shippingData['CEPHONENUMBER']);
                }

                if (isset($shippingData['CEEMAIL'])) {
                    $observer->getEvent()->getOrder()->setSocoEmail($shippingData['CEEMAIL']);
                }
            }
        } catch (Exception $e) {
            Mage::Log('Failed to save so-colissimo data : ' . print_r($shippingData, true));
        }
    }

    /**
     * Met à jour les données sur l'adresse de livraison à partir des informations retournées par So Colissimo
     *
     * @todo à remplacer car c'est exécuté à chaque requête
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param array                          $shippingData
     * @return void
     */
    protected function _updateQuoteAndShippingAddress($shippingAddress, $shippingData)
    {
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        if ($checkoutSession->hasData('socolissimosimplicite_new_address_from_socolissimo')) {
            /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
            $helper = Mage::helper('socolissimosimplicite');

            // on traite la nouvelle addresse une seule fois
            $checkoutSession->unsetData('socolissimosimplicite_new_address_from_socolissimo');

            $arrayData = array();
            $arrayData['customer_address_id'] = null;

            $street = array();
            $customerNotesArray = array();

            foreach ($shippingData as $fieldSoCo => $valueSoCo) {
                // ne pas traiter les champs vides (SoCo peut par exemple renvoyer un numéro de téléphone vide)
                if ($valueSoCo !== '') {
                    if (array_key_exists($fieldSoCo, $this->_mapFields)) {
                        $fieldMagento = $this->_mapFields[$fieldSoCo];
                        $arrayData[$fieldMagento] = $valueSoCo;
                    } else {
                        switch ($fieldSoCo) {
                            // cas civilité
                            case 'CECIVILITY':
                                $arrayData['prefix'] = $helper->getPrefixForMagento($valueSoCo);
                                break;

                            // cas livraison à domicile
                            case 'CEADRESS3':
                                $street['0'] = $valueSoCo; //mis en 1er car numéro de rue obligatoire côté SoCo
                                break;
                            case 'CEADRESS1':
                                $street['1'] = $valueSoCo;
                                break;
                            case 'CEADRESS2':
                                $street['2'] = $valueSoCo;
                                break;
                            case 'CEADRESS4':
                                $street['3'] = $valueSoCo;
                                break;

                            // cas livraison en point relais
                            case 'PRNAME':
                                $street['0'] = $valueSoCo;
                                break;
                            case 'PRCOMPLADRESS':
                                $street['1'] = $valueSoCo;
                                break;
                            case 'PRADRESS1':
                                $street['2'] = $valueSoCo;
                                break;
                            case 'PRADRESS2':
                                $street['3'] = $valueSoCo;
                                break;

                            // autres informations sur la livraison
                            case 'CEENTRYPHONE':
                                $customerNotesArray['0'] = $helper->__('Interphone :') . ' ' . $valueSoCo;
                                break;
                            case 'CEDOORCODE1':
                                $customerNotesArray['1'] = $helper->__('Code porte :') . ' ' . $valueSoCo;
                                break;
                            case 'CEDOORCODE2':
                                $customerNotesArray['2'] = $helper->__('Code porte 2 :') . ' ' . $valueSoCo;
                                break;
                            case 'CEDELIVERYINFORMATION':
                                $customerNotesArray['3'] = $valueSoCo;
                                break;

                            default:
                                break;
                        }
                    }
                }
            }

            if (!empty($customerNotesArray)) {
                $arrayData['customer_notes'] = implode("\n", $customerNotesArray);
            }

            // récuperation du mode de livraison choisi
            $deliveryMode = isset($shippingData['DELIVERYMODE'])? $shippingData['DELIVERYMODE'] : self::MODE_DOM;

            // sauvegarde dans le carnet d'adresse seulement pour le mode domicile
            if ($deliveryMode !== self::MODE_DOM) {
                $arrayData['save_in_address_book'] = 0;
            }

            // sauvegarde du mode et du montant de livraison dans la session
            $checkoutSession->setData('socolissimosimplicite_chosen_delivery_mode', $deliveryMode);
            $checkoutSession->setData('socolissimosimplicite_chosen_shipping_amount', $shippingData['DYFORWARDINGCHARGES']);

            // sauvegarder l'adresse
            $shippingAddress->addData($arrayData);
            $shippingAddress->setStreet($street);

            // relancer le calcul du prix (puisqu'il a pu changé depuis le retour de la plateforme So Colissimo)
            // voir getCalculatedPrice() de ShippingMethod
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->collectShippingRates();
        }
    }
}
