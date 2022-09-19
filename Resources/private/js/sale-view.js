define(['jquery', 'underscore', 'router', 'ekyna-modal', 'ekyna-dispatcher', 'ekyna-spinner', 'jquery/form', 'bootstrap'],
    function($, _, Router, Modal, Dispatcher) {
    "use strict";

    let saleType, saleId, items = [], draggedItem = null;

    /** @param e DragEvent */
    function isBefore(e) {
        return e.offsetY < this.clientHeight / 2;
    }

    /** @param e DragEvent */
    function setDropClass(e) {
        if (isBefore.apply(this, [e])) {
            this.classList.add('drop-before');
            this.classList.remove('drop-after');
        } else {
            this.classList.add('drop-after');
            this.classList.remove('drop-before');
        }
    }

    function clearDropClass() {
        this.classList.remove('drop-after');
        this.classList.remove('drop-before');
    }

    /** @param e DragEvent */
    function itemDragStart(e) {
        if (!this.classList.contains('sale-detail-item')) {
            e.preventDefault();
            return false;
        }

        draggedItem = this;
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
    }

    /** @param e DragEvent */
    function itemDragEnd(e) {
        draggedItem = null;
        this.style.opacity = '1';

        items.forEach(function (item) {
            clearDropClass.apply(item);
        });
    }

    /** @param e DragEvent */
    function itemDragOver(e) {
        setDropClass.apply(this, [e]);
    }

    /** @param e DragEvent */
    function itemDragEnter(e) {
        e.preventDefault();
    }

    /** @param e DragEvent */
    function itemDragLeave(e) {
        clearDropClass.apply(this);
    }

    // /** @param e DragEvent */
    function itemDrop(e) {
        e.stopPropagation(); // stops the browser from redirecting.

        console.log('Item Drop');

        console.log(e);

        if (!draggedItem) {
            return;
        }

        let targetItem = e.target;
        while ('TR' !== targetItem.nodeName) {
            targetItem = targetItem.parentNode;
            if (!targetItem) {
                return;
            }
        }

        if ('TR' !== targetItem.nodeName) {
            return;
        }

        if (!targetItem.classList.contains('sale-detail-item')) {
            return;
        }

        if (targetItem === draggedItem) {
            return;
        }

        let $saleView = $(targetItem).closest('.sale-view');
        $saleView.loadingSpinner();

        let parameters = {};
        parameters[saleType + 'Id'] = saleId;
        parameters[saleType + 'ItemId'] = draggedItem.attributes.getNamedItem('data-id').value;
        parameters['targetId'] = targetItem.attributes.getNamedItem('data-id').value;
        parameters['mode'] = isBefore.apply(e.target, [e]) ? 'before' : 'after';

        let xhr = $.ajax({
            url: Router.generate('admin_ekyna_commerce_' + saleType + '_item_move', parameters),
            method: 'GET',
            dataType: 'xml'
        });
        xhr.done(function(response) {
            parseResponse(response, $saleView);
        });

        return false;
    }

    function initView() {
        $('.sale-view [data-toggle="popover"]').popover({
            trigger: 'hover',
            placement: 'top',
            html: true,
            sanitize: false
        });

        let $sale = $('.sale-view');
        saleType = $sale.data('type');
        saleId = $sale.data('id');

        let dragover = _.throttle(itemDragOver, 400, {trailing: false});

        items = document.querySelectorAll('.sale-detail tr[draggable=true]');
        items.forEach(function (item) {
            item.addEventListener('dragstart', itemDragStart);
            item.addEventListener('dragend', itemDragEnd);
            item.addEventListener('dragenter', itemDragEnter);
            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                dragover.apply(this, [e]);
            });
            item.addEventListener('dragleave', itemDragLeave);
            item.addEventListener('dragend', itemDragEnd);
        });
    }

    document.addEventListener('drop', itemDrop);

    function parseResponse(response, $saleView) {
        var $xml = $(response),
            $view = $xml.find('view');

        if (1 === $view.length) {
            $saleView.replaceWith($($view.text()));

            Dispatcher.trigger('ekyna_commerce.sale_view_response', response);

            initView();

            return true;
        }

        return false;
    }

    $(document).on('click', '.sale-view [data-sale-modal]', function(e) {
        e.preventDefault();

        var $this = $(this),
            $saleView = $this.closest('.sale-view');

        var modal = new Modal();
        modal.load({url: $this.attr('href')});

        $(modal).on('ekyna.modal.response', function (modalEvent) {
            if (modalEvent.contentType === 'xml') {
                if (parseResponse(modalEvent.content, $saleView)) {
                    modalEvent.preventDefault();
                    modalEvent.modal.close();
                }
            }
        });

        return false;
    });

    $(document).on('click', '.sale-view [data-sale-xhr]', function(e) {
        e.preventDefault();

        var $this = $(this), confirmation = $this.data('confirm');
        if (confirmation && confirmation.length && !confirm(confirmation)) {
            return false;
        }

        var $saleView = $this.closest('.sale-view'),
            method = $this.data('sale-xhr');

        $saleView.loadingSpinner();

        var xhr = $.ajax({
            url: $(this).attr('href'),
            method: method || 'post',
            dataType: 'xml'
        });
        xhr.done(function(response) {
            parseResponse(response, $saleView);
        });

        return false;
    });

    $(document).on('click', '.sale-view [data-sale-toggle-all-children]', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var $link = $(e.currentTarget),
            $saleView = $link.closest('.sale-view'),
            $children = $saleView.find('tr[data-parent]'),
            hidden = $children.filter(':not(:visible)').length,
            visible = $children.filter(':visible').length;

        if (hidden > visible) {
            $children.show();
        } else {
            $children.hide();
        }

        return false;
    });

    $(document).on('click', '.sale-view [data-sale-toggle-children]', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var $link = $(e.currentTarget),
            $saleView = $link.closest('.sale-view'),
            id = $link.data('sale-toggle-children'),
            shown = !!$link.data('sale-toggle-shown');

        function hideChildren($children) {
            $children.each(function() {
                $(this).hide().find('[data-sale-toggle-children]').each(function() {
                    var $link = $(this),
                        id = $link.data('sale-toggle-children'),
                        shown = !!$link.data('sale-toggle-shown');

                    if (id && shown) {
                        hideChildren($saleView.find('tr[data-parent="' + id + '"]'));
                        $link.data('sale-toggle-shown', false)
                    }
                });
            })
        }

        if (id) {
            var $children = $saleView.find('tr[data-parent="' + id + '"]');
            if (shown) {
                hideChildren($children);
            } else {
                $children.show();
            }
            $link.data('sale-toggle-shown', !shown);
        }

        return false;
    });

    $(document).on('submit', '.sale-view form', function(e) {
        e.preventDefault();

        var $saleView = $(e.target).closest('.sale-view').loadingSpinner();

        $(e.target).closest('form').ajaxSubmit({
            dataType: 'xml',
            success: function(response) {
                parseResponse(response, $saleView);
            }
        });

        return false;
    });

    initView();
});
