<?php
/**
 * Réécriture du bloc gérant le tunnel de commande
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaPoste_SoColissimoSimplicite_Block_Onepage extends Mage_Checkout_Block_Onepage
{
    /**
     * Etape du tunnel de commande à afficher
     *
     * @var string
     */
    protected $_stepToActivate;

    /**
     * Met à jour les étapes précédentes lorsqu'une étape autre que la 1ère étape est à afficher
     *
     * @return self
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // on cherche en session si une étape est à afficher directement
        $checkout = Mage::getSingleton('checkout/session');
        $this->_stepToActivate = false;

        if ($checkout->getData('socolissimosimplicite_checkout_onepage_nextstep')) {
            $this->_stepToActivate = $checkout->getData('socolissimosimplicite_checkout_onepage_nextstep');
        }

        $steps = $this->getSteps();

        // on force l'activation des étapes précédant l'étape que l'on souhaite afficher + de l'étape à afficher
        if ($this->_stepToActivate && array_key_exists($this->_stepToActivate, $steps)) {
            foreach ($steps as $key => $data) {
                // permet d'afficher le contenu de l'étape en cours et de revenir sur les étapes précédentes
                $this->getCheckout()->setStepData($key, 'allow', true);

                if ($key === $this->_stepToActivate) {
                    break;
                }

                // permet d'afficher la progression
                $this->getCheckout()->setStepData($key, 'complete', true);
            }
        }

        return $this;
    }

    /**
     * Retourne le code de l'étape à afficher dans le tunnel de commande
     *
     * @return string
     */
    public function getActiveStep()
    {
        if ($this->_stepToActivate) {
            $activeStep = $this->_stepToActivate;
        } elseif ($this->isCustomerLoggedIn()) {
            $activeStep = 'billing';
        } else {
            $activeStep = 'login';
        }

        return $activeStep;
    }
}
