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
                $quantities = $form.find('.shipment-items > tbody > tr input'),
                $method = $form.find('[name="' + name + '[method]"]'),
                $parcels = $form.find('#shipment-parcels'),
                $generalTab = $form.find('#toggle-general'),
                $relayTab = $form.find('#toggle-relay-point'),
                $toggle = $form.find('#toggle-quantities');


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
            $quantities
                .on('blur', function() {
                    var $input = $(this),
                        quantity = parseInt($input.val());
                    if (isNaN(quantity)) $input.val(0).trigger('change');
                })
                .on('change keyup', function() {
                    var $input = $(this),
                        quantity = parseInt($input.val()),
                        $children = $quantities.find('[data-parent="' + $input.attr('id') + '"]');

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
                .not(':disabled').trigger('change');

            $toggle.on('click', function() {
                var sum = 0, total = 0;
                $quantities.each(function(index, input) {
                    var $input = $(input);
                    sum += parseFloat($input.val());
                    total += $input.data('max');
                });
                if (sum / total > 0.5) {
                    $quantities.not(':disabled').each(function(index, input) {
                        $(input).val(0);
                    }).trigger('change');
                } else {
                    $quantities.not(':disabled').each(function(index, input) {
                        var $input = $(input);
                        $input.val($input.data('max'));
                    }).trigger('change');
                }
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
