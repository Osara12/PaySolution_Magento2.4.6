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
        type: 'payso_ibanking',
        component: 'PaySolutions_IBanking/js/view/payment/method-renderer/payso-ibanking'
      }
    );
    return Component.extend({});
  }
);