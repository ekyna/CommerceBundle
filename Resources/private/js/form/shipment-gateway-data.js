define(['jquery', 'routing', 'ekyna-form', 'ekyna-spinner'], function($, Router, Form) {
    "use strict";

    /**
     * Shipment gateway data widget
     */
    $.fn.gatewayDataWidget = function() {

        this.each(function() {
            var $gatewayData = $(this),
                $method = $gatewayData.closest('form').find($gatewayData.data('method')),
                xhr = null;

            if (1 !== $method.length) {
                return;
            }

            function loadForm() {
                if (xhr) {
                    xhr.abort();
                    xhr = null;
                }

                var methodId = parseInt($method.val()) || 0;
                if (0 >= methodId) {
                    $gatewayData.empty();
                    return;
                }

                $gatewayData.loadingSpinner();

                var shipmentId = $gatewayData.data('shipment'),
                    parameters = {
                    orderId: $gatewayData.data('order'),
                    shipmentMethodId: methodId,
                    return: $gatewayData.data('return')
                };
                if (shipmentId) {
                    parameters.shipmentId = shipmentId;
                }

                xhr = $.ajax({
                    url: Router.generate('admin_ekyna_commerce_order_shipment_gateway_form', parameters),
                    dataType: 'xml'
                });

                xhr.done(function(xml) {
                    $gatewayData.loadingSpinner('off');

                    $gatewayData.empty();

                    var $form = $(xml).find('form');
                    if (1 !== $form.length) {
                        return;
                    }

                    $gatewayData.append($($form.text()).children());

                    var form = Form.create($gatewayData);
                    form.init();
                });
            }

            $method.on('change', loadForm);
        });

        return this;
    };

    return {
        init: function($element) {
            $element.gatewayDataWidget();
        }
    };
});
