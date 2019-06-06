define(['jquery', 'ekyna-modal', 'ekyna-commerce/templates', 'ekyna-ui', 'ekyna-polyfill'], function ($, Modal, Templates) {

    function CustomerBalance ($element) {
        this.$element = $element;
        this.$element.data('customerBalance', this);

        this.$form = this.$element.find('form');
        this.$submit = this.$form.find('button[name="balance[submit]"]');

        this.$table = this.$element.find('table');
        this.$notDone = this.$form.find('input[name="balance[notDone]"]');

        this.init();
    }

    CustomerBalance.prototype.init = function() {
        this.bindEvents();
    };

    CustomerBalance.prototype.bindEvents = function() {
        this.$submit.on('click', this.update.bind(this));
        this.$notDone.on('change', this.toggleDone.bind(this));
    };

    CustomerBalance.prototype.unbindEvents = function() {
        this.$submit.off('click');
        this.$notDone.off('change');
    };

    CustomerBalance.prototype.update = function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $element = this.$element;
        this.$element.loadingSpinner();

        this.$form.ajaxSubmit({
            dataType: 'json',
            success: function(balance) {
                var body = Templates['@EkynaCommerce/Js/customer_balance_rows.html.twig'].render({
                    'balance': balance
                });

                $element.find('tbody').replaceWith($(body));
            },
            complete: function () {
                $element.loadingSpinner('off');
            }
        });

        return false;
    };

    CustomerBalance.prototype.toggleDone = function() {
        this.$table.find('> tbody > tr').show();

        if (this.$notDone.is(':checked')) {
            this.$table.find('> tbody > tr[data-done="1"]').hide();
        }

        var credit = 0, debit = 0, balance = 0;
        this.$table.find('> tbody > tr:visible').each(function(index, row) {
            debit += parseFloat($(row).data('debit'));
            credit += parseFloat($(row).data('credit'));
        });

        balance = credit - debit;
        this.$table.find('> tfoot .debit-total').text(debit.localizedCurrency('EUR')); // TODO currency
        this.$table.find('> tfoot .credit-total').text(credit.localizedCurrency('EUR')); // TODO currency
        this.$table.find('> tfoot .balance-total').text(balance.localizedCurrency('EUR')); // TODO currency
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
