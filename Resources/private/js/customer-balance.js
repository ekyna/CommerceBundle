define(['jquery', 'ekyna-commerce/templates', 'ekyna-modal', 'ekyna-ui'], function ($, Templates, Modal) {
    function CustomerBalance ($element) {
        this.$element = $element;
        this.$element.data('customerBalance', this);

        this.$form = this.$element.find('form');

        this.$from = this.$form.find('input[name="balance[from]"]');
        this.$to = this.$form.find('input[name="balance[to]"]');
        this.$filter = this.$form.find('select[name="balance[filter]"]');

        this.$submit = this.$form.find('button[name="balance[submit]"]');

        this.init();
    }

    CustomerBalance.prototype.init = function() {
        this.bindEvents();
        this.onFilterChange();
    };

    CustomerBalance.prototype.bindEvents = function() {
        this.$filter.on('change', $.proxy(this.onFilterChange, this));
        this.$submit.on('click', $.proxy(this.onSubmit, this));
    };

    CustomerBalance.prototype.unbindEvents = function() {
        this.$filter.off('change');
        this.$submit.off('click');
    };

    CustomerBalance.prototype.onFilterChange = function() {
        var disabled = this.$filter.val() !== 'all';

        this.$from.prop('disabled', disabled);
        this.$to.prop('disabled', disabled);
    };

    CustomerBalance.prototype.onSubmit = function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $element = this.$element;
        this.$element.loadingSpinner();

        this.$form.ajaxSubmit({
            dataType: 'json',
            success: function(balance) {
                var rows = Templates['@EkynaCommerce/Js/customer_balance_rows.html.twig'].render({
                    'balance': balance
                });

                $element.find('table > tbody').html(rows);
            },
            complete: function () {
                $element.loadingSpinner('off');
            }
        });

        return false;
    };

    $.fn.customerBalance = function () {
        return this.each(function () {
            if (undefined === $(this).data('customerBalance')) {
                new CustomerBalance($(this));
            }
        });
    };

    $('.customer-balance').customerBalance();

    return CustomerBalance;
});
