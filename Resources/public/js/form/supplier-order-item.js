define(['jquery'], function($) {
    "use strict";

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderItemWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this);

            function inputChangeHandler() {
                var productValue = String($this.find('.order-item-product').val()),
                    readyOnly = 0 < productValue.length;

                $this
                    .find('.order-item-designation, .order-item-reference')
                    .prop('readonly', readyOnly);
            }

            $this.find('input').on('change', inputChangeHandler);

            inputChangeHandler();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierOrderItemWidget();
        }
    };
});
