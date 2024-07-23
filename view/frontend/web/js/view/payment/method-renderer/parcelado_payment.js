define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default'
    ], function (ko, quote, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Parceladousa_Payment/payment/parcelado_payment'
            },

            afterPlaceOrder: function () {

                let customer = JSON.stringify(window.checkoutConfig.customerData);
                let totals = JSON.stringify(quote.totals());
                let billingData = JSON.stringify(quote.billingAddress());

                let body = JSON.stringify({
                    "customerData": customer,
                    "billingData": billingData,
                    "totals": totals
                })
                fetch('/rest/V1/parcelado/payment/start', {
                    body: body,
                    headers: { "Content-Type": "application/json" },
                    method: "POST"
                }).then(function (response) {
                    response.json().then(function (responseData) {

                        let parceladoData = JSON.parse(responseData)
                        window.location.href = parceladoData.url;

                    })
                }).catch(function (error) {
                    console.log('error: ' + error)
                })
                return false;
            },

            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {
                return 'Você será redirecionado para o ambiente seguro da ParceladoUSA';
                // return window.checkoutConfig.payment.instructions[this.item.method];
            },

            /**
             * Logo
             * @returns {string|*}
             */
            getLogo: function () {
                return 'https://parceladousa.com/ancoragem/theme/source/img/logo/logo-horizontal-blue-parcelado.png?v=' + (new Date().getTime());
            }
        });
    });
