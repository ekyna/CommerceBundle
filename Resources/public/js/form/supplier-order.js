define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderItemWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this);

            function changeHandler() {
                var productValue = String($this.find('.order-item-product').val()),
                    readyOnly = 0 < productValue.length;

                $this
                    .find('.order-item-designation, .order-item-reference')
                    .prop('readonly', readyOnly);
            }

            $this.find('input').on('change', changeHandler);

            changeHandler();
        });

        return this;
    };

    /**
     * Supplier order compose widget
     */
    $.fn.supplierOrderComposeWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $collection = $this.find('.order-compose-items').eq(0),
                $selector = $this.find('.order-compose-quick-add-select').eq(0),
                $button = $this.find('.order-compose-quick-add-button').eq(0),
                keys = ['designation', 'reference', 'net-price'];

            // Init order item widgets
            $collection
                .find('.commerce-supplier-order-item')
                .supplierOrderItemWidget();

            $button.on('click', function() {

                var selectorVal = $selector.val(),
                    $productChoice = $selector.find('option[value=' + selectorVal + ']');

                $collection.find('[data-collection-role="add"]').trigger('click');

                var $form = $collection.find('.ekyna-collection-child:last-child');

                for (var i = 0; i < keys.length; i++) {
                    $form.find('.order-item-' + keys[i]).val($productChoice.data(keys[i]));
                }

                $form.supplierOrderItemWidget();

                $form.find('.order-item-product').val(selectorVal).trigger('change');

            });
        });

        return this;
    };

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this);

            // Init order compose widget
            $this
                .find('.commerce-supplier-order-compose')
                .supplierOrderComposeWidget();

            function stringToFloat(value) {
                return parseFloat(String(value).replace(',', '.'));
            }

            // Items change handler
            function changeHandler() {
                var total = 0;
                // console.log('changed');
                $this
                    .find('.ekyna-collection-child')
                    .each(function() {
                        total += stringToFloat($(this).find('.order-item-quantity').val())
                               * stringToFloat($(this).find('.order-item-net-price').val());
                    });

                $this.find('#supplier_order_paymentTotal').val(total);
            }

            $this.on('change blur', '.ekyna-collection-child input', changeHandler);

            changeHandler();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierOrderWidget();
        }
    };
});
