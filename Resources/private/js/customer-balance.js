define(['jquery', 'ekyna-commerce/templates', 'ekyna-modal', 'ekyna-spinner'], function ($, Templates, Modal) {
    function CustomerBalance ($element) {
        this.$element = $element;
        this.$element.data('customerBalance', this);

        this.$form = this.$element.find('form');
        this.$submit = this.$form.find('button[name="balance[submit]"]');

        this.$submit.on('click', $.proxy(this.onSubmit, this));
    }

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
