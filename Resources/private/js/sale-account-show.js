define(['jquery', 'ekyna-modal'], function ($, Modal) {
    "use strict";

    var frameMapping = {
        //'information': '#account-sale-information',
        'header': '#account-sale-header',
        'invoiceAddress': '#account-sale-invoice',
        'deliveryAddress': '#account-sale-delivery'
        //'comment': '#account-sale-comment',
        //'attachments': '#account-sale-attachments',
        //'content[type="cart"]': '#account-sale-content'
    };

    function parseResponse (response) {
        var $xml = $(response);

        // Information, invoice address and delivery address
        for (var key in frameMapping) {
            if (frameMapping.hasOwnProperty(key)) {
                var $node = $xml.find(key);

                if (1 === $node.size()) {
                    $(frameMapping[key]).html($node.text());
                }
            }
        }

        //updateElementsDisplay(response);

        // Sale view
        var $view = $xml.find('view');
        if (1 === $view.size()) {
            $('.account-sale-view').html($($view.text()));

            return true;
        }

        return false;
    }

    $(document).on('click', '.account-sale [data-account-modal]', function (e) {
        e.preventDefault();

        var $this = $(this);

        var modal = new Modal();
        modal.load({url: $this.attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType === 'xml') {
                if (parseResponse(modalEvent.content)) {
                    modalEvent.preventDefault();
                    modalEvent.modal.close();
                }
            }
        });

        return false;
    });
});
