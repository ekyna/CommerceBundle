define(['jquery', 'ekyna-modal', 'jquery/form'], function($, Modal) {
    "use strict";

    var parseResponse = function(response) {
        var $xml = $(response),
            mapping = {
                'information': '.cart-checkout-information',
                'invoice-address': '.cart-checkout-invoice-address',
                'delivery-address': '.cart-checkout-delivery-address'
            };

        // Information, invoice address and delivery address
        for (var key in mapping) {
            if (mapping.hasOwnProperty(key)) {
                var $node = $xml.find(key);

                if (1 == $node.length) {
                    $(mapping[key]).html($($node.text()));
                }
            }
        }

        // Sale view
        var $view = $xml.find('view');
        if (1 == $view.length) {
            $('.sale-view').replaceWith($($view.text()));
            return true;
        }

        return false;
    };

    $(document).on('click', '.cart-checkout [data-cart-modal]', function(e) {
        e.preventDefault();

        var $this = $(this);

        var modal = new Modal();
        modal.load({url: $this.attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType == 'xml') {
                if (parseResponse(modalEvent.content)) {
                    modalEvent.preventDefault();
                    modal.close();
                }
            }
        });

        return false;
    });

});
