define(['jquery', 'routing', 'ekyna-spinner'], function($, Router) {
    "use strict";

    /**
     * Sale item subject widget
     */
    $.fn.saleItemSubjectWidget = function() {

        this.each(function() {

            var $this = $(this),
                $provider = $this.find('.commerce-subject-choice select.provider'),
                $subject = $this.find('.commerce-subject-choice select.subject'),
                $stock = $this.find('.sale-item-subject-stock'),
                stockXhr;

            function updateStockView() {
                if (stockXhr) {
                    stockXhr.abort();
                }

                $stock.empty();

                var provider = String($provider.val()),
                    identifier = parseInt($subject.val());

                if (0 < provider.length && 0 < identifier) {
                    $stock.loadingSpinner('on');

                    stockXhr = $.ajax({
                        url: Router.generate('admin_ekyna_commerce_inventory_subject_stock', {
                            provider: provider,
                            identifier: identifier
                        }),
                        method: 'GET',
                        dataType: 'html'
                    });

                    stockXhr.done(function(html) {
                        if (0 < html.length) {
                            $stock.html(html);
                        }
                    });
                    stockXhr.always(function() {
                        $stock.loadingSpinner('off');
                    });
                }
            }

            $provider.on('change', updateStockView);
            $subject.on('change', updateStockView);

            updateStockView();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleItemSubjectWidget();
        }
    };
});
