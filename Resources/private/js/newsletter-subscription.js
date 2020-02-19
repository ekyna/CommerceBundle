define(['jquery', 'routing', 'ekyna-ui'], function ($, Router) {

    function Newsletter($element) {
        if ($element.data('NewsletterSubscription')) {
            return;
        }

        $element.data('NewsletterSubscription', this);

        this.$element = $element;
        this.$email = $element.find('input[type=email]');

        this.customer = this.$element.find('input[type=hidden]').val();

        this.$element.on('change', 'input[type=checkbox]', $.proxy(this.onAudienceClick, this));
    }

    Newsletter.prototype.onAudienceClick = function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $audience = $(e.target).eq(0);

        if (!$audience.prop('checked')) {
            // Non customers cannot unsubscribe
            if (!this.customer) {
                return false;
            }

            this.unsubscribe($audience, {customer: this.customer})
        } else {
            var data;
            if (this.customer) {
                data = {customer: this.customer};
            } else {
                data = {email: this.$email.val()};
            }

            this.subscribe($audience, data)
        }

        return false;
    };

    Newsletter.prototype.subscribe = function ($audience, data) {
        this.$element.loadingSpinner('on');

        var self = this,
            xhr = $.ajax({
                url: Router.generate('ekyna_commerce_api_newsletter_subscribe', {key: $audience.attr('value')}),
                method: 'POST',
                data: data,
                dataType: 'json'
            });

        xhr.done(function (response) {
            $audience.prop('checked', response.success);
            if (response.hasOwnProperty('errors')) {
                // TODO Display errors
            }
        });

        xhr.always(function () {
            self.$element.loadingSpinner('off');
        });
    };

    Newsletter.prototype.unsubscribe = function ($audience, data) {
        this.$element.loadingSpinner('on');

        var self = this,
            xhr = $.ajax({
                url: Router.generate('ekyna_commerce_api_newsletter_unsubscribe', {key: $audience.attr('value')}),
                method: 'POST',
                data: data,
                dataType: 'json'
            });

        xhr.done(function (response) {
            $audience.prop('checked', !response.success);
            if (response.hasOwnProperty('errors')) {
                // TODO Display errors
            }
        });

        xhr.always(function () {
            self.$element.loadingSpinner('off');
        });
    };

    return {
        init: function (selector) {
            $(selector).each(function () {
                new Newsletter($(this));
            });
        }
    }
});
