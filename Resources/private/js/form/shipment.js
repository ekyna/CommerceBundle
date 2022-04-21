define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Shipment widget
     */
    $.fn.shipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            const $form = $(this),
                name = $form.attr('name'),
                $quantities = $form.find('.shipment-items > tbody > tr input'),
                $method = $form.find('[name="' + name + '[method]"]'),
                $parcels = $form.find('#shipment-parcels'),
                $generalTab = $form.find('#toggle-general'),
                $relayTab = $form.find('#toggle-relay-point'),
                $toggle = $form.find('#toggle-quantities');

            const onMethodChange = function() {
                const $selectedMethod = $method.find('option[value="' + $method.val() + '"]');
                let supportParcel = 0, supportRelay = 0;

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

            const onParcelsChange = function() {
                $parcels.find('.parcel-index').each(function(index, element) {
                    $(element).text(index + 1);
                });
            };
            $parcels.on(
                'ekyna-collection-field-added '
                + 'ekyna-collection-field-removed '
                + 'ekyna-collection-field-moved-up '
                + 'ekyna-collection-field-moved-down',
                onParcelsChange
            );
            onParcelsChange();

            /*$state.on('change', function() {
                $shippedAt.prop('disabled', !($state.val() === 'shipped' || $state.val() === 'completed'));
            }).trigger('change');*/

            // TODO Packaging format

            $quantities
                .on('blur', function() {
                    const $input = $(this),
                        quantity = parseInt($input.val());
                    if (isNaN(quantity)) $input.val(0).trigger('change');
                })
                .on('change keyup', function() {
                    const $input = $(this),
                        $children = $form
                            .find('.shipment-items > tbody > tr input[data-parent="' + $input.attr('id') + '"]');

                    let quantity = parseInt($input.val());
                    if (isNaN(quantity)) quantity = 0;

                    if (quantity > $input.data('max')) {
                        $input.closest('tr').addClass('has-error danger');
                    } else {
                        $input.closest('tr').removeClass('has-error danger');
                    }

                    $children.each(function() {
                        const $input = $(this);
                        // TODO Only if input is disabled and quantity differs (do not trigger 'change' if not needed)
                        $input.val(quantity * $input.data('quantity')).trigger('change');
                    });

                    /* TODO // Update (public) parent quantity if needed
                    var $parent = null;
                    // TODO data-parent attribute is not set on non-disabled inputs
                    if ($input.data('parent')) {
                        $parent = $form.find('#' + $input.data('parent'));
                    }

                    if (!$parent || (0 === $parent.length)) {
                        return;
                    }

                    var parentMinQuantity = quantity / $input.data('quantity');
                    if ($parent.val() < parentMinQuantity) {
                        $parent.val(parentMinQuantity).trigger('change');
                    }*/
                });

            $toggle.on('click', function() {
                let sum = 0, total = 0;
                $quantities.each(function(index, input) {
                    const $input = $(input);
                    sum += parseFloat($input.val());
                    total += $input.data('max');
                });
                if (sum / total > 0.5) {
                    $quantities.not(':disabled').each(function(index, input) {
                        $(input).val(0);
                    }).trigger('change');
                } else {
                    $quantities.not(':disabled').each(function(index, input) {
                        const $input = $(input);
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
