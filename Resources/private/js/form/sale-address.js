define(['jquery', 'routing', 'ekyna-commerce/form/address'], function($, router) {
    "use strict";

    /**
     * Sale address widget
     */
    $.fn.saleAddressWidget = function() {

        this.each(function() {

            var $this = $(this),
                mode = $this.data('mode'),
                $customerChoice = $('#' + $this.data('customer-field')),
                $sameCheckbox = $this.find('.sale-address-same'),
                $choiceSelect = $this.find('.sale-address-choice'),
                $addressForm = $this.find('.sale-address').address();

            if (1 === $customerChoice.length) {
                $customerChoice.on('change', function() {
                    $choiceSelect
                        .empty()
                        .append($('<option value>Choose</option>'))
                        .prop('disabled', true);

                    var customerId = $(this).val();
                    if (customerId) {
                        var xhr = $.get(router.generate(
                            'admin_ekyna_commerce_customer_address_choice_list',
                            {customerId: customerId}
                        ));
                        xhr.done(function (data) {
                            var isEmpty = $addressForm.address('isEmpty');
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
                                        // Skip if address is not empty
                                        if (!isEmpty) {
                                            continue;
                                        }
                                        // Set default address
                                        if (mode === 'invoice' && addressData['invoice_default'] === 1) {
                                            $addressForm.address('set', addressData);
                                        } else if (mode === 'delivery' && addressData['delivery_default'] === 1) {
                                            if (addressData['invoice_default'] === 1) {
                                                if (1 === $sameCheckbox.length) {
                                                    $sameCheckbox
                                                        .prop('checked', true)
                                                        .trigger('change');
                                                }
                                                $addressForm.address('clear');
                                            } else {
                                                if (1 === $sameCheckbox.length) {
                                                    $sameCheckbox
                                                        .prop('checked', false)
                                                        .trigger('change');
                                                }
                                                $addressForm.address('set', addressData);
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
                if (0 === $option.length) {
                    return;
                }

                var data = $option.data('address');
                if (data && data.hasOwnProperty('id')) {
                    $addressForm.address('set', data);
                }
            });

            if (1 === $sameCheckbox.length) {
                var $wrapper = $this.find('.sale-address-wrap'),
                    toggleAddress = function () {
                        if ($sameCheckbox.prop('checked')) {
                            $wrapper.slideUp(function() {
                                $addressForm.address('clear');
                                if ($choiceSelect.length) {
                                    $choiceSelect.val(null).trigger('change');
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
