define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_CraditCard/js/action/set-payment-method-action'
    ],
    function (Component, setPaymentMethodAction) {
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
                setPaymentMethodAction(this.messageContainer);
                return false;
            },
        });
    }
);