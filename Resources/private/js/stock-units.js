define(['jquery', 'ekyna-dispatcher', 'ekyna-modal', 'ekyna-commerce/templates', 'ekyna-ui'], function ($, Dispatcher, Modal, Templates) {

    var StockUnits = function (id) {
        this.$element = $('#' + id);

        this.prefix = this.$element.data('prefix');
        this.$tbody = this.$element.find('> table > tbody');

        var that = this;
        this.$element.on('click', '[data-stock-unit-modal]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var modal = new Modal(),
                $button = $(e.currentTarget),
                $tr = that.$element.find('tr#' + $button.data('rel'));

            modal.load({
                url: $(e.currentTarget).attr('href'),
                method: 'GET'
            });

            $(modal).on('ekyna.modal.response', function (modalEvent) {
                if (modalEvent.contentType === 'json') {
                    modalEvent.preventDefault();

                    that.render($tr, modalEvent.content);

                    modalEvent.modal.close();

                    Dispatcher.trigger('ekyna_commerce.stock_units.change');
                }
            });

            return false;
        });
    };

    StockUnits.prototype.render = function ($tr, data) {
        var id = $tr.attr('id');

        var content = Templates['stock_unit_rows.html.twig'].render({
            'prefix': this.prefix,
            'stock_units': data.stock_units
        });

        this.$tbody.html(content);

        this.$tbody.find('tr#' + id + '_adjustments').show();
    };

    return StockUnits;
});
