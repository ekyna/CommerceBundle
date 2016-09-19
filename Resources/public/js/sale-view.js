define(['jquery', 'ekyna-modal', 'jquery/form'], function($, Modal) {
    "use strict";

    var parseResponse = function(response, $saleView) {
        var $xml = $(response),
            $view = $xml.find('view');

        if (1 == $view.length) {
            $saleView.replaceWith($($view.text()));
            return true;
        }

        return false;
    };

    $(document).on('click', '.sale-detail-action[data-sale-modal]', function(e) {
        e.preventDefault();

        var $saleView = $(e.target).closest('.sale-view');

        var modal = new Modal();
        modal.load({url: $(this).attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType == 'xml') {
                if (parseResponse(modalEvent.content, $saleView)) {
                    modalEvent.preventDefault();
                    modal.close();
                }
            }
        });

        return false;
    });

    $(document).on('click', '.sale-detail-action[data-sale-xhr]', function(e) {
        e.preventDefault();

        var $saleView = $(e.target).closest('.sale-view').addClass('loading');

        var xhr = $.ajax({
            url: $(this).attr('href'),
            method: 'post',
            dataType: 'xml'
        });
        xhr.done(function(response) {
            parseResponse(response, $saleView);
        });

        return false;
    });

    $(document).on('submit', '.sale-view', function(e) {
        e.preventDefault();

        var $saleView = $(e.target).closest('.sale-view').addClass('loading');

        $saleView.ajaxSubmit({
                dataType: 'xml',
                success: function(response) {
                    parseResponse(response, $saleView);
                }
            });

        return false;
    });

});
