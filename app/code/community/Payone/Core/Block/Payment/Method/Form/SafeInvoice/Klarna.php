<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License (GPL 3)
 * that is bundled with this package in the file LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Payone_Core to newer
 * versions in the future. If you wish to customize Payone_Core for your
 * needs please refer to http://www.payone.de for more information.
 *
 * @category        Payone
 * @package         Payone_Core_Block
 * @subpackage      Payment
 * @copyright       Copyright (c) 2013 <info@noovias.com> - www.noovias.com
 * @author          Alexander Dite <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Core_Block
 * @subpackage      Payment
 * @copyright       Copyright (c) 2013 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

class Payone_Core_Block_Payment_Method_Form_SafeInvoice_Klarna
    extends Mage_Core_Block_Template
{
    /** @var  Payone_Core_Model_Config_Payment_Method_Interface */
    protected $paymentMethodConfig;
    /** @var  Mage_Sales_Model_Quote */
    protected $quote;
    /** @var Payone_Core_Model_Factory */
    protected $factory = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payone/core/payment/method/form/safe_invoice/klarna.phtml');
    }

    /**
     * @param \Payone_Core_Model_Config_Payment_Method_Interface $paymentMethodConfig
     */
    public function setPaymentMethodConfig($paymentMethodConfig)
    {
        $this->paymentMethodConfig = $paymentMethodConfig;
    }

    /**
     * @return \Payone_Core_Model_Config_Payment_Method_Interface
     */
    public function getPaymentMethodConfig()
    {
        return $this->paymentMethodConfig;
    }

    /**
     * @param \Mage_Sales_Model_Quote $quote
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
    }

    /**
     * @return \Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @return bool
     */
    public function isAgreementCheckboxRequired()
    {
        $country = $this->getCountry();
        if ($country == 'AT' or $country == 'DE') {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getKlarnaStoreId()
    {
        $country = $this->getCountry();
        $klarnaConfig = $this->getPaymentMethodConfig()->getKlarnaConfig();
        if (empty($klarnaConfig)) {
            return '';
        }
        foreach ($klarnaConfig as $config) {
            if (isset($config['countries']) and is_array($config['countries'])
                    and in_array($country, $config['countries'])
            ) {
                return $config['klarna_store_id'];
            }
        }
        return '';
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        return $billing->getCountry();
    }

    /**
     * @return bool
     */
    public function isDobRequired()
    {
        // required for all countries
        // required only if customer didn't enter Dob in previous checkout step
        $customerDob = $this->getQuote()->getCustomerDob();
        if (empty($customerDob)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canShowAdditionalFields()
    {
        $country = $this->getCountry();
        if (empty($country)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isGenderRequired()
    {
        // required only for Austria (AT), Germany (DE) and Netherlands (NL)
        $country = $this->getCountry();
        if ($country != 'AT' and $country != 'DE' and $country != 'NL') {
            return false;
        }
        // required only if customer didn't enter gender in his customer account or previous checkout step
        $customerGender = $this->getQuote()->getCustomerGender();
        if (empty($customerGender)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getGenderOptions()
    {
        $customerResource = $this->getFactory()->getSingletonCustomerResource();
        $options = $customerResource->getAttribute('gender')->getSource()->getAllOptions();
        return $options;
    }

    /**
     * @return bool
     */
    public function isPersonalidRequired()
    {
        $country = $this->getCountry();
        // mandatory for Denmark (DK), Finland(FI), Norway (NO) and Sweden (SE)
        if ($country == 'DK' or $country == 'FI' or $country == 'NO' or $country == 'SE') {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isShippingAddressAdditionRequired()
    {
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $country = $shippingAddress->getCountry();
        // required only for Netherlands (NL)
        if ($country != 'NL') {
            return false;
        }

        $addressAdditionShipping = $shippingAddress->getStreet(2);
        if (empty($addressAdditionShipping)) {
            return true;
        }

        return false;
    }

    public function isBillingAddressAdditionRequired()
    {
        $billingAddress = $this->getQuote()->getBillingAddress();
        $country = $billingAddress->getCountry();
        // required only for Netherlands (NL)
        if ($country != 'NL') {
            return false;
        }
        $addressAdditionBilling = $billingAddress->getStreet(2);
        if (empty($addressAdditionBilling)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isTelephoneRequired()
    {
        // telephone is mandatory for any country in case of Klarna
        $telephone = $this->getQuote()->getBillingAddress()->getTelephone();
        if (empty($telephone)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Payone_Core_Model_Factory $factory
     */
    public function setFactory(Payone_Core_Model_Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return \Payone_Core_Model_Factory
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = new Payone_Core_Model_Factory();
        }
        return $this->factory;
    }
}