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
        type: 'payso_truewallet',
        component: 'PaySolutions_TrueWallet/js/view/payment/method-renderer/payso-truewallet'
      }
    );
    return Component.extend({});
  }
);