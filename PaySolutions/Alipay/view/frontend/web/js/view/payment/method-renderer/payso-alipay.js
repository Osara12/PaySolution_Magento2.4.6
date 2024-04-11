define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_Alipay/payment/payso-alipay'
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