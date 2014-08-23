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
 * @package         Payone_Core_Model
 * @subpackage      Sales
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @author          Matthias Walter <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Core_Model
 * @subpackage      Sales
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */
class Payone_Core_Model_Sales_Quote_Address_Total_Fee
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /** @var Payone_Core_Model_Factory */
    protected $factory = null;


    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $payment = $quote->getPayment();

        if($address->getAddressType() === 'billing')
            return $this;

        $configId = $payment->getPayoneConfigPaymentMethodId();
        if (empty($configId)) {
            return $this;
        }

        $config = $this->helperConfig()->getConfigPaymentMethodById($configId, $quote->getStoreId());
        if (empty($config)) {
            return $this;
        }

        $feeConfig = $config->getFeeConfigForQuote($quote);
        if (!is_array($feeConfig) or !array_key_exists('fee_config', $feeConfig)) {
            return $this;
        }

        $paymentFee = $feeConfig['fee_config'];
        $oldShippingAmount = $address->getBaseShippingAmount();
        $newShippingAmount = $oldShippingAmount + $paymentFee;

        $address->setBaseShippingAmount($newShippingAmount);
        $address->setShippingAmount(
            $quote->getStore()->convertPrice($newShippingAmount, false)
        );

        return parent::collect($address);
    }

    /**
     *
     * @return Payone_Core_Model_Factory
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = new Payone_Core_Model_Factory();
        }
        return $this->factory;
    }

    /**
     *
     * @param Payone_Core_Model_Factory $factory
     */
    public function setFactory(Payone_Core_Model_Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return Payone_Core_Helper_Config
     */
    protected function helperConfig()
    {
        return $this->getFactory()->helperConfig();
    }

}