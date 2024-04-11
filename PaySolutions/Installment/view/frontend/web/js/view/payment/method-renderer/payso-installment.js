define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_Installment/js/action/set-payment-method-action'
    ],
    function (Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PaySolutions_Installment/payment/payso-installment'
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