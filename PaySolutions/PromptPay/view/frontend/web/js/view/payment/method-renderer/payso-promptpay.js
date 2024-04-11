define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_PromptPay/js/action/set-payment-method-action'
    ],
    function (Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PaySolutions_PromptPay/payment/payso-promptpay'
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