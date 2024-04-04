define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'PaySolutions_PromptPay/payment/payso_promptpay_template'
            }
        });
    }
);