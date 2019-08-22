define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * invoice widget
     */
    $.fn.invoiceWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $invoice = $(this),
                $quantities = $invoice.find('.invoice-items > tbody > tr input'),
                $toggle = $invoice.find('#toggle-quantities');

            console.log('invoiceWidget', $quantities.length);

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
                        $children = $invoice
                            .find('.invoice-items > tbody > tr input[data-parent="' + $input.attr('id') + '"]');

                    if (isNaN(quantity)) quantity = 0;

                    if (quantity > $input.data('max')) {
                        $input.closest('tr').addClass('has-error danger');
                    } else {
                        $input.closest('tr').removeClass('has-error danger');
                    }

                    $children.each(function() {
                        var $child = $(this);
                        $child.val(quantity * $child.data('quantity')).trigger('change');
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
            $element.invoiceWidget();
        }
    };
});
