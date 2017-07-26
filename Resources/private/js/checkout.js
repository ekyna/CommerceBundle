define(['jquery', 'ekyna-modal', 'ekyna-dispatcher','jquery/form'], function($, Modal, Dispatcher) {
    "use strict";

    var mapping = {
        'information': '.cart-checkout-information',
        'invoice-address': '.cart-checkout-invoice-address',
        'delivery-address': '.cart-checkout-delivery-address',
        'comment': '.cart-checkout-comment',
        'attachments': '.cart-checkout-attachments'
    };

    var $checkout = $('.cart-checkout'),
        $customer = $checkout.find('.cart-checkout-customer'),
        $forms = $checkout.find('.cart-checkout-forms'),
        $submit = $checkout.find('.cart-checkout-submit'),
        refreshXHR;

    var updateElementsDisplay = function(response) {
        // Sale view
        var $view = $(response).find('view');
        if (1 === $view.size()) {
            if (1 === parseInt($view.attr('empty'))) {
                $forms.slideUp();
                $customer.slideUp();
                $submit.slideUp();
            } else {
                $forms.show().slideDown();

                if (1 === parseInt($view.attr('customer'))) {
                    $customer.slideUp();
                } else {
                    $customer.show().slideDown();
                }

                if (1 === parseInt($view.attr('valid'))) {
                    $submit.show().slideDown();
                } else {
                    $submit.slideUp();
                }
            }
        }
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

        updateElementsDisplay(response);

        // Sale view
        var $view = $xml.find('view');
        if (1 === $view.size()) {
            $('.sale-view').replaceWith($($view.text()));

            return true;
        }

        return false;
    };

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

    Dispatcher.on('ekyna_commerce.sale_view_response', function(response) {
        updateElementsDisplay(response);
    });

    Dispatcher.on('ekyna_user.user_status', function(e) {
        if (e.authenticated) {
            refreshCheckout();
            $customer.slideUp();
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
