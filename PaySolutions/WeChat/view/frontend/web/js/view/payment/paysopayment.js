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
        component: 'PaySolutions_WeChat/js/view/payment/method-renderer/payso-wechat'
      }
    );
    return Component.extend({});
  }
);