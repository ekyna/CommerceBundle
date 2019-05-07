/// <reference path="../../../../../../typings/index.d.ts" />
/// <reference path="../typings/templates.d.ts" />

import * as $ from 'jquery';
import * as Templates from 'ekyna-commerce/templates';
import * as _ from 'underscore';
import * as Dispatcher from 'ekyna-dispatcher';
import * as Form from 'ekyna-form';
import * as Ui from 'ekyna-ui';
import * as Bootstrap from 'bootstrap';
import * as Modal from 'ekyna-modal';

//noinspection JSUnusedLocalSymbols
let bs = Bootstrap;
//noinspection JSUnusedLocalSymbols
let ui = Ui;

interface AddToCartEvent {
    type: string
    data: any
    jqXHR: JQueryXHR
    success: boolean,
    modal: Ekyna.Modal
}

function dispatchAddToCartEvent(data: any, jqXHR:JQueryXHR, modal: Ekyna.Modal) {
    let event:AddToCartEvent = {
        type: Modal.prototype.getContentType(jqXHR),
        data: data,
        jqXHR : jqXHR,
        success: '1' == jqXHR.getResponseHeader('X-Commerce-Success'),
        modal: modal
    };

    Dispatcher.trigger('ekyna_commerce.add_to_cart', event);

    return event;
}

function init(config) {
    config = $.extend({
        debug: false,
        customer: {
            selector: '#customer-widget',
            event: 'ekyna_commerce.customer',
            debug: false
        },
        cart: {
            selector: '#cart-widget',
            event: 'ekyna_commerce.cart',
            debug: false,
        },
        context: {
            selector: '#context-widget',
            event: 'ekyna_commerce.context',
            debug: false,
        }
    }, config);

    let customerWidget, cartWidget, contextWidget;
    if (config.customer) {
        config.customer.debug = config.debug;
        customerWidget = new Widget(config.customer);
    }
    if (config.cart) {
        config.cart.debug = config.debug;
        cartWidget = new Widget(config.cart);
    }
    if (config.context) {
        config.context.debug = config.debug;
        contextWidget = new Widget(config.context);

        /** Same in src/Ekyna/Bundle/CoreBundle/Resources/private/js/form/phone-number.js */
        if (0 === $('link#core-flags-stylesheet').length) {
            let stylesheet = document.createElement('link');
            stylesheet.id = 'core-flags-stylesheet';
            stylesheet.href = '/bundles/ekynacore/css/flags.css';
            stylesheet.type = 'text/css';
            stylesheet.rel = 'stylesheet';
            $('head').append(stylesheet);
        }
    }

    if (customerWidget || cartWidget || contextWidget) {
        Dispatcher.on('ekyna_user.authentication', function () {
            if (customerWidget) {
                customerWidget.reload();
            }
            if (cartWidget) {
                cartWidget.reload();
            }
            if (contextWidget) {
                contextWidget.reload();
            }
        });
        if (cartWidget) {
            Dispatcher.on('ekyna_commerce.sale_view_response', function () {
                cartWidget.reload();
            });
            Dispatcher.on('ekyna_commerce.add_to_cart', function (e) {
                if (e.success) {
                    cartWidget.reload();
                }
            });
        }
    }

    $(document)
        // Sale item modal
        .on('click', 'a[data-add-to-cart]:not([data-add-to-cart=""])', function (e: JQueryEventObject) {
            if (e.ctrlKey || e.shiftKey || e.button === 2) {
                return true;
            }

            e.preventDefault();
            e.stopPropagation();

            let modal: Ekyna.Modal = new Modal();
            modal.load({
                url: $(e.currentTarget).data('add-to-cart'),
                method: 'GET'
            });
            $(modal).on('ekyna.modal.response', (e: Ekyna.ModalResponseEvent) => {
                dispatchAddToCartEvent(e.content, e.jqXHR, e.modal);
            });

            return false;
        })
        // Sale item form
        .on('submit', 'form[data-add-to-cart]:not([data-add-to-cart=""])', function (e: JQueryEventObject) {
            let $form = $(e.currentTarget).closest('form');

            e.preventDefault();
            e.stopPropagation();

            $form.loadingSpinner('on');

            $form.ajaxSubmit({
                url: $form.data('add-to-cart'),
                success: function (data, textStatus, jqXHR) {
                    let type = Modal.prototype.getContentType(jqXHR);
                    if (type === 'xml') {
                        let $xmlData = $(data),
                            $content = $xmlData.find('content');

                        if (1 === $content.length) {
                            $content = $($content.text());
                            if ($content.is('form')) {
                                $form.data('form').destroy();

                                $form.replaceWith($content);
                                $form = $content.eq(0);

                                let form = Form.create($form);
                                form.init();

                                return;
                            }
                        }
                    }

                    dispatchAddToCartEvent(data, jqXHR, null);

                    let modal = new Modal();
                    modal.handleResponse(data, textStatus, jqXHR);
                    $(modal).on('ekyna.modal.response', (e: Ekyna.ModalResponseEvent) => {
                        dispatchAddToCartEvent(e.content, e.jqXHR, e.modal);
                    });
                },
                complete: function () {
                    $form.loadingSpinner('off');
                }
            });

            return false;
        });
}

interface WidgetConfig {
    selector: string
    icon: string
    button: string
    dropdown: string
    template: Template
    event: string
    debug: boolean
    url: {
        widget: string,
        dropdown: string
    }
}

interface WidgetData {
    tag: string
    class: string
    icon: string
    id: string
    href: string
    title: string
    label: string
}

interface WidgetDataDefault {
    tag: string
    class: string
    icon: string
}

class Widget {
    private config: WidgetConfig;
    private defaultData: WidgetDataDefault;

    private $element: JQuery;
    private $button: JQuery;
    private $dropdown: JQuery;

    private busy: boolean;
    private preventReload: boolean;

    private dropdownShowHandler: () => void;

    constructor(options: WidgetConfig) {
        this.config = _.defaults(options, {
            tag: 'li',
            icon: '> a > span',
            button: '> a.dropdown-toggle',
            dropdown: '> div.dropdown-menu',
            template: Templates['@EkynaCommerce/Js/widget.html.twig'],
            debug: false
        });

        this.$element = $(this.config.selector);
        if (1 != this.$element.length) {
            throw 'Widget not found ! (' + this.config.selector + ')';
        }
        this.config.url = this.$element.data('url');

        this.dropdownShowHandler = _.bind(this.onDropdownShow, this);

        if (!this.config.debug) {
            $(window).on('focus', _.bind(this.onWindowFocus, this));
        }

        this.defaultData = {
            tag: this.$element.prop('tagName').toLowerCase(),
            class: this.$element.attr('class'),
            icon: null
        };

        let $icon = this.$element.find(this.config.icon);
        if (1 === $icon.length) {
            this.defaultData.icon = $icon.attr('class');
        }

        this.initialize();
    }

    reload(): void {
        if (this.busy) {
            return;
        }

        this.busy = true;

        let xhr = $.ajax({
            url: this.config.url.widget,
            method: 'GET',
            dataType: 'json',
            cache: false
        });

        xhr.done((data: WidgetData) => {
            this.renderWidget(data);

            if (this.config.event) {
                Dispatcher.trigger(this.config.event, data);
            }
        });

        xhr.fail(function () {
            console.log('Failed to reload widget.')
        });

        xhr.always(() => {
            this.busy = false;
        });
    }

    private initialize() {
        this.$button = this.$element.find(this.config.button);
        if (1 != this.$button.length) {
            throw 'Widget toggle button not found ! (' + this.config.button + ')';
        }

        this.$dropdown = this.$element.find(this.config.dropdown);
        if (1 != this.$dropdown.length) {
            throw 'Widget content not found ! (' + this.config.dropdown + ')';
        }

        this.$element.on('show.bs.dropdown', this.dropdownShowHandler);
    }

    private renderWidget(data: WidgetData) {
        let $element = $(this.config.template.render(_.defaults(data, this.defaultData)));
        this.$element.replaceWith($element);
        this.$element = $element;

        this.initialize();
    }

    private loadDropdown(): void {
        if (this.busy) {
            return;
        }

        this.busy = true;

        this.$dropdown.loadingSpinner('on');

        let xhr = $.ajax({
            url: this.config.url.dropdown,
            method: 'GET',
            dataType: 'html',
            data: this.$element.data('data'),
            cache: false,
        });

        xhr.done((html: string) => {
            this.$dropdown.html(html);
        });

        xhr.fail(function () {
            console.log('Failed to load widget dropdown.')
        });

        xhr.always(() => {
            this.$dropdown.loadingSpinner('off');
            this.busy = false;
        });
    }

    private onDropdownShow() {
        if (this.$dropdown.is(':empty')) {
            this.loadDropdown();
        }
    }

    private onWindowFocus() {
        if (!this.busy && !this.preventReload) {
            this.preventReload = true;

            setTimeout(() => {
                this.preventReload = false;
            }, 10000);

            this.reload();
        }
    }
}

export = {
    init: init,
    Widget: Widget
}
