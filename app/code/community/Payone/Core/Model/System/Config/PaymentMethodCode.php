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
 * @subpackage      System
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @author          Matthias Walter <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Core_Model
 * @subpackage      System
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */
class Payone_Core_Model_System_Config_PaymentMethodCode extends Payone_Core_Model_System_Config_Abstract
{
    const PREFIX = 'payone_';

    const ADVANCEPAYMENT = 'payone_advance_payment';
    const CASHONDELIVERY = 'payone_cash_on_delivery';
    const CREDITCARD = 'payone_creditcard';
    const DEBITPAYMENT = 'payone_debit_payment';
    const FINANCING = 'payone_financing';
    const INVOICE = 'payone_invoice';
    const SAFEINVOICE = 'payone_safe_invoice';
    const ONLINEBANKTRANSFER = 'payone_online_bank_transfer';
    const WALLET = 'payone_wallet';

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ADVANCEPAYMENT => 'Advance Payment',
            self::CASHONDELIVERY => 'Cash on Delivery',
            self::CREDITCARD => 'Creditcard',
            self::DEBITPAYMENT => 'Debit Payment',
            self::FINANCING => 'Financing',
            self::INVOICE => 'Invoice',
            self::SAFEINVOICE => 'Safe Invoice',
            self::ONLINEBANKTRANSFER => 'Online Bank Transfer',
            self::WALLET => 'Wallet'
        );
    }
}