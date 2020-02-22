define(['jquery', 'routing', 'ekyna-ui'], function ($, Router) {

    function Newsletter($element) {
        if ($element.data('NewsletterQuickSubscription')) {
            return;
        }

        $element.data('NewsletterQuickSubscription', this);

        this.$element = $element;

        this.$email = this.$element.find('input[type=email]');
        this.key = this.$element.find('input[type=hidden]').val();

        this.$element.on('click', 'button', $.proxy(this.subscribe, this));
    }

    Newsletter.prototype.subscribe = function (e) {
        e.preventDefault();
        e.stopPropagation();

        var email = String(this.$email.val());
        if (!email.length) {
            this.$email.closest('div').addClass('has-error');
            return;
        }

        this.$email.closest('div').removeClass('has-error');

        this.$element.loadingSpinner('on');

        var self = this,
            xhr = $.ajax({
                url: Router.generate('ekyna_commerce_api_newsletter_subscribe', {key: this.key}),
                method: 'POST',
                data: { email: email },
                dataType: 'json'
            });

        xhr.done(function (response) {
            if (response.success) {
                self.$email.val('');
                self.$element.find('.success').show();
                self.$element.find('.row').hide();
            } else if (response.hasOwnProperty('errors')) {
                // TODO Display errors
            }
        });

        xhr.always(function () {
            self.$element.loadingSpinner('off');
        });

        return false;
    };

    return {
        init: function (selector) {
            $(selector).each(function () {
                new Newsletter($(this));
            });
        }
    }
});
