<?php
/**
 * Formulaire de soumission pour l'accès à l'interface So Colissimo en front office
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Block_Form extends Mage_Core_Block_Template
{
    protected $_carrierInstance;

    /**
     * Retourne l'adresse de livraison
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->getData('shipping_address');
    }

    /**
     * Retourne la transaction
     *
     * @return LaPoste_SoColissimoSimplicite_Model_Transaction
     */
    public function getTransaction()
    {
        return $this->getData('transaction');
    }

    /**
     * Retourne l'url de redirection quand le service socolissimo est indisponible
     *
     * @return string
     */
    public function getServiceUnavailableRedirect()
    {
        return $this->getData('service_unavailable_redirect');
    }

    /**
     * Retourne le message à afficher lors de la redirection vers le front de So Colissimo Simplicité
     *
     * @return string
     */
    public function getRedirectMessage()
    {
        return $this->helper('socolissimosimplicite')->getRedirectMessage();
    }

    /**
     * Retourne l'identifiant FO d'accès à la plateforme So Colissimo (front-office)
     *
     * @return string
     */
    public function getAccountID()
    {
        return $this->helper('socolissimosimplicite')->getAccountId();
    }

    /**
     * Retourne la clé de cryptage pour l'accès à la plateforme So Colissimo (front-office)
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->helper('socolissimosimplicite')->getEncryptionKey();
    }

    /**
     * Retourne l'URL d'accès à la plateforme So Colissimo (front-office)
     *
     * @return string
     */
    public function getUrlFoWithReturnUrlKo()
    {
        $urlFo = $this->helper('socolissimosimplicite')->getUrlFo();
        $returnUrlKo = $this->helper('socolissimosimplicite')->getFailureUrl();

        return $urlFo.'?trReturnUrlKo=' . $returnUrlKo;
    }

    /**
     * Retourne l'url complète appelée en cas de succès
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->helper('socolissimosimplicite')->getSuccessUrl();
    }
}
