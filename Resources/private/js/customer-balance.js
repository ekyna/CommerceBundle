define(['jquery', 'ekyna-modal', 'ekyna-commerce/templates', 'ekyna-ui'], function ($, Modal, Templates) {


    function CustomerBalance ($element) {
        this.$element = $element;
        this.$element.data('customerBalance', this);

        this.$form = this.$element.find('form');
        this.$submit = this.$form.find('button[name="balance[submit]"]');

        this.init();
    }

    CustomerBalance.prototype.init = function() {
        this.bindEvents();
    };

    CustomerBalance.prototype.bindEvents = function() {
        this.$submit.on('click', this.update.bind(this));
    };

    /*CustomerBalance.prototype.unbindEvents = function() {
        this.$submit.off('click');
    };*/

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
