define(['jquery', 'routing', 'ekyna-commerce/templates'], function ($, Router, Templates) {
    "use strict";

    // Abort if mobile device
    if('ontouchstart' in window) {
        return;
    }

    var $table = $('.ekyna-table > form > div > table');

    function loadSummary($tr) {
        if ($tr.data('summary-xhr')) {
            return;
        }

        var xhr = $.ajax({
            url: Router.generate('ekyna_commerce_order_admin_summary', {'orderId': $tr.data('id')}),
            dataType: 'json'
        });

        xhr.done(function (data) {
            $tr.data('summary', data);

            $tr.popover({
                content: Templates['sale_summary.html.twig'].render($tr.data('summary')),
                template: '<div class="popover summary" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                container: 'body',
                html: true,
                placement: 'auto',
                trigger: 'hover',
                viewport: '.ekyna-table'
            });

            if ($tr.is(':hover')) {
                $tr.popover('show');
            }
        });

        xhr.always(function (e) {
            $tr.removeData('summary-xhr')
        });

        $tr.data('summary-xhr', xhr);
    }

    $table.on('mouseenter', 'tbody > tr', function (e) {
        var $tr = $(e.currentTarget);

        if ($tr.data('summary')) {
            return;
        }

        loadSummary($tr);
    });
});

