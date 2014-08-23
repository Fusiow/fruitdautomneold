<?php
/**
 * Transaction entre Magento et So Colissimo
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Model_Transaction extends Mage_Core_Model_Abstract
{
    /**
     * Module helper
     *
     * @var LaPoste_SoColissimoSimplicite_Helper_Data
     */
    protected $_helper;

    /**
     * Constructeur par défaut
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('socolissimosimplicite/transaction');
    }

    /**
     * Retourne le helper principal du module
     *
     * @return LaPoste_SoColissimoSimplicite_Helper_Data
     */
    public function helper()
    {
        if (is_null($this->_helper)) {
            $this->_helper = Mage::helper('socolissimosimplicite');
        }

        return $this->_helper;
    }

    /**
     * Crée et sauvegarde les données de la transaction avec So Colissimo (avant l'accès à la plateforme)
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return LaPoste_SoColissimoSimplicite_Model_Transaction
     */
    public function create($shippingAddress)
    {
        $now = date('Y-m-d h:i:s');
        $this->setCreatedAt($now);
        $this->setIsDone(0);

        try {
            $this->setQuoteId($shippingAddress->getQuoteId());
            $this->save(); // 1ère sauvegarde pour récupérer le transaction_id pour la signature

            $this->setSignature($this->_getGeneratedSignature($shippingAddress));
            $this->save();
        } catch (Exception $e) {
            throw new Exception($e);
        }

        return $this;
    }

    /**
     * Génère la signature requise pour l'accès à la plateforme So Colissimo (front-office).
     *
     * SPECIFICATIONS FOURNIES PAR LA POSTE :
     *
     * La signature permet de garantir que les données transmises à la Page SO
     * « Modes de livraison » ne subiront pas de modifications ultérieures.
     *
     * La signature permet un contrôle de l’accès à la page SO « Modes de livraison ».
     *
     * La signature doit être :
     * - renseignée
     * - correspondre à celle calculée par La Poste ColiPoste (à noter que celle-ci doit
     *   être calculée en minuscule)
     *
     * Si ce n’est pas le cas, l’accès à la Page SO « Modes de livraison » ne sera pas
     * autorisé. L’internaute sera redirigé vers l'url retour Echec. Un code erreur sera
     * transmis.
     *
     * Il s’agit d’appliquer l’algorithme SHA sur la concaténation des champs composant
     * la signature. La clé SHA doit être également concaténée à la fin de la chaîne.
     *
     * Certains des champs ci-dessous ne sont pas obligatoires. S’ils ne sont pas transmis
     * par le formulaire d'envoi des données à la plateforme, ils ne doivent pas être pris
     * en compte dans le calcul de la signature.
     * FIN SPECIFICATIONS
     *
     * @param LaPoste_SoColissimoSimplicite_Model_Carrier_ShippingMethod $method
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return string signature de 160 bits (40 caractères)
     */
    protected function _getGeneratedSignature($shippingAddress)
    {
        $stringToConvert = '';

        foreach ($this->helper()->getFieldsToSend($this->getId(), $shippingAddress) as $value) {
            // on ignore les champs vides (pas obligatoire mais cela permet d'envoyer moins de données dans le form)
            if ($value !== null && $value !== '' && $value !== false) {
                $stringToConvert .= utf8_decode($value);
            }
        }

        return sha1($stringToConvert);
    }
}
