define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderWidget = function() {
        this.each(function() {

            var $this = $(this),
                $carrier = $this.find('.order-carrier'),
                $carrierFields = $this.find('.forwarder-fieldset');

            $carrier.on('change', function() {
                if ($carrier.val()) {
                    $carrierFields.slideDown();
                } else {
                    $carrierFields.slideUp();
                }
            }).trigger('change');
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierOrderWidget();
        }
    };
});
