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
class LaPoste_SoColissimoSimplicite_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructeur
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('socolissimosimplicite/transaction', 'transaction_id');
    }
}
