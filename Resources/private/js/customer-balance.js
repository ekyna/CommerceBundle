define(['jquery', 'ekyna-modal', 'ekyna-commerce/templates', 'ekyna-ui', 'ekyna-polyfill'], function ($, Modal, Templates) {

    function getToday() {
        var d = new Date(),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    function CustomerBalance ($element) {
        this.$element = $element;
        this.$element.data('customerBalance', this);

        this.$form = this.$element.find('form');
        this.$table = this.$element.find('table');

        this.$from = this.$form.find('input[name="balance[from]"]');
        this.$to = this.$form.find('input[name="balance[to]"]');
        this.$filter = this.$form.find('select[name="balance[filter]"]');
        this.$submit = this.$form.find('button[name="balance[submit]"]');

        this.init();
    }

    CustomerBalance.prototype.init = function() {
        this.bindEvents();
    };

    CustomerBalance.prototype.bindEvents = function() {
        this.$submit.on('click', $.proxy(this.filter, this));
    };

    CustomerBalance.prototype.unbindEvents = function() {
        this.$submit.off('click');
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

    CustomerBalance.prototype.filter = function(e) {
        e.preventDefault();
        e.stopPropagation();

        this.$element.loadingSpinner();

        this.$table.find('> tbody > tr').show();

        var from = this.$from.val(),
            to = this.$to.val(),
            filter = this.$filter.val(),
            credit = 0,
            debit = 0,
            balance,
            today = getToday();

        this.$table.find('> tbody > tr').each(function(index, row) {
            var $row = $(row),
                date = $row.data('date');

            if (from || to) {
                if (from && from > date) {
                    $row.hide();
                    return true;
                }

                if (to && to < date) {
                    $row.hide();
                    return true;
                }
            }

            if (filter !== 'all') {
                if ($row.data('type') !== 'invoice' || !$row.data('due')) {
                    $row.hide();
                    return true;
                }

                if (filter === 'due_invoices') {
                    if (date > today) {
                        $row.hide();
                        return true;
                    }
                } else if (filter === 'befall_invoices') {
                    if (date <= today) {
                        $row.hide();
                        return true;
                    }
                }
            }

            debit += parseFloat($(row).data('debit'));
            credit += parseFloat($(row).data('credit'));
        });

        balance = credit - debit;
        this.$table.find('> tfoot .debit-total').text(debit.localizedCurrency('EUR'));
        this.$table.find('> tfoot .credit-total').text(credit.localizedCurrency('EUR'));
        this.$table.find('> tfoot .balance-total').text(balance.localizedCurrency('EUR'));

        this.$element.loadingSpinner('off');

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
