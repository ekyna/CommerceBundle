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
                $method = $form.find('[name="' + name + '[method]"]'),
                $parcels = $form.find('#shipment-parcels'),
                $generalTab = $form.find('#toggle-general'),
                $relayTab = $form.find('#toggle-relay-point');


            var onMethodChange = function() {
                var supportParcel = 0,
                    supportRelay = 0,
                    $selectedMethod = $method.find('option[value="' + $method.val() + '"]');

                if (1 === $selectedMethod.length) {
                    supportParcel = $selectedMethod.data('parcel');
                    supportRelay = $selectedMethod.data('relay');
                }

                if (supportParcel) {
                    $parcels.slideDown();
                } else {
                    $parcels.slideUp(function() {
                        // Clears parcels
                        $parcels.find('.ekyna-collection-child-container').empty();
                    });
                }

                if (supportRelay) {
                    $relayTab.show();
                } else {
                    $relayTab.hide();
                    $generalTab.trigger('click');
                }
            };

            $method.on('change', onMethodChange);
            onMethodChange();

            /*$state.on('change', function() {
                $shippedAt.prop('disabled', !($state.val() === 'shipped' || $state.val() === 'completed'));
            }).trigger('change');*/

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
