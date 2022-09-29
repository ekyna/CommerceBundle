define(['jquery', 'underscore', 'router', 'ekyna-modal', 'ekyna-dispatcher', 'ekyna-spinner', 'jquery/form', 'bootstrap'],
    function ($, _, Router, Modal, Dispatcher) {
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
            parameters[saleType + 'ItemId'] = draggedItem.getAttribute('data-id');
            parameters['targetId'] = targetItem.getAttribute('data-id');
            parameters['mode'] = isBefore.apply(e.target, [e]) ? 'before' : 'after';

            let xhr = $.ajax({
                url: Router.generate('admin_ekyna_commerce_' + saleType + '_item_move', parameters),
                method: 'GET',
                dataType: 'xml'
            });
            xhr.done(function (response) {
                parseResponse(response, $saleView);
            });

            return false;
        }

        document.addEventListener('drop', itemDrop);

        function initView() {
            $('.sale-view [data-toggle="popover"]').popover({
                trigger: 'hover',
                placement: 'top',
                html: true,
                sanitize: false
            });

            let saleView = document.querySelector('.sale-view'),
                $saleView = $('.sale-view');

            saleType = saleView.getAttribute('data-type');
            saleId = saleView.getAttribute('data-id');

            let dragover = _.throttle(itemDragOver, 400, {trailing: false});

            items = saleView.querySelectorAll('.sale-detail tr[draggable=true]');
            items.forEach(function (item) {
                item.addEventListener('dragstart', itemDragStart);
                item.addEventListener('dragend', itemDragEnd);
                item.addEventListener('dragenter', itemDragEnter);
                item.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    dragover.apply(this, [e]);
                });
                item.addEventListener('dragleave', itemDragLeave);
                item.addEventListener('dragend', itemDragEnd);
            });

            let itemCheckboxes = saleView.querySelectorAll('input[name="sale[items][]"]'),
                batchButtons = saleView.querySelectorAll('button[type=button].sale-view-batch');

            function toggleBatchButtons() {
                let disabled = true;
                for (let checkbox of itemCheckboxes.values()) {
                    if (checkbox.checked) {
                        disabled = false;
                        break;
                    }
                }

                let changeButton = disabled
                    ? function (/** HTMLElement */button) {
                        button.classList.add('disabled');
                        button.setAttribute('disabled', 'disabled');
                    }
                    : function (/** HTMLElement */button) {
                        button.classList.remove('disabled');
                        button.removeAttribute('disabled');
                    };

                batchButtons.forEach(changeButton);
            }

            itemCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', toggleBatchButtons);
            });
            toggleBatchButtons();

            batchButtons.forEach((button) => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    if (button.hasAttribute('data-confirm') && !confirm(button.getAttribute('data-confirm'))) {
                        return;
                    }

                    $saleView.loadingSpinner();

                    let data = new FormData();
                    saleView.querySelectorAll('input[name="sale[items][]"]:checked').forEach((input) => {
                        data.append('sale[items][]', input.value);
                    });

                    let parameters = {};
                    parameters[saleType + 'Id'] = saleId;
                    parameters['action'] = e.target.getAttribute('data-action');

                    let uri = Router.generate('admin_ekyna_commerce_' + saleType + '_batch', parameters);

                    let request = new Request(uri, {
                        method: 'post',
                        body: data
                    });

                    fetch(request)
                        .then((response) => {
                            if (!response.ok) {
                                return;
                            }

                            response.text().then((result) => {
                                let dom = new DOMParser().parseFromString(result, 'application/xml');

                                parseResponse(dom, $saleView);
                            });
                        });
                });
            })
        }

        function parseResponse(response, $saleView) {
            let $xml = $(response),
                $view = $xml.find('view');

            if (1 === $view.length) {
                $saleView.replaceWith($($view.text()));

                Dispatcher.trigger('ekyna_commerce.sale_view_response', response);

                initView();

                return true;
            }

            return false;
        }

        $(document).on('click', '.sale-view [data-sale-modal]', function (e) {
            e.preventDefault();

            let $this = $(this),
                $saleView = $this.closest('.sale-view');

            let modal = new Modal();
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

        $(document).on('click', '.sale-view [data-sale-xhr]', function (e) {
            e.preventDefault();

            let $this = $(this), confirmation = $this.data('confirm');
            if (confirmation && confirmation.length && !confirm(confirmation)) {
                return false;
            }

            let $saleView = $this.closest('.sale-view'),
                method = $this.data('sale-xhr');

            $saleView.loadingSpinner();

            let xhr = $.ajax({
                url: $(this).attr('href'),
                method: method || 'post',
                dataType: 'xml'
            });
            xhr.done(function (response) {
                parseResponse(response, $saleView);
            });

            return false;
        });

        $(document).on('click', '.sale-view [data-sale-toggle-all-children]', function (e) {
            e.stopPropagation();
            e.preventDefault();

            let $link = $(e.currentTarget),
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

        $(document).on('click', '.sale-view [data-sale-toggle-children]', function (e) {
            e.stopPropagation();
            e.preventDefault();

            let $link = $(e.currentTarget),
                $saleView = $link.closest('.sale-view'),
                id = $link.data('sale-toggle-children'),
                shown = !!$link.data('sale-toggle-shown');

            function hideChildren($children) {
                $children.each(function () {
                    $(this).hide().find('[data-sale-toggle-children]').each(function () {
                        let $link = $(this),
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
                let $children = $saleView.find('tr[data-parent="' + id + '"]');
                if (shown) {
                    hideChildren($children);
                } else {
                    $children.show();
                }
                $link.data('sale-toggle-shown', !shown);
            }

            return false;
        });

        $(document).on('submit', '.sale-view form', function (e) {
            e.preventDefault();

            let $saleView = $(e.target).closest('.sale-view').loadingSpinner();

            $(e.target).closest('form').ajaxSubmit({
                dataType: 'xml',
                success: function (response) {
                    parseResponse(response, $saleView);
                }
            });

            return false;
        });

        initView();
    });
