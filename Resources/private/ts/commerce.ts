/// <reference path="../../../../../../typings/index.d.ts" />
/// <reference path="../typings/templates.d.ts" />

import * as $ from 'jquery';
import * as Router from 'routing';
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
    success: boolean
}

function dispatchAddToCartEvent(data: any, jqXHR:JQueryXHR) {
    let event:AddToCartEvent = {
        type: Modal.prototype.getContentType(jqXHR),
        data: data,
        jqXHR : jqXHR,
        success: '1' == jqXHR.getResponseHeader('X-Commerce-Success')
    };

    Dispatcher.trigger('ekyna_commerce.add_to_cart', event);

    return event;
}

export function init() {
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
                dispatchAddToCartEvent(e.data, e.jqXHR);
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
                                $form = $content;

                                let form = Form.create($form);
                                form.init();

                                return;
                            }
                        }
                    }

                    dispatchAddToCartEvent(data, jqXHR);

                    let modal = new Modal();
                    modal.handleResponse(data, textStatus, jqXHR);
                    $(modal).on('ekyna.modal.response', (e: Ekyna.ModalResponseEvent) => {
                        dispatchAddToCartEvent(e.data, e.jqXHR);
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
    tag: string
    class: string
    debug: boolean
    button: string
    dropdown: string
    widget_route: string
    widget_template: Template
    dropdown_route: string
    event: string
}

interface WidgetData {
    tag: string
    class: string
    id: string
    href: string
    title: string
    label: string
    icon: string
}

export class Widget {
    private config: WidgetConfig;

    private $element: JQuery;
    private $button: JQuery;
    private $dropdown: JQuery;

    private busy: boolean;
    private preventReload: boolean;

    private dropdownShowHandler: () => void;

    constructor(options: WidgetConfig) {
        this.config = _.defaults(options, {
            tag: 'li',
            class: '',
            debug: false,
            button: '> a.dropdown-toggle',
            dropdown: '> div.dropdown-menu',
            widget_template: Templates['widget.html.twig']
        });

        this.$element = $(this.config.selector);
        if (1 != this.$element.length) {
            throw 'Widget not found ! (' + this.config.selector + ')';
        }

        this.dropdownShowHandler = _.bind(this.onDropdownShow, this);

        if (!this.config.debug) {
            $(window).on('focus', _.bind(this.onWindowFocus, this));
        }

        this.initialize();
    }

    reload(): void {
        if (this.busy) {
            return;
        }

        this.busy = true;

        let xhr = $.ajax({
            url: Router.generate(this.config.widget_route),
            method: 'GET',
            dataType: 'json',
            cache: false
        });

        xhr.done((data: WidgetData) => {
            this.renderWidget(data);

            Dispatcher.trigger(this.config.event, data);
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
        data.tag = this.config.tag;
        data.class = this.config.class;

        this.$element.empty().append($(this.config.widget_template.render(data)).children());

        this.initialize();
    }

    private loadDropdown(): void {
        if (this.busy) {
            return;
        }

        this.busy = true;

        this.$dropdown.loadingSpinner('on');

        let xhr = $.ajax({
            url: Router.generate(this.config.dropdown_route),
            method: 'GET',
            dataType: 'html',
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