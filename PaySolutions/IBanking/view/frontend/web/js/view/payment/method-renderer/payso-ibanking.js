define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'PaySolutions_IBanking/js/action/set-payment-method-action'
    ],
    function ($, Component, quote, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PaySolutions_IBanking/payment/payso-ibanking',
                redirectAfterPlaceOrder: false,
                banktype:'',
            },
           /** @inheritdoc */
           initObservable: function () {
            this._super()
                .observe(['banktype']);
            return this;
        },
        getData: function () {
            return {
                method: this.item.method,
                // 'mpesanumber': this.mpesaNumber(),
                'additional_data': {
                    'banktype': $('input[name="banktype"]:checked').val()
                }
            };
        },
        getMailingAddress: function () {
            return window.checkoutConfig.payment.checkmo.mailingAddress;
        },
        afterPlaceOrder: function () {
            console.log($('input[name="banktype"]:checked').val())
            setPaymentMethodAction($('input[name="banktype"]:checked').val());
            return false;
        },
    });
}
); 