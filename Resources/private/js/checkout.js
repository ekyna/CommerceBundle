define(['jquery', 'ekyna-modal', 'ekyna-dispatcher','jquery/form'], function($, Modal, Dispatcher) {
    "use strict";

    var mapping = {
        'information': '.cart-checkout-information',
        'invoice-address': '.cart-checkout-invoice-address',
        'delivery-address': '.cart-checkout-delivery-address',
        'comment': '.cart-checkout-comment',
        'attachments': '.cart-checkout-attachments'
    };

    var parseResponse = function(response) {
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

        // Sale view
        var $view = $xml.find('view');
        if (1 === $view.size()) {
            $('.sale-view').replaceWith($($view.text()));
            return true;
        }

        return false;
    };

    var $checkout = $('.cart-checkout'),
        $signInOrRegister = $('.cart-sign-in-or-register'),
        refreshXHR;

    function refreshCheckout() {
        if (refreshXHR) {
            refreshXHR.abort();
        }

        $checkout.loadingSpinner();

        refreshXHR = $.ajax({
            url: $checkout.data('refresh-url'),
            dataType: 'xml',
            cache: false
        });

        refreshXHR.done(function(data) {
            parseResponse(data);

            $checkout.loadingSpinner('off');
        });

        refreshXHR.fail(function() {
            console.log('Failed to update cart checkout content.');
        });
    }

    Dispatcher.on('ekyna_user.user_status', function(e) {
        if (e.authenticated) {
            refreshCheckout();
            $signInOrRegister.slideUp();
        }
    });

    $(document).on('click', '.cart-checkout [data-cart-modal]', function(e) {
        e.preventDefault();

        var $this = $(this);

        var modal = new Modal();
        modal.load({url: $this.attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType === 'xml') {
                if (parseResponse(modalEvent.content)) {
                    modalEvent.preventDefault();
                    modal.close();
                }
            }
        });

        return false;
    });

    $(document).on('click', '.cart-checkout [data-cart-xhr]', function(e) {
        e.preventDefault();

        var $this = $(this), confirmation = $this.data('confirm');
        if (confirmation && confirmation.length && !confirm(confirmation)) {
            return false;
        }

        var xhr = $.ajax({
            url: $(this).attr('href'),
            method: 'post',
            dataType: 'xml'
        });
        xhr.done(function(response) {
            parseResponse(response);
        });

        return false;
    });

});
