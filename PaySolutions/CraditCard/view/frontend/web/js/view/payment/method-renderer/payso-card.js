define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'PaySolutions_CraditCard/js/action/set-payment-method-action'
    ],
    function ($, Component, quote, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'PaySolutions_CraditCard/payment/payso-card',
                cardtype:'',
            },
            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(['cardtype']);
                return this;
            },
            getData: function () {
                return {
                    method: this.item.method,
                    // 'mpesanumber': this.mpesaNumber(),
                    'additional_data': {
                        'cardtype': $('input[name="cardtype"]:checked').val()
                    }
                };
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            afterPlaceOrder: function () {
                console.log($('input[name="cardtype"]:checked').val())
                setPaymentMethodAction($('input[name="cardtype"]:checked').val());
                return false;
            },
        });
    }
);