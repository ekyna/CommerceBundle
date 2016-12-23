define(['jquery'], function($) {
    "use strict";

    /**
     * Sale address widget
     */
    $.fn.saleAddressWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
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

            $choiceSelect.on('change', function() {
                clearForm();

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
                    for (var key in mapping) {
                        if (mapping.hasOwnProperty(key)) {
                            $addressForm.find(mapping[key]).val(data[key]).trigger("change");
                        }
                    }
                }
            });

            if (1 == $sameCheckbox.length) {
                var $wrapper = $this.find('.sale-address-wrap'),
                    toggleAddress = function () {
                        if ($sameCheckbox.prop('checked')) {
                            $wrapper.slideUp(function() {
                                $choiceSelect.val(null).trigger('change');
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
