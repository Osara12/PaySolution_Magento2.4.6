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
                type: 'payso_payment',
                component: 'PaySolutions_PsPayments/js/view/payment/method-renderer/payso_payment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
