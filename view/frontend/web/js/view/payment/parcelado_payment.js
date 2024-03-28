define(
  [
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
  ],
  function (Component,
    rendererList) {
    'use strict';
    const isEnabled = window.checkoutConfig.payment.parcelado.enable;
    if (isEnabled) {
      rendererList.push(
        {
          type: 'parcelado_payment',
          component: 'Parceladousa_Payment/js/view/payment/method-renderer/parcelado_payment'
        }
      );
    }
    return Component.extend({});
  }
);
