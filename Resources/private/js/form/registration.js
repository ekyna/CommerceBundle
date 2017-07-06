define(['jquery', 'validator'], function($, Validator) {
    "use strict";

    /**
     * Registration widget
     */
    $.fn.registrationWidget = function() {

        var config = {
            password_min_length: 6,
            email_first: '#registration_email_first',
            email_second: '#registration_email_second',
            password_first: '#registration_plainPassword_first',
            password_second: '#registration_plainPassword_second'
        };

        this.each(function() {
            var $this = $(this),
                $emailFirst = $this.find(config.email_first),
                $emailFirstGroup = $emailFirst.closest('.form-group'),
                $emailSecond = $this.find(config.email_second),
                $emailSecondGroup = $emailSecond.closest('.form-group'),
                $passwordFirst = $this.find(config.password_first),
                $passwordFirstGroup = $passwordFirst.closest('.form-group'),
                $passwordSecond = $this.find(config.password_second),
                $passwordSecondGroup = $passwordSecond.closest('.form-group');

            function emailStates() {
                $emailFirstGroup.removeClass('has-success has-error');
                $emailSecondGroup.removeClass('has-success has-error');

                var email = $emailFirst.val();
                if (6 < email.length) {
                    if (Validator.isEmail(email)) {
                        $emailFirstGroup.addClass('has-success');

                        if (email === $emailSecond.val()) {
                            $emailSecondGroup.addClass('has-success');
                        } else {
                            $emailSecondGroup.addClass('has-error');
                        }

                        return;
                    }
                }

                $emailFirstGroup.addClass('has-error');
            }

            function passwordStates() {
                $passwordFirstGroup.removeClass('has-success has-error');
                $passwordSecondGroup.removeClass('has-success has-error');

                var password = $passwordFirst.val();
                if (6 <= password.length) {
                    $passwordFirstGroup.addClass('has-success');

                    if (password === $passwordSecond.val()) {
                        $passwordSecondGroup.addClass('has-success');
                    } else {
                        $passwordSecondGroup.addClass('has-error');
                    }

                    return;
                }

                $passwordFirstGroup.addClass('has-error');
            }

            $this.on('change keyup', config.email_first + ', ' + config.email_second, emailStates);
            $this.on('change keyup', config.password_first + ', ' + config.password_second, passwordStates);

        });
        return this;
    };

    return {
        init: function($element) {
            $element.registrationWidget();
        }
    };
});
