<?php
/**
 * IFrame So Colissimo
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Block_Iframe extends Mage_Core_Block_Template
{
    /**
     * Retourne la session checkout
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Vérifie si on doit charger manuellement les étapes shipping_method et payment
     * après soumission du formulaire de l'IFrame So Colissimo
     *
     * @return bool
     */
    public function hasToLoadShippingMethodAndPayment()
    {
        return $this->getCheckoutSession()->getData('socolissimosimplicite_checkout_onepage_nextstep')
            && method_exists('Mage', 'getEdition')
            && ((version_compare(Mage::getVersion(), '1.8.0.0', '>=') && Mage::getEdition() === Mage::EDITION_COMMUNITY)
            || (version_compare(Mage::getVersion(), '1.13.0.0', '>=') && Mage::getEdition() === Mage::EDITION_ENTERPRISE));
    }

    /**
     * Retourne le contenu html de l'étape shipping_method
     *
     * @return string
     */
    public function getShippingMethodHtml()
    {
        /* @var $block Mage_Checkout_Block_Onepage_Shipping_Method_Available */
        $block = $this->getLayout()->createBlock(
            'checkout/onepage_shipping_method_available',
            'socolissimosiplicite.dynamic.shipping_method',
            array('template' => 'checkout/onepage/shipping_method/available.phtml')
        );

        return $block->toHtml();
    }

    /**
     * Retourn le contenu html de l'étape payment
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        /* @var $block Mage_Checkout_Block_Onepage_Payment_Methods */
        $block = $this->getLayout()->createBlock(
            'checkout/onepage_payment_methods',
            'socolissimosiplicite.dynamic.payment',
            array('template' => 'checkout/onepage/payment/methods.phtml')
        );

        $block->setMethodFormTemplate('purchaseorder', 'payment/form/purchaseorder.phtml');

        return $block->toHtml();
    }
}
