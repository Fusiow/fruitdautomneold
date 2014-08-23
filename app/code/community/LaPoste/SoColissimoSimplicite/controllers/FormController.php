<?php
/**
 * Contrôleur pour les échanges avec l'interface So Colissimo en front office.
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_FormController extends Mage_Core_Controller_Front_Action
{
    /**
     * Soumet le formulaire pour l'accès à So Colissimo
     *
     * @todo check postal code and country before send (only France, Monaco et Andorre)
     * @return void
     */
    public function sendAction()
    {
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
        $helper = Mage::helper('socolissimosimplicite');

        // nettoyer la session
        $helper->clearCheckoutSession(false);

        // vérifier la disponibilité de la plateforme, si ce n'est pas le cas
        // sélectionner la livraison à domicile et passer à l'étape suivante
        $serviceUnavailableRedirect = null;
        if ($helper->getServiceIsAvailable() && $helper->getServiceIsAvailableUrl() != '') {
            $serviceAvailable = $helper->checkServiceAvailability();
            // passer à l'étape suivante avec le mode livraison à domicile
            if (!$serviceAvailable) {
                $checkoutSession->setData('socolissimosimplicite_checkout_onepage_nextstep', 'payment');
                $serviceUnavailableRedirect = Mage::getUrl('checkout/onepage');
            }
        }

        // récupération de l'adresse de livraison de la commande en cours
        $shippingAddress = $this->getShippingAddress();

        // sauvegarde des adressses choisies dans la session car les données seront réinitialisées au rechargement du tunnel
        $checkoutSession->setData('socolissimosimplicite_shipping_address', $shippingAddress->getData());
        $checkoutSession->setData('socolissimosimplicite_billing_address', $this->getBillingAddress()->getData());

        // on relance le calcul du prix de livraison pour gérer le cas du du retour en arrière et du choix du mode classique
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();

        // création d'une nouvelle transaction à partir de l'adresse de livraison.
        $transaction = Mage::getModel('socolissimosimplicite/transaction')->create($shippingAddress);
        $checkoutSession->setData('socolissimosimplicite_checkout_onepage_transactionid', $transaction->getId());

        $this->loadLayout();

        // passage de données à la vue
        $this->getLayout()->getBlock('form.socolissimosimplicite')
            ->setShippingAddress($shippingAddress)
            ->setTransaction($transaction)
            ->setServiceUnavailableRedirect($serviceUnavailableRedirect);

        $this->renderLayout();
    }

    /**
     * Succès suite au retour de So Colissimo
     *
     * @todo use TRPARAMPLUS to pass / get "payment" ?
     * @todo deal with "error code" on validation (data truncation and others)
     * @return void
     */
    public function successAction()
    {
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        // permettre de revenir en arrière pour change le mode de livraison
        $checkoutSession->unsetData('socolissimosimplicite_from_socolissimo');

        // si l'adresse de livraison a changée, mettre à jour la signature
        $this->_updateSignature();

        // sauvegarde des données retournées par So Colissimo en session (choix du mode de paiement)
        $this->_saveSoColissimoData($checkoutSession);

        // sauvegarde de l'étape suivante en session
        $checkoutSession->setData('socolissimosimplicite_checkout_onepage_nextstep', 'payment');

        $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
    }

    /**
     * Échec lors de la soumission du formulaire d'accès à So Colissimo
     *
     * @return void
     */
    public function failureAction()
    {
        /* @var $helper LaPoste_SoColissimoSimplicite_Helper_Data */
        $helper = Mage::helper('socolissimosimplicite');
        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        // permettre de revenir en arrière pour change le mode de livraison
        $checkoutSession->unsetData('socolissimosimplicite_from_socolissimo');

        // sauvegarde de l'étape suivante en session  (choix de la méthode de livraison)
        $checkoutSession->setData('socolissimosimplicite_checkout_onepage_nextstep', 'shipping_method');

        // récupérer le code erreur et afficher le message correspondant
        $errCode = trim($this->getRequest()->getPost('ERRORCODE'));
        $errCodeToLabel = array(
            '001' => $helper->__('Identifiant FO manquant'),
            '002' => $helper->__('Identifiant FO incorrect'),
            '003' => $helper->__('Client non autorisé'),
            '004' => $helper->__('Champs obligatoire manquant'),
            '006' => $helper->__('Signature manquante'),
            '007' => $helper->__('Signature invalide'),
            '008' => $helper->__('Code postal invalide'),
            '009' => $helper->__('Format url retour Validation incorrect'),
            '010' => $helper->__('Format url retour Echec incorrect'),
            '011' => $helper->__('Numéro de transaction non valide'),
            '012' => $helper->__('Format des frais d’expédition incorrect'),
            '015' => $helper->__('Serveur applicatif non disponible'),
            '016' => $helper->__('SGBD non disponible')
        );

        // ajout d'un message d'erreur dans la session du tunnel de commande
        $msg = array_key_exists($errCode, $errCodeToLabel) ? $errCodeToLabel[$errCode] : $helper->__('Erreur non identifiée');
        $checkoutSession->addError($msg);

        $this->_redirect('checkout/cart');
    }

    /**
     * Retourne l'adresse de livraison de la commande en cours
     *
     * @param string $quoteId
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress($quoteId = false)
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
    }

    /**
     * Retourne l'adresse de facturation de la commande en cours
     *
     * @param string $quoteId
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress($quoteId = false)
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
    }

    /**
     * Vérifie si la signature a changée (ie l'adresse de livraison a changée), dans ce cas mettre à jour la signature
     *
     * @return void
     */
    protected function _updateSignature()
    {
        $newSignature = $this->getRequest()->getParam('SIGNATURE');
        // signature invalide
        if (empty($newSignature)) {
            throw new Exception(Mage::helper('socolissimosimplicite')->__('SoColissimo : la signature retournée par colissimo est vide'));
        }

        $transactionId = $this->getRequest()->getParam('ORDERID');
        $transactions = Mage::getModel('socolissimosimplicite/transaction')
            ->getCollection()
            ->addFieldToFilter('transaction_id', $transactionId);

        // transaction inexistante
        if ($transactions->getSize() == 0) {
            throw new Exception(Mage::helper('socolissimosimplicite')->__('SoColissimo : la transaction avec colissimo (id="%s") n\'est pas valide car inconnue de Magento', $transactionId));
        }

        // mettre à jour la signature si elle a changée
        $transaction = current(array_values($transactions->getItems()));
        if ($transaction->getSignature() != $newSignature) {
            $transaction->setSignature($newSignature);
            $transaction->save();
        }
    }

    /**
     * Enregistre les données retournées par So Colissimo en session,
     * afin de mettre à jour les données de livraison ultérieurement
     *
     * @param Mage_Checkout_Model_Session $checkoutSession
     * @return void
     */
    protected function _saveSoColissimoData($checkoutSession)
    {
        // sauve params adresse livraison
        $allParams = $this->getRequest()->getParams();
        $paramKeysToNotSave = array_flip(
            array(
                'TRRETURNURLKO',
                'PUDOFOID',
                'TRPARAMPLUS',
                'TRADERCOMPANYNAME',
                'ORDERID',
                'SIGNATURE',
            )
        );

        $shippingData = array_diff_key($allParams, $paramKeysToNotSave);
        $checkoutSession->setData('socolissimosimplicite_shipping_data', $shippingData);

        // sauve transaction id, pour éviter de créer une nouvelle transaction si retour en arrière
        $transactionId = $this->getRequest()->getParam('ORDERID');
        $checkoutSession->setData('socolissimosimplicite_checkout_onepage_transactionid', $transactionId);

        // indique que l'adresse vient de socolissimo
        $checkoutSession->setData('socolissimosimplicite_new_address_from_socolissimo', true);
    }
}
