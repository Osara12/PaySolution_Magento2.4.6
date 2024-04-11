define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'jquery',
    ],
    function (Component, url, $) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_TrueWallet/payment/payso-truewallet'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                url.setBaseUrl(BASE_URL);
                var link = url.build('paysopayment/jumpapp/request');
                $.mage.redirect(link);
                return false;
            },
        });
    }
);  