define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $collection = $this.find('.commerce-shipment-zone-prices'),
                $methodChoice = $this.find('.commerce-shipment-zone-method'),
                methodId;


            // Prices collection children visibility
            function togglePricesVisibility() {
                methodId = $methodChoice.val();
                $collection
                    .find('tbody > tr')
                    .hide()
                    .filter(function() { return $(this).data('method') == methodId; })
                    .show();
            }

            $methodChoice.on('change', togglePricesVisibility);

            togglePricesVisibility();


            // New price form event
            $collection.on('ekyna-collection-field-added', function(e) {
                var $priceForm = $(e.target);

                // Sets the method (currently selected)
                $priceForm
                    .data('method', methodId)
                    .find('.shipment-price-method')
                    .val(methodId);
            });

            // TODO ...
            $collection.on('invalid', 'input', function(e) {
                console.log(e);
                var $child = $(e.target).eq(0).closest('tr');
                if ($child.size()) {
                    $methodChoice.val($child.data('method'));
                    togglePricesVisibility();
                    return;
                }
                e.preventDefault();
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierOrderWidget();
        }
    };
});
