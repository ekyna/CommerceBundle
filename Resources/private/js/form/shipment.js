define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Shipment widget
     */
    $.fn.shipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $form = $(this),
                name = $form.attr('name'),
                $items = $form.find('.shipment-items'),
                $state = $form.find('[name="' + name + '[state]"]'),
                $shippedAt = $form.find('[name="' + name + '[shippedAt]"]');

            console.log('shipmentWidget', $items.length, $state.length, $shippedAt.length);

            $state.on('change', function() {
                $shippedAt.prop('disabled', !($state.val() === 'shipped' || $state.val() === 'completed'));
            }).trigger('change');

            // TODO Packaging format
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
                })
                .find('input').not(':disabled').trigger('change');
        });

        return this;
    };

    return {
        init: function($element) {
            $element.shipmentWidget();
        }
    };
});
