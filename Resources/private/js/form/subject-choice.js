define(['jquery', 'select2'], function($) {
    "use strict";


    /**
     * Subject choice widget
     */
    $.fn.subjectChoiceWidget = function() {

        this.each(function() {
            let $this = $(this),
                select2initialized = false,
                $provider = $this.find('.provider'),
                $identifier = $this.find('.identifier'),
                $subject = $this.find('.subject');

            if ($provider.is(':disabled')) {
                return;
            }

            let providerChangeHandler = function() {
                $subject.prop('disabled', true).off('change');
                $identifier.val(null);

                if (select2initialized) {
                    $subject.select2('destroy');
                    select2initialized = false;
                }

                var value = $provider.val(), $option, config;
                if (!value) {
                    return;
                }

                $option = $provider.find('option[value="' + $provider.val() + '"]');
                if (1 !== $option.length) {
                    return;
                }

                config = $option.data('config');
                if (!config || !config.hasOwnProperty('search')) {
                    return;
                }

                let formatter = function(data) {
                    if(!data.id)return 'Rechercher';
                    try {
                        return $('<span>[' + data.reference.at(0) + '] ' + data.text + '</span>');
                    } catch (e) {
                        return $('<span>' + data.text + '</span>');
                    }
                };

                let $parent = $provider.closest('.modal');
                if (!$parent.length) {
                    $parent = null;
                }

                $subject
                    .prop('disabled', false)
                    .select2({
                        placeholder: 'Rechercher ...',
                        allowClear: true,
                        //selectOnClose: true, // For tests
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
                        $identifier.val($subject.val());
                    })
                    .trigger('change');

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
