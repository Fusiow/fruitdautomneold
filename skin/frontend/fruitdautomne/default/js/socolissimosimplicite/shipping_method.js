/**
 * LaPoste_SoColissimoSimplicite
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var SocoShippingMethod = Class.create();
SocoShippingMethod.prototype = {
    /**
     * Initialisation
     */
    initialize: function(url, options) {
        this.options = Object.extend({
            cancelLabel: '',
            rateCode: ''
        }, options);

        this.url = url;
        this.savedAllowedSteps = [];
    },

    /**
     * Point d'entrée pour le choix du mode de livraison So Colissimo
     */ 
    save: function() {
        result = false;

        if ($('socolissimo-error') === null && checkout.loadWaiting == false && shippingMethod.validate()) {
            result = true;
            checkout.setLoadWaiting('shipping-method');
            new Ajax.Request(
                shippingMethod.saveUrl,
                {
                    method:'post',
                    onSuccess: this.displayIFrame.bind(this),
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(shippingMethod.form)
                }
            );
        }

        return result;
    },

    /**
     * Empêche Magento de passer à l'étape suivante
     */
    freezeSteps: function() {
        this.savedAllowedSteps = [];

        var steps = $('checkoutSteps').children;
        for (var i=0; i<steps.length; i++) {
            if (steps[i].hasClassName('allow')) {
                this.savedAllowedSteps[i] = true;
                steps[i].removeClassName('allow');
            } else {
                this.savedAllowedSteps[i] = false;
            }
        }
    },

    /**
     * Annule le blocage du passage à l'étape suivante
     */
    unfreezeSteps: function() {
        if (typeof(this.savedAllowedSteps) !== 'undefined') {
            var steps = $('checkoutSteps').children;
            for (var i=0; i<steps.length; i++) {
                if (this.savedAllowedSteps[i] === true) {
                    steps[i].addClassName('allow');
                }
            }
        }
    },

    /**
     * Affichage de l'IFrame So Colissimo
     */
    displayIFrame: function() {
        // création du container de l'IFrame
        var socoIFrameContainer = $('socolissimosimplicite_iframe_wrapper');
        if (socoIFrameContainer === null) {
            socoIFrameContainer = new Element('div', {id: 'socolissimosimplicite_iframe_wrapper'});
            $$('input[value="' + this.options.rateCode + '"]').first().up().appendChild(socoIFrameContainer);
        }

        // création de l'IFrame
        socoIFrameContainer.appendChild(new Element('iframe', {frameBorder: 0, width: '572px', height: '1100px', src: this.url}));
        var button = new Element('button', {'class': 'button', type: 'button'}).update('<span><span>' + this.options.cancelLabel + '</span></span>');
        Event.observe(button, 'click', this.cancel.bind(this));
        socoIFrameContainer.appendChild(button);

        // désactivation des modes de livraison pendant que l'IFrame est affichée
        var methods = $$('input[name="shipping_method"]');
        for (var j=0; j<methods.length; j++) {
            methods[j].disabled = 'disabled';
        }

        // empêche Magento de passer à l'étape suivante (paiement)
        this.freezeSteps();
        checkout.setLoadWaiting('shipping-method');
        $('shipping-method-please-wait').hide();
    },

    /**
     * Cancel So Cocolissimo IFrame and reenable disabled checkout feature
     */
    cancel: function() {
        // réactivation des modes de livraison
        var methods = $$('input[name="shipping_method"]');
        for (var i=0; i<methods.length; i++) {
            methods[i].disabled = '';
        }

        // suppression de l'IFrame
        var socoIframeContainer = $('socolissimosimplicite_iframe_wrapper');
        while (socoIframeContainer.firstChild) {
            socoIframeContainer.removeChild(socoIframeContainer.firstChild);
        }

        // annulation du blocage du passage à l'étape suivante
        shippingMethod.resetLoadWaiting();
        this.unfreezeSteps();
    }
};
