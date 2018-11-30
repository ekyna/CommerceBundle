define(['jquery'], function($) {
    "use strict";

    /**
     * Notify widget
     */
    $.fn.notifyWidget = function() {

        this.each(function() {
            var $this = $(this),
                $from = $this.find('[name="' + $this.attr('name') + '[from]"]');

            function onFromChange() {
                if ($from.val() === $this.data('current-user')) {
                    $from.closest('.form-group').removeClass('has-warning').addClass('has-success');
                } else {
                    $from.closest('.form-group').removeClass('has-success').addClass('has-warning');
                }
            }

            onFromChange();

            $from.on('change', onFromChange);
        });

        return this;
    };

    return {
        init: function($element) {
            $element.notifyWidget();
        }
    };
});
