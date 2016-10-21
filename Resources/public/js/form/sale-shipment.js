define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Sale shipment widget
     */
    $.fn.saleShipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $methodSelect = $this.find('select.sale-shipment-method'),
                $amountInput = $this.find('input.sale-shipment-amount');

            function applyAmountFromSelectedMethod() {
                var $methodOption = $methodSelect.find('option[value="' + $methodSelect.val() + '"]');
                $amountInput.val($methodOption.data('price'));
            }

            $methodSelect.on('change', applyAmountFromSelectedMethod);

            $this
                .on('click', '.sale-shipment-amount-apply', applyAmountFromSelectedMethod)
                .on('click', '.sale-shipment-amount-clear', function() {
                    $amountInput.val('');
                });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleShipmentWidget();
        }
    };
});
