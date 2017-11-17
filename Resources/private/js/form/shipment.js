define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Shipment widget
     */
    $.fn.shipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $items = $(this).find('.shipment-items');

            console.log('shipmentWidget', $items.length);

            $items
                .on('blur', 'input', function() {
                    var $input = $(this),
                        quantity = parseInt($input.val());
                    if (isNaN(quantity)) $input.val(0).trigger('change');
                })
                .on('change keyup', 'input', function() {
                    var $input = $(this),
                        quantity = parseInt($input.val()),
                        $children = $items.find('[data-parent="' + $input.attr('id') + '"]');

                    if (isNaN(quantity)) quantity = 0;

                    if (quantity > $input.data('max')) {
                        $input.closest('tr').addClass('has-error danger');
                    } else {
                        $input.closest('tr').removeClass('has-error danger');
                    }

                    $children.each(function() {
                        var $input = $(this);
                        $input.val(quantity * $input.data('quantity')).trigger('change');
                    });
                });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.shipmentWidget();
        }
    };
});
