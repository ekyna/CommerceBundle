define(['jquery', 'validator'], function($, Validator) {
    "use strict";

    function toggleRequired($field, required) {
        $field.prop('required', required);

        var $label = $('label[for="' + $field.attr('id') + '"]');
        if (1 === $label.length) {
            if (required) {
                $label.addClass('required');
            } else {
                $label.removeClass('required');
            }
        }
    }

    /**
     * Registration widget
     */
    $.fn.registrationWidget = function() {

        var config = {
            password_min_length: 6,
            email_first: '#registration_customer_email_first',
            email_second: '#registration_customer_email_second',
            password_first: '#registration_customer_plainPassword_first',
            password_second: '#registration_customer_plainPassword_second',
            company: '#registration_customer_company',
            vat_number: '#registration_customer_vatNumber',
            apply_group: '#registration_applyGroup > input',
            business: '#registration_business',
            regular: '#registration_regular',
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
                $passwordSecondGroup = $passwordSecond.closest('.form-group'),
                $company = $this.find(config.company),
                $vatNumber = $this.find(config.vat_number),
                $applyGroup = $this.find(config.apply_group),
                $regular = $this.find(config.regular),
                $business = $this.find(config.business);

            /* -------------------------------- User -------------------------------- */

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

            if (1 === $emailFirst.length) {
                $this.on('change keyup', config.email_first + ', ' + config.email_second, emailStates);
            }
            if (1 === $passwordFirst.length) {
                $this.on('change keyup', config.password_first + ', ' + config.password_second, passwordStates);
            }

            /* -------------------------------- Business -------------------------------- */

            function updateBusinessFields() {
                console.log($applyGroup.val());
                var $groupOption = $applyGroup.filter(':checked');
                if (1 === $groupOption.length) {
                    if (1 === parseInt($groupOption.data('business'))) {
                        toggleRequired($company, true);
                        $regular.slideUp(function() {
                            $regular.find('select,input').each(function() {
                                $(this).val(null);
                            });
                        });
                        $business.slideDown();

                        return;
                    }
                }

                $regular.slideDown();
                $business.slideUp(function() {
                    $business.find('select,input').each(function() {
                        $(this).val(null);
                    });
                });
                toggleRequired($company, false);
            }

            $this.on('change', config.apply_group, updateBusinessFields);

            updateBusinessFields();
        });
        return this;
    };

    return {
        init: function($element) {
            $element.registrationWidget();
        }
    };
});

