define(['jquery', 'ekyna-modal', 'ekyna-dispatcher', 'ekyna-ui', 'jquery/form', 'bootstrap'], function($, Modal, Dispatcher) {
    "use strict";

    function initView() {
        $('.sale-view [data-toggle="popover"]').popover({
            trigger: 'hover',
            placement: 'top',
            html: true
        })
    }

    function parseResponse(response, $saleView) {
        var $xml = $(response),
            $view = $xml.find('view');

        if (1 === $view.size()) {
            $saleView.replaceWith($($view.text()));

            Dispatcher.trigger('ekyna_commerce.sale_view_response', response);

            initView();

            return true;
        }

        return false;
    }

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
                    modalEvent.modal.close();
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

        var $saleView = $this.closest('.sale-view'),
            method = $this.data('sale-xhr');

        $saleView.loadingSpinner();

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

    $(document).on('click', '.sale-view [data-sale-toggle-children]', function(e) {
        e.preventDefault();

        var $link = $(e.currentTarget),
            $saleView = $link.closest('.sale-view'),
            id = $link.data('sale-toggle-children'),
            shown = !!$link.data('sale-toggle-shown');

        if (id) {
            var $children = $saleView.find('tr[data-parent="' + id + '"]');
            if (shown) {
                $children.hide();
            } else {
                $children.show();
            }
            $link.data('sale-toggle-shown', !shown);
        }

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

    initView();
});
