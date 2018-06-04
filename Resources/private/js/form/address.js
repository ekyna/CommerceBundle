define(['jquery', 'jquery-ui/widget'], function($) {
    "use strict";

    var mapping = {
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
    };

    $.widget("ekyna.address", {
        set: function(data) {
            this.clear();

            for (var key in mapping) {
                if (mapping.hasOwnProperty(key) && data.hasOwnProperty(key)) {
                    this.element.find(mapping[key]).val(data[key]).trigger("change");
                }
            }
        },
        isEmpty: function() {
            for (var key in mapping) {
                if (mapping.hasOwnProperty(key)) {
                    if (key !== 'country' && this.element.find(mapping[key]).val()) {
                        return false;
                    }
                }
            }
            return true;
        },
        clear: function() {
            for (var key in mapping) {
                if (mapping.hasOwnProperty(key)) {
                    this.element.find(mapping[key]).val(null).trigger("change");
                }
            }
        }
    });
});
