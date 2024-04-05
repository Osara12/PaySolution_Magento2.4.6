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
        type: 'custompayment',
        component: 'PaySolutions_Alipay/js/view/payment/method-renderer/payso-alipay'
      }
    );
    return Component.extend({});
  }
);