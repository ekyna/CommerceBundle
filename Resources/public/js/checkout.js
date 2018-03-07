define(["jquery","ekyna-modal","ekyna-dispatcher","jquery/form"],function(a,b,c){"use strict";function d(){if(k)return!1;k=!0,f.loadingSpinner();var b=a.ajax({url:f.data("refresh-url"),dataType:"xml",cache:!1});return b.done(function(a){m(a),f.loadingSpinner("off")}),b.fail(function(){console.log("Failed to update cart checkout content.")}),b.always(function(){k=!1}),!0}var e={information:".cart-checkout-information","invoice-address":".cart-checkout-invoice-address","delivery-address":".cart-checkout-delivery-address",comment:".cart-checkout-comment",attachments:".cart-checkout-attachments"},f=a(".cart-checkout"),g=f.find(".cart-checkout-customer"),h=f.find(".cart-checkout-forms"),i=f.find(".cart-checkout-submit"),j=f.find(".cart-checkout-quote"),k=!1,l=function(b){var c=a(b).find("view");1===c.size()&&(1===parseInt(c.attr("empty"))?(h.slideUp(),g.slideUp(),i.slideUp()):(h.show().slideDown(),1===parseInt(c.attr("customer"))?g.slideUp():(g.show().slideDown(),1===parseInt(c.attr("user"))?(g.find(".no-user-case").hide(),g.find(".user-case").show()):(g.find(".no-user-case").show(),g.find(".user-case").hide())),1===parseInt(c.attr("quote"))?j.show():j.hide(),1===parseInt(c.attr("valid"))?i.show().slideDown():i.slideUp()))},m=function(b){var c=a(b);for(var d in e)if(e.hasOwnProperty(d)){var f=c.find(d);1===f.size()&&a(e[d]).html(f.text())}l(b);var g=c.find("view");return 1===g.size()&&(a(".sale-view").replaceWith(a(g.text())),!0)};if(c.on("ekyna_commerce.sale_view_response",function(a){l(a)}),c.on("ekyna_user.authentication",function(){d()}),a(document).on("click",".cart-checkout [data-cart-modal]",function(c){c.preventDefault();var d=a(this),e=new b;return e.load({url:d.attr("href")}),a(e).on("ekyna.modal.response",function(a){"xml"===a.contentType&&m(a.content)&&(a.preventDefault(),a.modal.close())}),!1}),a(document).on("click",".cart-checkout [data-cart-xhr]",function(b){b.preventDefault();var c=a(this),d=c.data("confirm");if(d&&d.length&&!confirm(d))return!1;var e=a.ajax({url:a(this).attr("href"),method:"post",dataType:"xml"});return e.done(function(a){m(a)}),!1}),!a("html").data("debug")){var n=!1;a(window).on("focus",function(){!n&&d()&&(n=!0,setTimeout(function(){n=!1},1e4))})}});