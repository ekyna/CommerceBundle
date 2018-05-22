define(['jquery', 'ekyna-modal', 'ekyna-dispatcher', 'ekyna-ui', 'jquery/form'], function($, Modal, Dispatcher) {
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
        $quote = $checkout.find('.cart-checkout-quote'),
        $submitPrevented = $checkout.find('.submit-prevented'),
        preventRefresh = false;

    var updateElementsDisplay = function(response) {
        $submitPrevented.clearQueue().slideUp(function() {
            $submitPrevented.find('.alert-danger').hide().find('p').empty();
            $submitPrevented.find('.alert-warning').show();
        });

        // Sale view
        var $view = $(response).find('view');
        if (1 === $view.length) {
            if (1 === parseInt($view.attr('empty'))) {
                $forms.slideUp();
                $customer.slideUp();
                $submit.addClass('disabled').hide();
                $quote.addClass('disabled').hide();
            } else {
                $forms.show().slideDown();
                $submit.show();

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

                if (1 === parseInt($view.attr('quote'))) {
                    $quote.show();
                } else {
                    $quote.hide();
                }

                if (1 === parseInt($view.attr('valid'))) {
                    $submit.removeClass('disabled');
                    $quote.removeClass('disabled');
                } else {
                    $submit.addClass('disabled');
                    $quote.addClass('disabled');

                    /*var $errors = $(response).find('errors');
                    if ($errors.length === 1) {
                        $submitPrevented.find('.alert-danger').show().find('p').html($errors.text());
                        $submitPrevented.find('.alert-warning').hide();
                    }*/
                }
            }
        }
    };


    function parseResponse(response) {
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
    }

    function refreshCheckout() {
        if (preventRefresh) {
            return false;
        }

        preventRefresh = true;

        $checkout.loadingSpinner();

        var refreshXHR = $.ajax({
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

        refreshXHR.always(function() {
            preventRefresh = false;
        });

        return true;
    }

    Dispatcher.on('ekyna_commerce.sale_view_response', function(response) {
        updateElementsDisplay(response);
    });

    Dispatcher.on('ekyna_user.authentication', function() {
        refreshCheckout();
    });

    $checkout.on('click', '.cart-checkout-footer a.btn', function(e) {
        e.stopPropagation();

        if ($(e.target).closest('a.btn').hasClass('disabled')) {
            e.preventDefault();

            $submitPrevented.clearQueue().slideDown();

            return false;
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
                    modalEvent.modal.close();
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
    if (!$('html').data('debug')) {
        var preventWindowFocusRefresh = false;
        $(window).on('focus', function () {
            if (!preventWindowFocusRefresh && refreshCheckout()) {
                preventWindowFocusRefresh = true;

                setTimeout(function () {
                    preventWindowFocusRefresh = false;
                }, 10000);
            }
        });
    }
});
