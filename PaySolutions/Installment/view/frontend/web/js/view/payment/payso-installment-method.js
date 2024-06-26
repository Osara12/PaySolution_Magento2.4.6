define(
  [
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
  ],
  function (Component,
            rendererList) {
    'use strict';
    rendererList.push(
      {
        type: 'payso_installment',
        component: 'PaySolutions_Installment/js/view/payment/method-renderer/payso-installment'
      }
    );
    return Component.extend({});
  }
);