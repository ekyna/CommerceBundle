/// <reference path="../../../../../../../assets/typings/index.d.ts" />
/// <reference path="../typings/templates.d.ts" />

import * as $ from 'jquery';
import * as Router from 'routing';
import * as Templates from 'ekyna-commerce/templates';
import * as Ui from 'ekyna-spinner';
import * as Bootstrap from 'bootstrap';
import * as Modal from 'ekyna-modal';

//noinspection JSUnusedLocalSymbols
let bs = Bootstrap;
//noinspection JSUnusedLocalSymbols
let ui = Ui;

interface Routes {
    ticket: string
    message: string
    attachment: string
    order: string
    quote: string
}

let trans:{[id:string]: string};
let config: {
    standalone: boolean
    editable: boolean
    admin: boolean
    routes: Routes
};

interface Response {
    ticket?: TicketData
    ticketMessage?: MessageData
    ticketAttachment?: AttachmentData
}

interface TicketData {
    id: number
    messages: Array<MessageData>
    expanded?: boolean
}

interface MessageData {
    id: number
    ticket: number
    attachments: Array<AttachmentData>
}

interface AttachmentData {
    id: number
    ticket: number
    message: number
    internal: boolean
}

class Support {
    private readonly $element: JQuery;
    private tickets: Array<Ticket>;

    constructor($element: JQuery) {
        this.$element = $element;
        this.tickets = [];

        $element.data('support', this);
    }

    public init(): Support {
        this.$element.find('.ticket').each((index, ticket) => {
            this.tickets.push(new Ticket(this, $(ticket)).init());
        });

        this.$element.on('click', '.ticket-new', (e:JQueryMouseEventObject) => {
            this.request($(e.currentTarget).data('url'))
        });

        if (this.tickets.length) {
            this.tickets[0].show();
        }

        return this;
    }

    public findTicket(id: number): Ticket {
        let ticket: Ticket = null;

        this.tickets.forEach(function(t: Ticket) {
            if (t.getId() === id) {
                ticket = t;
                return false;
            }
        });

        return ticket;
    }

    public request(url: string, onSuccess?: Function) {
        let modal: Ekyna.Modal = new Modal();
        modal.load({url: url});

        $(modal).on('ekyna.modal.response', (e: Ekyna.ModalResponseEvent) => {
            if (e.contentType !== 'json') {
                return;
            }

            this.parse(e.content);

            if (onSuccess) {
                onSuccess();
            }

            modal.close();
        });
    }

    public createTicket(ticket: TicketData) {
        let $ticket = $(Templates['@EkynaCommerce/Js/ticket.html.twig'].render({
            ticket: ticket,
            trans: trans,
            config: config
        }));

        this.$element.find('.tickets').prepend($ticket);

        this.tickets.forEach(function (t: Ticket) {
            t.hide();
        });

        this.tickets.push(new Ticket(this, $ticket).init().show());

        this.$element.find('.no-tickets').hide();
    }

    public removeTicket(id: number) {
        let index: number;
        this.tickets.forEach(function(t: Ticket, i: number) {
            if (t.getId() === id) {
                t.delete();
                index = i;
                return false;
            }
        });
        if (index) {
            this.tickets.splice(index, 1);
        }
    }

    private parse(response: Response) {
        if (response.ticket) {
            let ticket = this.findTicket(response.ticket.id);
            if (ticket) {
                ticket.update(response.ticket);
            } else {
                this.createTicket(response.ticket);
            }
        }

        if (response.ticketMessage) {
            let ticket = this.findTicket(response.ticketMessage.ticket);
            if (!ticket) {
                throw "Ticket not found.";
            }

            let message = ticket.findMessage(response.ticketMessage.id);
            if (message) {
                message.update(response.ticketMessage);
            } else {
                ticket.createMessage(response.ticketMessage);
            }
        }

        if (response.ticketAttachment) {
            let ticket = this.findTicket(response.ticketAttachment.ticket);
            if (!ticket) {
                throw "Ticket not found.";
            }

            let message = ticket.findMessage(response.ticketAttachment.message);
            if (!message) {
                throw "Message not found.";
            }

            let attachment = message.findAttachment(response.ticketAttachment.id);
            if (attachment) {
                attachment.update(response.ticketAttachment);
            } else {
                message.createAttachment(response.ticketAttachment);
            }
        }
    }
}

class Ticket {
    private readonly id: number;
    private readonly support: Support;
    private $element: JQuery;
    private messages: Array<Message>;

    constructor(support: Support, $element: JQuery) {
        this.id = $element.data('id');
        this.support = support;
        this.$element = $element;
        this.messages = [];

        $element.data('ticket', this);
    }

    public init(): Ticket {
        this.$element.find('.message').each((index, message) => {
            this.messages.push(new Message(this, $(message)).init());
        });

        this.$element.on('click', '.message-new', () => this.newMessage());
        this.$element.on('click', '.ticket-edit', () => this.edit());
        this.$element.on('click', '.ticket-remove', () => this.remove());
        this.$element.on('click', '.ticket-close', () => this.close());
        this.$element.on('click', '.ticket-open', () => this.open());

        this.$element.on('click', '.customer-show', (e: JQueryMouseEventObject) => Ticket.showCustomer(e));
        this.$element.on('click', '.order-show', (e: JQueryMouseEventObject) => Ticket.showOrder(e));
        this.$element.on('click', '.quote-show', (e: JQueryMouseEventObject) => Ticket.showQuote(e));

        return this;
    }

    private static showCustomer(e: JQueryMouseEventObject) {
        e.preventDefault();
        e.stopPropagation();

        if (!config.admin) {
            return;
        }

        window.open(
            Router.generate(config.routes.order + '_read', {customerId: $(e.target).data('id')})
            , '_blank'
        ).focus();

        return false;
    }

    private static showOrder(e: JQueryMouseEventObject) {
        e.preventDefault();
        e.stopPropagation();

        let parameters;
        if (config.admin) {
            parameters = {orderId: $(e.target).data('id')};
        } else {
            parameters = {number: $(e.target).text()};
        }

        window.open(Router.generate(config.routes.order + '_read', parameters), '_blank').focus();

        return false;
    }

    private static showQuote(e: JQueryMouseEventObject) {
        e.preventDefault();
        e.stopPropagation();

        let parameters;
        if (config.admin) {
            parameters = {quoteId: $(e.target).data('id')};
        } else {
            parameters = {number: $(e.target).text()};
        }

        window.open(Router.generate(config.routes.quote + '_read', parameters), '_blank').focus();

        return false;
    }

    public getId(): number {
        return this.id;
    }

    public getSupport(): Support {
        return this.support;
    }

    private newMessage() {
        this.support.request(
            Router.generate(config.routes.message + '_create', {
                'ticketId': this.id
            })
        );
    }

    private edit() {
        this.support.request(
            Router.generate(config.routes.ticket + '_update', {
                'ticketId': this.id
            })
        );
    }

    private remove() {
        this.support.request(
            Router.generate(config.routes.ticket + '_delete', {
                'ticketId': this.id
            }),
            () => {
                this.support.removeTicket(this.id);
                this.delete();
            }
        );
    }

    private open() {
        this.support.request(
            Router.generate(config.routes.ticket + '_open', {
                'ticketId': this.id
            })
        );
    }

    private close() {
        this.support.request(
            Router.generate(config.routes.ticket + '_close', {
                'ticketId': this.id
            })
        );
    }

    public findMessage(id: number): Message {
        let message: Message = null;

        this.messages.forEach(function(m: Message) {
            if (m.getId() === id) {
                message = m;
                return false;
            }
        });

        return message;
    }

    public createMessage(message: MessageData) {
        let $message = $(Templates['@EkynaCommerce/Js/ticket_message.html.twig'].render({
            message: message,
            trans: trans,
            config: config
        }));

        this.$element.find('.messages').append($message);

        this.messages.push(new Message(this, $message).init());

        return;
    }

    public removeMessage(id: number) {
        let index: number;
        this.messages.forEach(function(m: Message, i: number) {
            if (m.getId() === id) {
                m.delete();
                index = i;
                return false;
            }
        });
        if (index) {
            this.messages.splice(index, 1);
        }
    }

    public update(data: TicketData) {
        data.expanded = true;

        if (data.messages !== undefined) {
            let $ticket = $(Templates['@EkynaCommerce/Js/ticket.html.twig'].render({
                ticket: data,
                trans: trans,
                config: config
            }));

            this.$element.replaceWith($ticket);
            this.$element = $ticket;

            this.init();

            return;
        }

        this.$element.find('.ticket-header').html(
            Templates['@EkynaCommerce/Js/ticket_header.html.twig'].render({
                ticket: data,
                trans: trans,
                config: config
            })
        );
        this.$element.find('.ticket-body').html(
            Templates['@EkynaCommerce/Js/ticket_body.html.twig'].render({
                ticket: data,
                trans: trans,
                config: config
            })
        );
        this.$element.find('.ticket-footer').html(
            Templates['@EkynaCommerce/Js/ticket_footer.html.twig'].render({
                ticket: data,
                trans: trans,
                config: config
            })
        );
    }

    public delete() {
        this.$element.remove();
    }

    public show(): Ticket {
        this.$element.find('.collapse').collapse('show');

        return this;
    }

    public hide(): Ticket {
        this.$element.find('.collapse').collapse('hide');

        return this;
    }
}

class Message {
    private readonly id: number;
    private readonly ticket: Ticket;
    private $element: JQuery;
    private attachments: Array<Attachment>;

    constructor(ticket: Ticket, $element: JQuery) {
        this.id = $element.data('id');
        this.ticket = ticket;
        this.$element = $element;

        $element.data('message', this);
    }

    public init(): Message {
        this.attachments = [];

        this.$element.find('.attachment').each((index, attachment) => {
            this.attachments.push(new Attachment(this, $(attachment)).init());
        });

        this.$element.on('click', '.attachment-new', () => this.newAttachment());
        this.$element.on('click', '.message-edit', () => this.edit());
        this.$element.on('click', '.message-remove', () => this.remove());

        return this;
    }

    public getId(): number {
        return this.id;
    }

    public getTicket(): Ticket {
        return this.ticket;
    }

    private newAttachment() {
        this.ticket.getSupport().request(
            Router.generate(config.routes.attachment + '_create', {
                'ticketId': this.ticket.getId(),
                'ticketMessageId': this.id
            })
        );
    }

    private edit() {
        this.ticket.getSupport().request(
            Router.generate(config.routes.message + '_update', {
                'ticketId': this.ticket.getId(),
                'ticketMessageId': this.id
            })
        );
    }

    private remove() {
        this.ticket.getSupport().request(
            Router.generate(config.routes.message + '_delete', {
                'ticketId': this.ticket.getId(),
                'ticketMessageId': this.id
            }),
            () => {
                this.ticket.removeMessage(this.id);
                this.delete();
            }
        );
    }

    public findAttachment(id: number): Attachment {
        let attachment: Attachment = null;

        this.attachments.forEach(function(a: Attachment) {
            if (a.getId() === id) {
                attachment = a;
                return false;
            }
        });

        return attachment;
    }

    public createAttachment(attachment: AttachmentData) {
        let $attachment = $(Templates['@EkynaCommerce/Js/ticket_attachment.html.twig'].render({
            attachment: attachment,
            trans: trans,
            config: config
        }));

        this.$element.find('.attachments').append($attachment);

        this.attachments.push(new Attachment(this, $attachment).init());

        return;
    }

    public removeAttachment(id: number) {
        let index: number;
        this.attachments.forEach(function(a: Attachment, i: number) {
            if (a.getId() === id) {
                a.delete();
                index = i;
                return false;
            }
        });
        if (index) {
            this.attachments.splice(index, 1);
        }
    }

    public update(data: MessageData) {
        let $message = $(Templates['@EkynaCommerce/Js/ticket_message.html.twig'].render({
            message: data,
            trans: trans,
            config: config
        }));

        this.$element.replaceWith($message);
        this.$element = $message;

        this.init();
    }

    public delete() {
        this.$element.remove();
    }
}

class Attachment {
    private readonly id: number;
    private readonly message: Message;
    private $element: JQuery;

    constructor(message: Message, $element: JQuery) {
        this.id = $element.data('id');
        this.message = message;
        this.$element = $element;

        $element.data('attachment', this);
    }

    public init(): Attachment {
        this.$element.on('click', '.attachment-download', () => this.download());
        this.$element.on('click', '.attachment-edit', () => this.edit());
        this.$element.on('click', '.attachment-remove', () => this.remove());

        return this;
    }

    public getId() {
        return this.id;
    }

    private download() {
        window.open(Router.generate(config.routes.attachment + '_download', {
            'ticketId': this.message.getTicket().getId(),
            'ticketMessageId': this.message.getId(),
            'ticketAttachmentId': this.id
        }), '_blank').focus();
    }

    private edit() {
        this.message.getTicket().getSupport().request(
            Router.generate(config.routes.attachment + '_update', {
                'ticketId': this.message.getTicket().getId(),
                'ticketMessageId': this.message.getId(),
                'ticketAttachmentId': this.id
            })
        );
    }

    private remove() {
        this.message.getTicket().getSupport().request(
            Router.generate(config.routes.attachment + '_delete', {
                'ticketId': this.message.getTicket().getId(),
                'ticketMessageId': this.message.getId(),
                'ticketAttachmentId': this.id
            }),
            () => {
                this.message.removeAttachment(this.id);
                this.delete();
            }
        );
    }

    public update(data: AttachmentData) {
        let $attachment = $(Templates['@EkynaCommerce/Js/ticket_attachment.html.twig'].render({
            attachment: data,
            trans: trans,
            config: config
        }));

        this.$element.replaceWith($attachment);
        this.$element = $attachment;

        this.init();
    }

    public delete() {
        this.$element.remove();
    }
}

let $support = $('#commerce-support');
if (1 === $support.length) {
    trans = $support.data('trans');
    config = $support.data('config');

    new Support($support).init();
} else {
    console.error('Support root not found.');
}
