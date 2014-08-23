<?php
/**
 * LaPoste_SoColissimoSimplicite
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Model_System_Config_Source_Shipping_Amountbasetype
{
    /**
     * Valorise les options de la liste déroulante
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'fixed',
                'label' => Mage::helper('socolissimosimplicite')->__('Tarif unique')),
            array(
                'value' => 'per_weight',
                'label' => Mage::helper('socolissimosimplicite')->__('Tarif selon poids')),
            array(
                'value' => 'per_amount',
                'label' => Mage::helper('socolissimosimplicite')->__('Tarif selon sous-total du panier')),
        );
    }
}
