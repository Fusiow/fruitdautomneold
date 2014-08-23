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
 * @subpackage      Config
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @author          Matthias Walter <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Core_Model
 * @subpackage      Config
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */
class Payone_Core_Model_Config_Payment_Method
    extends Payone_Core_Model_Config_AreaAbstract
    implements Payone_Core_Model_Config_Payment_Method_Interface
{
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var string
     */
    protected $scope = '';
    /**
     * @var int
     */
    protected $scope_id = 0;
    /**
     * @var string
     */
    protected $code = '';
    /**
     * @var string
     */
    protected $name = '';
    /**
     * @var int
     */
    protected $sort_order = 0;
    /**
     * @var int
     */
    protected $enabled = 0;
    /**
     * @var array
     */
    protected $fee_config = array();

    /** @var string */
    protected $mode = '';

    /**
     * @var int
     */
    protected $use_global = 0;
    /**
     * @var int
     */
    protected $mid = 0;
    /**
     * @var int
     */
    protected $portalid = 0;
    /**
     * @var int
     */
    protected $aid = 0;
    /**
     * @var string
     */
    protected $key = '';
    /**
     * @var int
     */
    protected $allowspecific = 0;
    /**
     * @var array
     */
    protected $specificcountry = array();
    /**
     * @var array
     */
    protected $allowedCountries = array();
    /**
     * @var string
     */
    protected $request_type = '';

    /**
     * @var int
     */
    protected $invoice_transmit = 0;

    /**
     * @var array
     */
    protected $types = array();
    /**
     * @var array
     */
    protected $klarna_config = array();

    /**
     * @var int
     */
    protected $check_cvc = 0;
    /**
     * @var int
     */
    protected $check_bankaccount = 0;

    /** @var string */
    protected $bankaccountcheck_type = '';

    /** @var string */
    protected $message_response_blocked = '';

    /**
     * @var array
     */
    protected $sepa_country = array();
    /**
     * @var int
     */
    protected $sepa_de_show_bank_data = 0;
    /**
     * @var int
     */
    protected $sepa_mandate_enabled = 1;
    /**
     * @var int
     */
    protected $sepa_mandate_download_enabled = 1;

    /**
     * @var int
     */
    protected $is_deleted = 0;

    /**
     * @var string
     */
    protected $minValidityPeriod = '';

    /** @var float */
    protected $minOrderTotal = 0;

    /** @var float */
    protected $maxOrderTotal = 0;

    /**
     * @var int | null
     */
    protected $parent = null;

    /**
     * @var int
     */
    protected $currency_convert = 0;

    /**
     * Check if Method can be used in Country
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        if ($this->getAllowspecific() and !in_array($country, $this->getSpecificcountry())) {
            return false;
        }
        return true;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array|bool
     */
    public function getFeeConfigForQuote(Mage_Sales_Model_Quote $quote)
    {
        // No handling fee for virtual quotes
        if($quote->isVirtual()){
            return false;
        }

        $shippingAddress = $quote->getShippingAddress();
        $country = $shippingAddress->getCountry();
        $shippingMethod = $shippingAddress->getShippingMethod();

        $feeConfigs = $this->getFeeConfig();

        if (!is_array($feeConfigs)) {
            return false;
        }

        foreach ($feeConfigs as $key => $feeConfig) {
            if (in_array($shippingMethod, $feeConfig['shipping_method']) === false) {
                unset($feeConfigs[$key]);
                continue;
            }
            if (array_key_exists('countries', $feeConfig) and in_array($country, $feeConfig['countries']) === false) {
                unset($feeConfigs[$key]);
                continue;
            }
        }

        if (count($feeConfigs) > 0) {
            return array_shift($feeConfigs);
        }
        else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isRequestAuthorization()
    {
        if ($this->getRequestType() === Payone_Api_Enum_RequestType::AUTHORIZATION) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isRequestPreauthorization()
    {
        if ($this->getRequestType() === Payone_Api_Enum_RequestType::PREAUTHORIZATION) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isInvoiceTransmitEnabled()
    {
        if ($this->getInvoiceTransmit()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAllowspecific()
    {
        if ($this->getAllowspecific()) {
            return true;
        }
        return false;
    }

    /**
     * @param int $aid
     */
    public function setAid($aid)
    {
        $this->aid = $aid;
    }

    /**
     * @return int
     */
    public function getAid()
    {
        return $this->aid;
    }

    /**
     * @param int $allowspecific
     */
    public function setAllowspecific($allowspecific)
    {
        $this->allowspecific = $allowspecific;
    }

    /**
     * @return int
     */
    public function getAllowspecific()
    {
        return $this->allowspecific;
    }

    /**
     * @param int $check_bankaccount
     */
    public function setCheckBankAccount($check_bankaccount)
    {
        $this->check_bankaccount = $check_bankaccount;
    }

    /**
     * @return int
     */
    public function getCheckBankAccount()
    {
        return $this->check_bankaccount;
    }

    /**
     * @param int $check_cvc
     */
    public function setCheckCvc($check_cvc)
    {
        $this->check_cvc = $check_cvc;
    }

    /**
     * @return int
     */
    public function getCheckCvc()
    {
        return $this->check_cvc;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return int
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param array $fee_config
     */
    public function setFeeConfig($fee_config)
    {
        $this->fee_config = $fee_config;
    }

    /**
     * @return array
     */
    public function getFeeConfig()
    {
        return $this->fee_config;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $invoice_transmit
     */
    public function setInvoiceTransmit($invoice_transmit)
    {
        $this->invoice_transmit = $invoice_transmit;
    }

    /**
     * @return int
     */
    public function getInvoiceTransmit()
    {
        return $this->invoice_transmit;
    }

    /**
     * @param int $is_deleted
     */
    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
    }

    /**
     * @return int
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param array $klarna_config
     */
    public function setKlarnaConfig($klarna_config)
    {
        $this->klarna_config = $klarna_config;
    }

    /**
     * @return array
     */
    public function getKlarnaConfig()
    {
        return $this->klarna_config;
    }

    /**
     * @param int $mid
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }

    /**
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $portalid
     */
    public function setPortalid($portalid)
    {
        $this->portalid = $portalid;
    }

    /**
     * @return int
     */
    public function getPortalid()
    {
        return $this->portalid;
    }

    /**
     * @param string $request_type
     */
    public function setRequestType($request_type)
    {
        $this->request_type = $request_type;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->request_type;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param int $scope_id
     */
    public function setScopeId($scope_id)
    {
        $this->scope_id = $scope_id;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return $this->scope_id;
    }

    /**
     * @param int $sort_order
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param array $specificcountry
     */
    public function setSpecificcountry($specificcountry)
    {
        $this->specificcountry = $specificcountry;
    }

    /**
     * @return array
     */
    public function getSpecificcountry()
    {
        return $this->specificcountry;
    }

    /**
     * @param array $allowedCountries
     */
    public function setAllowedCountries($allowedCountries)
    {
        $this->allowedCountries = $allowedCountries;
    }

    /**
     * @return array
     */
    public function getAllowedCountries()
    {
        return $this->allowedCountries;
    }

    /**
     * @param array $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param int $use_global
     */
    public function setUseGlobal($use_global)
    {
        $this->use_global = $use_global;
    }

    /**
     * @return int
     */
    public function getUseGlobal()
    {
        return $this->use_global;
    }

    /**
     * @param string $minValidityPeriod
     */
    public function setMinValidityPeriod($minValidityPeriod)
    {
        $this->minValidityPeriod = $minValidityPeriod;
    }

    /**
     * @return string
     */
    public function getMinValidityPeriod()
    {
        return $this->minValidityPeriod;
    }

    /**
     * @param float $maxOrderTotal
     */
    public function setMaxOrderTotal($maxOrderTotal)
    {
        $this->maxOrderTotal = $maxOrderTotal;
    }

    /**
     * @return float
     */
    public function getMaxOrderTotal()
    {
        return $this->maxOrderTotal;
    }

    /**
     * @param float $minOrderTotal
     */
    public function setMinOrderTotal($minOrderTotal)
    {
        $this->minOrderTotal = $minOrderTotal;
    }

    /**
     * @return float
     */
    public function getMinOrderTotal()
    {
        return $this->minOrderTotal;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $parentId
     */
    public function setParent($parentId)
    {
        $this->parent = $parentId;
    }

    /**
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        if ($this->getParent()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $message_response_blocked
     */
    public function setMessageResponseBlocked($message_response_blocked)
    {
        $this->message_response_blocked = $message_response_blocked;
    }

    /**
     * @return string
     */
    public function getMessageResponseBlocked()
    {
        return $this->message_response_blocked;
    }

    /**
     * @param array $sepaCountry
     */
    public function setSepaCountry($sepaCountry)
    {
        $this->sepa_country = $sepaCountry;
    }

    /**
     * @return array
     */
    public function getSepaCountry()
    {
        return $this->sepa_country;
    }

    /**
     * @param int $sepaDeShowBankData
     */
    public function setSepaDeShowBankData($sepaDeShowBankData)
    {
        $this->sepa_de_show_bank_data = $sepaDeShowBankData;
    }

    /**
     * @return int
     */
    public function getSepaDeShowBankData()
    {
        return $this->sepa_de_show_bank_data;
    }

    /**
     * @param int $sepaMandateEnabled
     */
    public function setSepaMandateEnabled($sepaMandateEnabled)
    {
        $this->sepa_mandate_enabled = $sepaMandateEnabled;
    }

    /**
     * @return int
     */
    public function getSepaMandateEnabled()
    {
        return $this->sepa_mandate_enabled;
    }

    /**
     * @param int $sepaMandateDownloadEnabled
     */
    public function setSepaMandateDownloadEnabled($sepaMandateDownloadEnabled)
    {
        $this->sepa_mandate_download_enabled = $sepaMandateDownloadEnabled;
    }

    /**
     * @return int
     */
    public function getSepaMandateDownloadEnabled()
    {
        return $this->sepa_mandate_download_enabled;
    }

    /**
     * @param string $bankaccountcheck_type
     */
    public function setBankAccountCheckType($bankaccountcheck_type)
    {
        $this->bankaccountcheck_type = $bankaccountcheck_type;
    }

    /**
     * @return string
     */
    public function getBankAccountCheckType()
    {
        return $this->bankaccountcheck_type;
    }

    /**
     * @return bool
     */
    public function isBankAccountCheckEnabled()
    {
        if ($this->getCheckBankAccount()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isSepaMandateEnabled()
    {
        if ($this->getSepaMandateEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isSepaDeShowBankDataEnabled() {
        if ($this->getSepaDeShowBankData()) {
            return true;
        }
        return false;
    }

    /**
     * @param int $currency_convert
     */
    public function setCurrencyConvert($currency_convert)
    {
        $this->currency_convert = $currency_convert;
    }

    /**
     * @return int
     */
    public function getCurrencyConvert()
    {
        return $this->currency_convert;
    }
}
