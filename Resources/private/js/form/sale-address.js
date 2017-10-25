define(['jquery', 'routing'], function($, router) {
    "use strict";

    /**
     * Sale address widget
     */
    $.fn.saleAddressWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                mode = $this.data('mode'),
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
                    complement: '.address-complement',
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
                },
                setAddress = function(data) {
                    clearForm();

                    for (var key in mapping) {
                        if (mapping.hasOwnProperty(key)) {
                            $addressForm.find(mapping[key]).val(data[key]).trigger("change");
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
                                        var addressData = data.choices[i];
                                        $choiceSelect.append(
                                            $('<option />')
                                                .attr('value', addressData.id)
                                                .text(addressData.text)
                                                .data('address', addressData)
                                        );
                                        if (mode === 'invoice' && addressData['invoice_default'] === 1) {
                                            setAddress(addressData);
                                        } else if (mode === 'delivery' && addressData['delivery_default'] === 1) {
                                            if (addressData['invoice_default'] === 1) {
                                                if (1 === $sameCheckbox.size()) {
                                                    $sameCheckbox
                                                        .prop('checked', true)
                                                        .trigger('change');
                                                }
                                                clearForm();
                                            } else {
                                                if (1 === $sameCheckbox.size()) {
                                                    $sameCheckbox
                                                        .prop('checked', false)
                                                        .trigger('change');
                                                }
                                                setAddress(addressData);
                                            }
                                        }
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
                if (0 === $option.size()) {
                    return;
                }

                var data = $option.data('address');
                if (data && data.hasOwnProperty('id')) {
                    setAddress(data);
                }
            });

            if (1 === $sameCheckbox.size()) {
                var $wrapper = $this.find('.sale-address-wrap'),
                    toggleAddress = function () {
                        if ($sameCheckbox.prop('checked')) {
                            $wrapper.slideUp(function() {
                                if ($choiceSelect.size()) {
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
