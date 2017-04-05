define(['jquery', 'ekyna-modal', 'ekyna-dispatcher', 'ekyna-ui', 'jquery/form'], function($, Modal, Dispatcher) {
    "use strict";

    var parseResponse = function(response, $saleView) {
        var $xml = $(response),
            $view = $xml.find('view');

        if (1 === $view.size()) {
            $saleView.replaceWith($($view.text()));

            Dispatcher.trigger('ekyna_commerce.sale_view_response');

            return true;
        }

        return false;
    };

    $(document).on('click', '.sale-view [data-sale-modal]', function(e) {
        e.preventDefault();

        var $this = $(this),
            $saleView = $this.closest('.sale-view');

        var modal = new Modal();
        modal.load({url: $this.attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType === 'xml') {
                if (parseResponse(modalEvent.content, $saleView)) {

                    modalEvent.preventDefault();
                    modal.close();
                }
            }
        });

        return false;
    });

    $(document).on('click', '.sale-view [data-sale-xhr]', function(e) {
        e.preventDefault();

        var $this = $(this), confirmation = $this.data('confirm');
        if (confirmation && confirmation.length && !confirm(confirmation)) {
            return false;
        }

        var $saleView = $this.closest('.sale-view').loadingSpinner(),
            method = $this.data('sale-xhr');

        var xhr = $.ajax({
            url: $(this).attr('href'),
            method: method || 'post',
            dataType: 'xml'
        });
        xhr.done(function(response) {
            parseResponse(response, $saleView);
        });

        return false;
    });

    $(document).on('submit', '.sale-view', function(e) {
        e.preventDefault();

        var $saleView = $(e.target).closest('.sale-view').loadingSpinner();

        $saleView.ajaxSubmit({
            dataType: 'xml',
            success: function(response) {
                parseResponse(response, $saleView);
            }
        });

        return false;
    });

});
