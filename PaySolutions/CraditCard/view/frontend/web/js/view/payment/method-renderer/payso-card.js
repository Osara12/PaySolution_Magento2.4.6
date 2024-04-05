define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_CraditCard/js/action/set-payment-method-action'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_CraditCard/payment/payso-card'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                url.setBaseUrl(BASE_URL);
                var link = url.build('shopeepay/jumpapp/request');
                $.mage.redirect(link); //redirect to Request page after place order
                return false;
            },
        });
    }
);