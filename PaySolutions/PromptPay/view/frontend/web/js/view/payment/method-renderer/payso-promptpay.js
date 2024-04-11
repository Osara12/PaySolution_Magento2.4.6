define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'PaySolutions_PromptPay/js/action/set-payment-method-action'
    ],
    function ($, Component, quote, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_PromptPay/payment/payso-card',
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
