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

                    if (1 === parseInt($view.attr('user'))) {
                        $customer.find('.no-user-case').hide();
                        $customer.find('.user-case').show();
                    } else {
                        $customer.find('.no-user-case').show();
                        $customer.find('.user-case').hide();
                    }
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
        //if (e.authenticated) {
            refreshCheckout();
        //}
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


    // Refreshes the checkout on visibility change.

    var hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }

    function handleVisibilityChange() {
        if (document[hidden]) {

        } else {
            refreshCheckout();
        }
    }

    if (typeof document.addEventListener === "undefined" || typeof document[hidden] === "undefined") {
        //console.log("This demo requires a browser, such as Google Chrome or Firefox, that supports the Page Visibility API.");
    } else {
        // Handle page visibility change
        document.addEventListener(visibilityChange, handleVisibilityChange, false);
    }

});
