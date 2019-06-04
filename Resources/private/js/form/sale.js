define(['jquery', 'routing', 'ekyna-commerce/form/address'], function($, router) {
    "use strict";

    /**
     * Sale widget
     */
    $.fn.saleWidget = function() {

        this.each(function() {

            var $this = $(this),
                name = $this.prop('name'),
                $customerChoice = $('select[name="' + name + '[customer]"]'),
                $currencyChoice = $('select[name="' + name + '[currency]"]'),
                $localeChoice = $('select[name="' + name + '[locale]"]');

            $customerChoice.on('change', function() {
                if (!$customerChoice.val()) {
                    return;
                }

                var selection = $customerChoice.select2('data');

                if (selection.length) {
                    var customer = selection[0];

                    if (!customer.hasOwnProperty('currency')) {
                        if (!customer.hasOwnProperty('element')) {
                            return;
                        }

                        customer = $(customer.element).data('entity');
                    }

                    if (!customer.hasOwnProperty('currency')) {
                        return;
                    }

                    $currencyChoice.val(customer.currency).trigger('change');
                    $localeChoice.val(customer.locale).trigger('change');
                }
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleWidget();
        }
    };
});
