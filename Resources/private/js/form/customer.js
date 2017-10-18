define(['jquery'], function ($) {
    "use strict";

    /**
     * Customer widget
     */
    $.fn.customerWidget = function () {
        this.each(function () {

            var $this = $(this),
                $parent = $this.find('select[name="customer[parent]"]'),
                $childFields = $this.find(
                    'select[name="customer[customerGroup]"], ' +
                    'input[name="customer[vatNumber]"], ' +
                    'input[name="customer[vatValid]"], ' +
                    'select[name="customer[paymentTerm]"], ' +
                    'input[name="customer[outstandingLimit]"], ' +
                    '.commerce-vat-number .btn'
                );

            function onParentChange() {
                if (0 < $parent.val()) {
                    $childFields.prop('disabled', true);
                } else {
                    $childFields.prop('disabled', false);
                }
            }

            $parent.on('change', onParentChange);

            onParentChange();
        });

        return this;
    };

    return {
        init: function ($element) {
            $element.customerWidget();
        }
    };
});
