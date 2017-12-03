define(['jquery', 'ekyna-dispatcher'], function ($, Dispatcher) {
    "use strict";

    var mapping = {
        'state': '.sale-state',
        'grandTotal': '.sale-grand-total',
        'paidTotal': '.sale-paid-total',
        'outstandingAccepted': '.sale-outstanding-accepted',
        'outstandingExpired': '.sale-outstanding-expired',
        'outstandingLimit': '.sale-outstanding-limit',
        'outstandingDate': '.sale-outstanding-date',
        'paymentTerm': '.sale-payment-term',
        'paymentState': '.sale-payment-state',
        'weightTotal': '.sale-weight-total',
        'shipmentMethod': '.sale-shipment-method',
        'shipmentState': '.sale-shipment-state',
        'invoiceTotal': '.sale-invoice-total',
        'creditTotal': '.sale-credit-total',
        'invoiceState': '.sale-invoice-state'
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

