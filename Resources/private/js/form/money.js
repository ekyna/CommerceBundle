define(['jquery', 'ekyna-polyfill', 'bootstrap'], function ($) {
    "use strict";

    function MoneyWidget($element) {
        this.$element = $element;
        this.$element.data('moneyWidget', this);

        this.$base = this.$element.find('.commerce-money-base');
        this.$quote = this.$element.find('.commerce-money-quote');
        if (0 === this.$base.size() || 0 === this.$quote.size()) {
            return;
        }

        this.config = this.$element.data('config');
        this.options = {
            minimumFractionDigits: this.config.scale,
            useGrouping: false
        };

        this.$base.on('change keyup', $.proxy(this.onBaseChange, this));
        this.$quote.on('change keyup', $.proxy(this.onQuoteChange, this));

        this.onBaseChange();

        this.$element.tooltip();
    }

    MoneyWidget.prototype.onBaseChange = function() {
        var value = parseFloat(
            this.$base.val()
                .replace(' ', '') // \u202F
                .replace(',', '.')
        );

        if (isNaN(value)) {
            value = 0;
        }

        this.$quote.val((value * this.config.rate).localizedNumber(null, this.options));
    };

    MoneyWidget.prototype.onQuoteChange = function() {
        var value = parseFloat(
            this.$quote.val()
                .replace(' ', '') // \u202F
                .replace(',', '.')
        );

        if (isNaN(value)) {
            value = 0;
        }

        this.$base.val((value / this.config.rate).localizedNumber(null, this.options));
    };

    return {
        init: function ($element) {
            $element.each(function () {
                if (undefined === $(this).data('moneyWidget')) {
                    new MoneyWidget($(this));
                }
            });
        }
    };
});
