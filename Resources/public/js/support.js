define(["require","exports","jquery","routing","ekyna-commerce/templates","ekyna-spinner","bootstrap","ekyna-modal"],function(u,d,s,e,i,m,p,f){"use strict";Object.defineProperty(d,"__esModule",{value:!0});t.prototype.init=function(){var i=this;return this.$element.find(".ticket").each(function(t,e){i.tickets.push(new k(i,s(e)).init())}),this.$element.on("click",".ticket-new",function(t){i.request(s(t.currentTarget).data("url"))}),this.tickets.length&&this.tickets[0].show(),this},t.prototype.findTicket=function(e){var i=null;return this.tickets.forEach(function(t){if(t.getId()===e)return i=t,!1}),i},t.prototype.request=function(t,e){var i=this,n=new f;n.load({url:t}),s(n).on("ekyna.modal.response",function(t){"json"===t.contentType&&(i.parse(t.content),e&&e(),n.close())})},t.prototype.createTicket=function(t){t=s(i["@EkynaCommerce/Js/ticket.html.twig"].render({ticket:t,trans:n,config:o}));this.$element.find(".tickets").prepend(t),this.tickets.forEach(function(t){t.hide()}),this.tickets.push(new k(this,t).init().show()),this.$element.find(".no-tickets").hide()},t.prototype.removeTicket=function(i){var n;this.tickets.forEach(function(t,e){if(t.getId()===i)return t.delete(),n=e,!1}),n&&this.tickets.splice(n,1)},t.prototype.parse=function(t){var e;if(t.ticket&&((i=this.findTicket(t.ticket.id))?i.update(t.ticket):this.createTicket(t.ticket)),t.ticketMessage){if(!(i=this.findTicket(t.ticketMessage.ticket)))throw"Ticket not found.";(e=i.findMessage(t.ticketMessage.id))?e.update(t.ticketMessage):i.createMessage(t.ticketMessage)}if(t.ticketAttachment){if(!(i=this.findTicket(t.ticketAttachment.ticket)))throw"Ticket not found.";if(!(e=i.findMessage(t.ticketAttachment.message)))throw"Message not found.";var i=e.findAttachment(t.ticketAttachment.id);i?i.update(t.ticketAttachment):e.createAttachment(t.ticketAttachment)}};var n,o,d=t;function t(t){this.$element=t,this.tickets=[],t.data("support",this)}r.prototype.init=function(){var i=this;return this.$element.find(".message").each(function(t,e){i.messages.push(new g(i,s(e)).init())}),this.$element.on("click",".message-new",function(){return i.newMessage()}),this.$element.on("click",".ticket-edit",function(){return i.edit()}),this.$element.on("click",".ticket-remove",function(){return i.remove()}),this.$element.on("click",".ticket-close",function(){return i.close()}),this.$element.on("click",".ticket-open",function(){return i.open()}),this.$element.on("click",".customer-show",function(t){return r.showCustomer(t)}),this.$element.on("click",".order-show",function(t){return r.showOrder(t)}),this.$element.on("click",".quote-show",function(t){return r.showQuote(t)}),this},r.showCustomer=function(t){if(t.preventDefault(),t.stopPropagation(),o.admin)return window.open(e.generate(o.routes.customer+"_read",{customerId:s(t.target).data("id")}),"_blank").focus(),!1},r.showOrder=function(t){return t.preventDefault(),t.stopPropagation(),t=o.admin?{orderId:s(t.target).data("id")}:{number:s(t.target).text()},window.open(e.generate(o.routes.order+"_read",t),"_blank").focus(),!1},r.showQuote=function(t){return t.preventDefault(),t.stopPropagation(),t=o.admin?{quoteId:s(t.target).data("id")}:{number:s(t.target).text()},window.open(e.generate(o.routes.quote+"_read",t),"_blank").focus(),!1},r.prototype.getId=function(){return this.id},r.prototype.getSupport=function(){return this.support},r.prototype.newMessage=function(){this.support.request(e.generate(o.routes.message+"_create",{ticketId:this.id}))},r.prototype.edit=function(){this.support.request(e.generate(o.routes.ticket+"_update",{ticketId:this.id}))},r.prototype.remove=function(){var t=this;this.support.request(e.generate(o.routes.ticket+"_delete",{ticketId:this.id}),function(){t.support.removeTicket(t.id),t.delete()})},r.prototype.open=function(){this.support.request(e.generate(o.routes.ticket+"_open",{ticketId:this.id}))},r.prototype.close=function(){this.support.request(e.generate(o.routes.ticket+"_close",{ticketId:this.id}))},r.prototype.findMessage=function(e){var i=null;return this.messages.forEach(function(t){if(t.getId()===e)return i=t,!1}),i},r.prototype.createMessage=function(t){t=s(i["@EkynaCommerce/Js/ticket_message.html.twig"].render({message:t,trans:n,config:o}));this.$element.find(".messages").append(t),this.messages.push(new g(this,t).init())},r.prototype.removeMessage=function(i){var n;this.messages.forEach(function(t,e){if(t.getId()===i)return t.delete(),n=e,!1}),n&&this.messages.splice(n,1)},r.prototype.update=function(t){var e;if(t.expanded=!0,void 0!==t.messages)return e=s(i["@EkynaCommerce/Js/ticket.html.twig"].render({ticket:t,trans:n,config:o})),this.$element.replaceWith(e),this.$element=e,void this.init();this.$element.find(".ticket-header").html(i["@EkynaCommerce/Js/ticket_header.html.twig"].render({ticket:t,trans:n,config:o})),this.$element.find(".ticket-body").html(i["@EkynaCommerce/Js/ticket_body.html.twig"].render({ticket:t,trans:n,config:o})),this.$element.find(".ticket-footer").html(i["@EkynaCommerce/Js/ticket_footer.html.twig"].render({ticket:t,trans:n,config:o}))},r.prototype.delete=function(){this.$element.remove()},r.prototype.show=function(){return this.$element.find(".collapse").collapse("show"),this},r.prototype.hide=function(){return this.$element.find(".collapse").collapse("hide"),this};var k=r;function r(t,e){this.id=e.data("id"),this.support=t,this.$element=e,this.messages=[],e.data("ticket",this)}c.prototype.init=function(){var i=this;return this.attachments=[],this.$element.find(".attachment").each(function(t,e){i.attachments.push(new l(i,s(e)).init())}),this.$element.on("click",".attachment-new",function(){return i.newAttachment()}),this.$element.on("click",".message-edit",function(){return i.edit()}),this.$element.on("click",".message-remove",function(){return i.remove()}),this},c.prototype.getId=function(){return this.id},c.prototype.getTicket=function(){return this.ticket},c.prototype.newAttachment=function(){this.ticket.getSupport().request(e.generate(o.routes.attachment+"_create",{ticketId:this.ticket.getId(),ticketMessageId:this.id}))},c.prototype.edit=function(){this.ticket.getSupport().request(e.generate(o.routes.message+"_update",{ticketId:this.ticket.getId(),ticketMessageId:this.id}))},c.prototype.remove=function(){var t=this;this.ticket.getSupport().request(e.generate(o.routes.message+"_delete",{ticketId:this.ticket.getId(),ticketMessageId:this.id}),function(){t.ticket.removeMessage(t.id),t.delete()})},c.prototype.findAttachment=function(e){var i=null;return this.attachments.forEach(function(t){if(t.getId()===e)return i=t,!1}),i},c.prototype.createAttachment=function(t){t=s(i["@EkynaCommerce/Js/ticket_attachment.html.twig"].render({attachment:t,trans:n,config:o}));this.$element.find(".attachments").append(t),this.attachments.push(new l(this,t).init())},c.prototype.removeAttachment=function(i){var n;this.attachments.forEach(function(t,e){if(t.getId()===i)return t.delete(),n=e,!1}),n&&this.attachments.splice(n,1)},c.prototype.update=function(t){t=s(i["@EkynaCommerce/Js/ticket_message.html.twig"].render({message:t,trans:n,config:o}));this.$element.replaceWith(t),this.$element=t,this.init()},c.prototype.delete=function(){this.$element.remove()};var g=c;function c(t,e){this.id=e.data("id"),this.ticket=t,(this.$element=e).data("message",this)}a.prototype.init=function(){var t=this;return this.$element.on("click",".attachment-download",function(){return t.download()}),this.$element.on("click",".attachment-edit",function(){return t.edit()}),this.$element.on("click",".attachment-remove",function(){return t.remove()}),this},a.prototype.getId=function(){return this.id},a.prototype.download=function(){window.open(e.generate(o.routes.attachment+"_download",{ticketId:this.message.getTicket().getId(),ticketMessageId:this.message.getId(),ticketAttachmentId:this.id}),"_blank").focus()},a.prototype.edit=function(){this.message.getTicket().getSupport().request(e.generate(o.routes.attachment+"_update",{ticketId:this.message.getTicket().getId(),ticketMessageId:this.message.getId(),ticketAttachmentId:this.id}))},a.prototype.remove=function(){var t=this;this.message.getTicket().getSupport().request(e.generate(o.routes.attachment+"_delete",{ticketId:this.message.getTicket().getId(),ticketMessageId:this.message.getId(),ticketAttachmentId:this.id}),function(){t.message.removeAttachment(t.id),t.delete()})},a.prototype.update=function(t){t=s(i["@EkynaCommerce/Js/ticket_attachment.html.twig"].render({attachment:t,trans:n,config:o}));this.$element.replaceWith(t),this.$element=t,this.init()},a.prototype.delete=function(){this.$element.remove()};var l=a;function a(t,e){this.id=e.data("id"),this.message=t,(this.$element=e).data("attachment",this)}var h=s("#commerce-support");1===h.length?(n=h.data("trans"),o=h.data("config"),new d(h).init()):console.error("Support root not found.")});