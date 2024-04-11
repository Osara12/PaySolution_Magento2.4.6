define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_Bill/js/action/set-payment-method-action'

    ],
    function (Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PaySolutions_Bill/payment/payso-bill'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                setPaymentMethodAction();
                return false;
            },
        });
    }
);  