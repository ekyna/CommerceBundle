define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Supplier order item widget
     */
    $.fn.supplierOrderItemWidget = function() {
        this.each(function() {
            var $this = $(this),
                //$product = $this.find('input.order-item-product'),
                $priceAndWeight = $this.find('input.order-item-net-price, input.order-item-weight');

            /*$product.on('change', function() {
                var productValue = String($product.val()),
                    readyOnly = 0 < productValue.length;

                $this
                    .find('.order-item-designation, .order-item-reference')
                    .prop('readonly', readyOnly);
            }).trigger('change');*/

            $priceAndWeight.on('change keyup', function() {
                var $group = $(this).parent(),
                    value = parseFloat(String($(this).val()).replace(',', '.')) || 0;
                if (0 >= value) {
                    $group.addClass('has-warning');
                } else {
                    $group.removeClass('has-warning');
                }
            }).trigger('change');
        });

        return this;
    };

    /**
     * Supplier order compose widget
     */
    $.fn.supplierOrderComposeWidget = function() {
        this.each(function() {

            var $this = $(this),
                $collection = $this.find('.order-compose-items').eq(0),
                $selector = $this.find('.order-compose-quick-add-select').eq(0),
                $button = $this.find('.order-compose-quick-add-button').eq(0),
                keys = ['designation', 'reference', 'net-price', 'weight', 'packing', 'tax-group'];

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

                $form.find('.order-item-product').val(selectorVal);

                // Init order item widget
                $form.supplierOrderItemWidget();
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierOrderComposeWidget();
        }
    };
});
