define(['jquery', 'routing'], function($, router) {
    "use strict";

    /**
     * Entity search widget
     */
    $.fn.entitySearchWidget = function(config) {

        config = $.extend({
            limit: 8
        }, config);

        function formatCustomer(customer) {
            var output = '';

            if (customer.company && 0 < customer.company.length) {
                output = '<strong>' + customer.company +'</strong> ';
            }

            output = output + customer.first_name + ' ' + customer.last_name + ' &lt;' + customer.email + '&gt;';

            return $(output);
        }

        this.each(function() {

            var $this = $(this);

            var searchUrl = router.generate($this.data('search'));
            var findUrl = router.generate($this.data('find'));
            var allowClear = $this.data('clear') == 1;

            $this.select2({
                placeholder: 'Rechercher ...',
                allowClear: allowClear,
                minimumInputLength: 3,
                ajax: {
                    delay: 300,
                    url: searchUrl,
                    dataType: 'json',
                    data: function (params) {
                        return {
                            search: params.term, // search term
                            page: params.page,
                            limit: config.limit
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * config.limit) < data.total_count
                            }
                        };
                    },
                    escapeMarkup: function (markup) { return markup; }
                }
            });
        });
        return this;
    };

    return {
        init: function($element) {
            $element.entitySearchWidget();
        }
    };
});
