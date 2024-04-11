define(
    [
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_Alipay/js/action/set-payment-method-action'
    ],
    function (Component, setPaymentMethodAction) {
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
            setPaymentMethodAction();
            return false;
        },
    });
}
);