/// <reference path="../../../../../../typings/index.d.ts" />

import * as $ from 'jquery';
import * as _ from 'underscore';
import * as Bootstrap from 'bootstrap';
import * as Modal from 'ekyna-modal';
import * as Ui from 'ekyna-ui';
import * as Dispatcher from 'ekyna-dispatcher';

//noinspection JSUnusedLocalSymbols
let bs = Bootstrap;
//noinspection JSUnusedLocalSymbols
let ui = Ui;

interface Config {
    widgetSelector: string
    contentSelector: string
    toggleSelector: string
}

class CartEvent {
    authenticated:boolean;
}

class CartWidget {
    private config:Config;
    private enabled:boolean;
    private contentXHR:JQueryXHR;

    private $widget:JQuery;
    private $content:JQuery;
    private $toggle:JQuery;

    private modalLinkClickHandler:(e:JQueryEventObject) => boolean;
    //private toggleClickHandler:(e:JQueryEventObject) => void;
    private contentShowHandler:() => void;

    constructor() {
        this.enabled = false;
    }

    initialize(config?:Config):CartWidget {
        this.config = _.defaults(config || {}, {
            widgetSelector: '#cart-widget',
            toggleSelector: '> a',
            contentSelector: '> div'
        });

        this.$widget = $(this.config.widgetSelector);
        if (1 != this.$widget.length) {
            throw 'Widget not found ! (' + this.config.widgetSelector + ')';
        }

        this.$content = this.$widget.find(this.config.contentSelector);
        if (1 != this.$content.length) {
            throw 'Widget content not found ! (' + this.config.contentSelector + ')';
        }

        this.$toggle = this.$widget.find(this.config.toggleSelector);
        if (1 != this.$toggle.length) {
            throw 'Widget toggle button not found ! (' + this.config.toggleSelector + ')';
        }

        //this.toggleClickHandler = _.bind(this.onToggleClick, this);
        this.contentShowHandler = _.bind(this.onContentShow, this);
        //this.modalLinkClickHandler = _.bind(this.onModalLinkClick, this);

        Dispatcher.on('ekyna_commerce.sale_view_response', () => {
            this.$content.empty();
        });

        return this;
    }

    enable():CartWidget {
        if (this.enabled) {
            return;
        }

        this.enabled = true;

        //this.$toggle.on('click', this.toggleClickHandler);
        this.$widget.on('show.bs.dropdown', this.contentShowHandler);

        //$(document).on('click', '[data-cart-modal]', this.modalLinkClickHandler);

        return this;
    }

    disable():CartWidget {
        if (!this.enabled) {
            return;
        }

        this.enabled = false;

        //this.$toggle.off('click', this.toggleClickHandler);
        this.$widget.off('show.bs.dropdown', this.contentShowHandler);

        //$(document).off('click', '[data-cart-modal]', this.modalLinkClickHandler);

        return this;
    }

    /*onToggleClick(e:JQueryEventObject):void {
        e.preventDefault();

        this.$toggle.dropdown('toggle');

        return false;
     }*/

    onContentShow():void {
        if (this.$content.is(':empty')) {
            this.loadContent();
        }
    }

    /*onModalLinkClick(clickEvent:JQueryEventObject):boolean {
        clickEvent.preventDefault();

        let modal:Ekyna.Modal = new Modal();

        $(modal).on('ekyna.modal.response', (modalEvent:Ekyna.ModalResponseEvent) => {
            if (modalEvent.contentType == 'xml') {
                if (this.parseResponse(modalEvent.content)) {
                    modalEvent.preventDefault();
                }
            } else if (modalEvent.contentType == 'json') {
                modalEvent.preventDefault();

                if (modalEvent.content.success) {
                    this.loadContent();
                }
            }
        });

        modal.load({
            url: $(clickEvent.target).attr('href'),
            method: 'GET'
        });

        return false;
    }*/

    loadContent():void {
        this.$content.loadingSpinner('on');

        if (this.contentXHR) {
            this.contentXHR.abort();
        }

        this.contentXHR = $.ajax({
            url: this.$widget.data('url'),
            dataType: 'xml',
            cache: false,
        });

        this.contentXHR.done((xml:XMLDocument) => this.parseResponse(xml));

        this.contentXHR.fail(function() {
            console.log('Failed to load account widget content.')
        });
    }

    parseResponse(xml:XMLDocument):boolean {
        let widgetNode:Element = xml.querySelector('cart-widget');
        if (widgetNode) {
            this.$content
                .loadingSpinner('off')
                .html(widgetNode.textContent);

            let event = new CartEvent();
            //event.authenticated = parseInt(widgetNode.getAttribute('status')) == 1;
            Dispatcher.trigger('ekyna_commerce.cart_status', event);

            return true;
        }

        return false;
    }
}

export = new CartWidget();
