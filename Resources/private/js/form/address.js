define(['jquery', 'jquery-ui/ui/widget'], function($) {
    "use strict";

    var mapping = {
        company: 'input.address-company',
        gender: 'select.identity-gender',
        first_name: 'input.identity-first-name',
        last_name: 'input.identity-last-name',
        street: 'input.address-street',
        complement: 'input.address-complement',
        supplement: 'input.address-supplement',
        postal_code: 'input.address-postal-code',
        city: 'input.address-city',
        country: 'select.address-country',
        //state: '.address-state',
        phone: '.address-phone input.number',
        phone_country: '.address-phone input.country',
        mobile: '.address-mobile input.number',
        mobile_country: '.address-mobile input.country',
        digicode1: 'input.address-digicode1',
        digicode2: 'input.address-digicode2',
        intercom: 'input.address-intercom'
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
                    if (key === 'country' || key === 'phone_country' || key === 'mobile_country') {
                        continue;
                    }
                    if (this.element.find(mapping[key]).val()) {
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
