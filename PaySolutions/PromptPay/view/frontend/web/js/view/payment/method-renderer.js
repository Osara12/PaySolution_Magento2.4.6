define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'payso_promptpay',
                component: 'PaySolutions_PromptPay/js/view/payment/method-renderer/payso_promptpay'
            }
        );
        return Component.extend({});
    }
);