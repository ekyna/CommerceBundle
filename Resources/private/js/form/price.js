define(['jquery', 'jquery-ui/widget', 'ekyna-number'], function ($) {
    "use strict";

    var defaultLocale = (navigator.language || navigator.browserLanguage).split('-')[0] || 'en';

    /**
     * Price type widget
     */
    $.widget('ekyna_commerce.priceType', {
        options: {
            taxes: null
        },
        _create: function () {
            this.config = $.extend({
                tax_group: '.tax-group-choice',
                rates: [],
                precision: 2
            }, this.element.data('config'));

            this.$input = this.element.find('.commerce-price-input');
            this.$mode = this.element.find('.commerce-price-mode');
            this.$rates = this.element.find('.commerce-price-rates');
            this.$value = this.element.find('.commerce-price-value');
            this.$group = $(this.config.tax_group);

            if (
                1 !== this.$input.length ||
                1 !== this.$mode.length ||
                1 !== this.$value.length
            ) {
                throw 'Missing commerce price type fields';
            }

            if (1 === this.$group.length && !Array.isArray(this.options.taxes)) {
                this._on(this.$group, {'change': this._loadRates});
            }
            this._on(this.$mode, {'change': this._display});
            this._on(this.$input, {'keyup': this._calculate, 'blur': this._display});

            this._loadRates();
        },
        _destroy: function () {
            if (1 === this.$group.length && !Array.isArray(this.options.taxes)) {
                this._off(this.$group, 'change');
            }
            this._off(this.$mode, 'change');
            this._off(this.$input, 'keyup blur');

            this.rates = undefined;
            this.$input = undefined;
            this.$mode = undefined;
            this.$value = undefined;
            this.$group = undefined;
        },
        _setOption: function (key, value) {
            this._super(key, value);

            if (key === "taxes") {
                this._loadRates();
            }
        },
        _loadRates: function () {
            this.rates = [];
            this.$rates.empty();

            if (Array.isArray(this.options.taxes)) {
                this.rates = this.options.taxes;
            } else if (1 === this.$group.length) {
                var group = this.$group.val();
                if (group) {
                    var $option = this.$group.find('option[value=' + this.$group.val() + ']');
                    if (1 === $option.length) {
                        var that = this;
                        $.each($option.data('taxes'), function (index, value) {
                            that.rates.push(value.rate);
                        });
                    }
                }
            } else {
                this.rates = this.config.rates;
            }

            if (0 < this.rates.length) {
                this.$rates.html('&nbsp;(' + this.rates.map(function(rate) {
                    return (rate * 100) + '%';
                }).join(',&nbsp;') + ')');
            }

            this._display();
        },
        _display: function () {
            var value = parseFloat(this.$value.val()),
                price = 0,
                precision = this.config.precision;

            if (isNaN(value)) {
                this.$input.val(null);
            } else {
                price = Math.fRound(value, precision);
                if (this.$mode.is(':checked')) {
                    $.each(this.rates, function (index, rate) {
                        price += Math.fRound(value * rate, precision);
                    });
                }

                this.$input.val(price.toLocaleString(defaultLocale, {
                    minimumFractionDigits: precision,
                    useGrouping: false
                }));
            }

            this._calculate();
        },
        _calculate: function () {
            var input = parseFloat(this.$input.val().replace(',', '.').replace(' ', '')),
                value = 0;

            if (isNaN(input)) {
                this.$value.val(null);

                if (this.$input.prop('required')) {
                    this.element.find('.input-group').addClass('has-error');
                }

                return;
            }

            this.element.find('.input-group').removeClass('has-error');

            value = Math.fRound(input, this.config.precision);

            // Net to Ati
            if (!this.$mode.is(':checked')) {
                $.each(this.rates, function (index, rate) {
                    value *= 1 + rate;
                });
                value = Math.fRound(value, this.config.precision);
            }

            // Ati To Net
            $.each(this.rates, function (index, rate) {
                value /= 1 + rate;
            });
            value = Math.fRound(value, 5); // 5 decimals

            this.$value.val(value);
        },
        save: function() {
            this._calculate();
        }
    });

    return {
        init: function ($element) {
            $element.priceType();
        },
        save: function ($element) {
            if ($element.data('ekyna_commerce.priceType')) {
                $element.priceType('save');
            }
        },
        destroy: function ($element) {
            if ($element.data('ekyna_commerce.priceType')) {
                $element.priceType('destroy');
            }
        }
    };
});
