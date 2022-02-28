define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Shipment pricing widget
     */
    $.fn.saleShipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $collection = $this.find('.commerce-shipment-pricing-prices'),
                $filter = $this.find('.commerce-shipment-pricing-filter'),
                $addButton = $collection.find('[data-collection-role="add"]'),
                filterBy = $filter.data('filter-by'),
                filterValue;


            // Prices collection children visibility
            function togglePricesVisibility() {
                var $prices = $collection.find('tbody > tr').hide();

                filterValue = parseInt($filter.val());
                if (!filterValue) {
                    $addButton.hide();
                    return;
                }

                $addButton.show();

                $prices.filter(function() {
                    return $(this).data(filterBy) === filterValue;
                }).show();
            }

            $filter.on('change', togglePricesVisibility);

            togglePricesVisibility();

            // New price form event
            $collection.on('ekyna-collection-field-added', function(e) {
                var $priceForm = $(e.target);

                // Sets the method (currently selected)
                $priceForm
                    .data(filterBy, filterValue)
                    .find('.shipment-price-' + filterBy)
                    .val(filterValue);
            });

            // TODO ...
            /*$collection.on('invalid', 'input', function(e) {
                console.log(e);
                var $child = $(e.target).eq(0).closest('tr');
                if ($child.length) {
                    $filter.val($child.data('method'));
                    togglePricesVisibility();
                    return;
                }
                e.preventDefault();
            });*/
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleShipmentWidget();
        }
    };
});
