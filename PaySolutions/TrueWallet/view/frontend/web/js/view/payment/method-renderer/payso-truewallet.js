define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_WeChat/js/action/set-payment-method-action'
    ],
    function (Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PaySolutions_TrueWallet/payment/payso-truewallet'
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