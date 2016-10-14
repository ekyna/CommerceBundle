define(['jquery', 'ekyna-form/collection'], function($) { // Nee collection to be initialized first
    "use strict";

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

            $button.on('click', function() {

                var selectorVal = $selector.val(),
                    $productChoice = $selector.find('option[value=' + selectorVal + ']');

                $collection.find('[data-collection-role="add"]').trigger('click');

                var $form = $collection.find('.ekyna-collection-child:last-child');

                for (var i = 0; i < keys.length; i++) {
                    $form.find('.order-item-' + keys[i]).val($productChoice.data(keys[i]));
                }

                $form.find('.order-item-product').val(selectorVal).trigger('change');
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
