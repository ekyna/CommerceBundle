define(['jquery', 'bootstrap'], function($) {
    "use strict";

    /**
     * Vat number widget
     */
    $.fn.vatNumberWidget = function() {
        this.each(function() {
            var $this = $(this),
                $button = $this.find('button[type="button"]');

            if (1 !== $button.length) {
                return;
            }

            var config = $this.data('config'),
                $icon = $button.find('.fa'),
                $input = $this.find('input[type="text"]'),
                $checkbox = $(config.checkbox),
                xhr = null,
                lastNumber = config.lastNumber,
                lastResult = config.lastResult;

            if (lastResult) {
                resultHandler(lastResult);
            }

            $input.on('keyup', function() {
                if (lastResult && $input.val() === lastNumber) {
                    if (lastResult.valid) {
                        $button.removeClass('btn-default btn-danger').addClass('btn-success');
                    } else {
                        $button.removeClass(' btn-default btn-success').addClass('btn-danger');
                    }
                } else {
                    $button.removeClass('btn-success btn-danger').addClass('btn-default');
                }
            });

            $button.on('click', function() {
                if (xhr) {
                    xhr.abort();
                }

                if ($input.val() === lastNumber) {
                    return;
                }

                lastNumber = $input.val();
                lastResult = null;

                try {
                    $button.popover('destroy');
                } catch(e) {

                }
                $icon.removeClass('fa-check').addClass('fa-spinner fa-pulse');

                if (0 === lastNumber.length) {
                    return;
                }

                xhr = $.ajax({
                    url: config.path,
                    data: {'number': $input.val()},
                    method: 'GET',
                    dataType: 'json'
                });
                xhr.done(function (result) {
                    resultHandler(result);
                });
                xhr.always(function() {
                    $icon.removeClass('fa-spinner fa-pulse').addClass('fa-check');
                });
            });

            function resultHandler(result) {
                lastResult = result;

                if (1 === $checkbox.length) {
                    $checkbox.prop('checked', result.valid);
                }

                $button.removeClass('btn-default');
                if (result.valid) {
                    $button
                        .addClass('btn-success')
                        .popover({
                            container: 'body',
                            content: result.content,
                            html: true,
                            placement: 'top'
                        });
                } else {
                    $button.addClass('btn-danger');
                }
            }
        });

        return this;
    };

    return {
        init: function($element) {
            $element.vatNumberWidget();
        }
    };
});
