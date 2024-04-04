define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'PaySolutions_CraditCard/js/action/set-payment-method-action'
    ],
    function (ko, $, Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_CraditCard/payment/paysopayment'
            },
            afterPlaceOrder: function () {
                setPaymentMethodAction(this.messageContainer);
                return false;
            }
        });
    }
);
