define(['jquery', 'routing'], function($, router) {
    "use strict";

    /**
     * Sale address widget
     */
    $.fn.saleAddressWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $customerChoice = $('#' + $this.data('customer-field')),
                $sameCheckbox = $this.find('.sale-address-same'),
                $choiceSelect = $this.find('.sale-address-choice'),
                $addressForm = $this.find('.sale-address'),
                mapping = {
                    company: '.address-company',
                    gender: '.identity-gender',
                    first_name: '.identity-first-name',
                    last_name: '.identity-last-name',
                    street: '.address-street',
                    supplement: '.address-supplement',
                    postal_code: '.address-postal-code',
                    city: '.address-city',
                    country: '.address-country',
                    //state: '.address-state',
                    phone: '.address-phone',
                    mobile: '.address-mobile'
                },
                clearForm = function() {
                    for (var key in mapping) {
                        if (mapping.hasOwnProperty(key)) {
                            $addressForm.find(mapping[key]).val(null).trigger("change");
                        }
                    }
                };

            if (1 === $customerChoice.size()) {
                $customerChoice.on('change', function() {
                    $choiceSelect
                        .empty()
                        .append(
                            $('<option value>Choose</option>')
                        )
                        .prop('disabled', true);

                    var customerId = $(this).val();
                    if (customerId) {
                        var xhr = $.get(router.generate(
                            'ekyna_commerce_customer_address_admin_choice_list',
                            {customerId: customerId}
                        ));
                        xhr.done(function (data) {
                            if (typeof data.choices !== 'undefined') {
                                for (var i in data.choices) {
                                    if (data.choices.hasOwnProperty(i)) {
                                        $choiceSelect.append(
                                            $('<option />')
                                                .attr('value', data.choices[i].id)
                                                .text(data.choices[i].text)
                                                .data('address', data.choices[i])
                                        );
                                    }
                                }
                                $choiceSelect.prop('disabled', false);
                            }
                            $choiceSelect.trigger('form_choices_loaded', data);
                        });
                    }
                });
            }

            $choiceSelect.on('change', function() {
                var val = $choiceSelect.val();
                if (!val) {
                    return;
                }

                var $option = $choiceSelect.find('option[value=' + $choiceSelect.val() + ']');
                if (!$option.length) {
                    return;
                }

                var data = $option.data('address');
                if (data && data.hasOwnProperty('id')) {
                    clearForm();

                    for (var key in mapping) {
                        if (mapping.hasOwnProperty(key)) {
                            $addressForm.find(mapping[key]).val(data[key]).trigger("change");
                        }
                    }
                }
            });

            if (1 === $sameCheckbox.length) {
                var $wrapper = $this.find('.sale-address-wrap'),
                    toggleAddress = function () {
                        if ($sameCheckbox.prop('checked')) {
                            $wrapper.slideUp(function() {
                                if ($choiceSelect.length) {
                                    $choiceSelect.val(null).trigger('change');
                                } else {
                                    clearForm();
                                }
                            });
                        } else {
                            $wrapper.slideDown();
                        }
                    };

                $sameCheckbox.on('change', toggleAddress);
                toggleAddress();
            }
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleAddressWidget();
        }
    };
});
