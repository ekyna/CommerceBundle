define(['jquery', 'routing', 'select2'], function($, router) {
    "use strict";

    /**
     * Subject choice widget
     */
    $.fn.subjectChoiceWidget = function(config) {

        config = $.extend({
            limit: 8
        }, config);

        this.each(function() {

            var $this = $(this),
                select2initialized = false,
                $provider = $this.find('.provider'),
                $identifier = $this.find('.identifier'),
                $subject = $this.find('.subject');

            if ($provider.is(':disabled')) {
                return;
            }

            var providerChangeHandler = function() {
                $subject.prop('disabled', true);
                $identifier.val(null).off('change');

                if (select2initialized) {
                    $subject.select2('destroy');
                    select2initialized = false;
                }

                var value = $provider.val(), $option, config;
                if (!value) {
                    return;
                }

                $option = $provider.find('option[value="' + $provider.val() + '"]');
                if (1 != $option.size()) {
                    return;
                }

                config = $option.data('config');
                if (!config || !config.hasOwnProperty('search')) {
                    return;
                }

                var formatter = function(data) {
                    if(!data.id)return 'Rechercher'; return $('<span>'+data.choice_label+'</span>');
                };

                var $parent = $provider.closest('.modal');
                if (!$parent.size()) {
                    $parent = null;
                }

                $subject
                    .prop('disabled', false)
                    .select2({
                        placeholder: 'Rechercher ...',
                        allowClear: true,
                        minimumInputLength: 3,
                        templateResult: formatter,
                        templateSelection: formatter,
                        dropdownParent: $parent,
                        ajax: {
                            delay: 300,
                            url: config.search,
                            dataType: 'json',
                            data: function (params) {
                                return {
                                    query: params.term,
                                    page:  params.page,
                                    limit: 10
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.results,
                                    pagination: {
                                        more: (params.page * config.limit) < data.total_count
                                    }
                                };
                            },
                            escapeMarkup: function (markup) {
                                return markup;
                            }
                        }
                    })
                    .on('change', function() {
                        $identifier.val($(this).val());
                    });

                select2initialized = true;
            };

            $provider.on('change', providerChangeHandler);

            providerChangeHandler();
        });
        return this;
    };

    return {
        init: function($element) {
            $element.subjectChoiceWidget();
        }
    };
});
