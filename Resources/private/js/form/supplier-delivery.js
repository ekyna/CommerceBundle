define(['jquery', 'routing'], function($, Router) {
    "use strict";

    /**
     * Supplier delivery item widget
     */
    $.fn.supplierDeliveryWidget = function() {
        this.each(function() {
            $(this).on('click', 'a.print-label', function(e) {
                var $button = $(e.currentTarget),
                    orderId = parseInt($button.data('order-id')),
                    itemId = parseInt($button.data('item-id'));

                if (!(orderId && itemId)) {
                    console.log('Undefined order or item id.');
                    return false;
                }

                var url = Router.generate('ekyna_commerce_supplier_order_admin_label', {
                    'supplierOrderId': orderId,
                    'id': [itemId],
                    'geocode': $button.closest('tr').find('input.geocode').val()
                });

                var win = window.open(url, '_blank');
                win.focus();
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierDeliveryWidget();
        }
    };
});
