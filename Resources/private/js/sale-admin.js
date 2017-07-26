define(['jquery', 'ekyna-dispatcher'], function ($, Dispatcher) {
    "use strict";

    var mapping = {
        'granTotal': '#sale_grandTotal',
        'paidTotal': '#sale_paidTotal',
        'weightTotal': '#sale_weightTotal',
        'state': '#sale_state',
        'paymentState': '#sale_paymentState',
        'shipmentState': '#sale_shipmentState'
    };

    var parseResponse = function (response) {
        var $xml = $(response);

        // Information, invoice address and delivery address
        for (var key in mapping) {
            if (mapping.hasOwnProperty(key)) {
                var $node = $xml.find(key);

                if (1 === $node.size()) {
                    $(mapping[key]).html($node.text());
                }
            }
        }
    };

    Dispatcher.on('ekyna_commerce.sale_view_response', function (response) {
        parseResponse(response);
    });
});

