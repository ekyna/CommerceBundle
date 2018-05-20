define(["jquery","ekyna-modal","ekyna-dispatcher","ekyna-ui","jquery/form"],function(a,b,c){"use strict";function d(b){var c=a(b);for(var d in f)if(f.hasOwnProperty(d)){var e=c.find(d);1===e.size()&&a(f[d]).html(e.text())}n(b);var g=c.find("view");return 1===g.size()&&(a(".sale-view").replaceWith(a(g.text())),!0)}function e(){if(m)return!1;m=!0,g.loadingSpinner();var b=a.ajax({url:g.data("refresh-url"),dataType:"xml",cache:!1});return b.done(function(a){d(a),g.loadingSpinner("off")}),b.fail(function(){console.log("Failed to update cart checkout content.")}),b.always(function(){m=!1}),!0}var f={information:".cart-checkout-information","invoice-address":".cart-checkout-invoice-address","delivery-address":".cart-checkout-delivery-address",comment:".cart-checkout-comment",attachments:".cart-checkout-attachments"},g=a(".cart-checkout"),h=g.find(".cart-checkout-customer"),i=g.find(".cart-checkout-forms"),j=g.find(".cart-checkout-submit"),k=g.find(".cart-checkout-quote"),l=g.find(".submit-prevented"),m=!1,n=function(b){l.slideUp();var c=a(b).find("view");1===c.size()&&(1===parseInt(c.attr("empty"))?(i.slideUp(),h.slideUp(),j.addClass("disabled").hide(),k.addClass("disabled").hide()):(i.show().slideDown(),j.show(),1===parseInt(c.attr("customer"))?h.slideUp():(h.show().slideDown(),1===parseInt(c.attr("user"))?(h.find(".no-user-case").hide(),h.find(".user-case").show()):(h.find(".no-user-case").show(),h.find(".user-case").hide())),1===parseInt(c.attr("quote"))?k.show():k.hide(),1===parseInt(c.attr("valid"))?(j.removeClass("disabled"),k.removeClass("disabled")):(j.addClass("disabled"),k.addClass("disabled"))))};if(c.on("ekyna_commerce.sale_view_response",function(a){n(a)}),c.on("ekyna_user.authentication",function(){e()}),g.on("click",".cart-checkout-footer a.btn",function(b){if(b.stopPropagation(),a(b.target).closest("a.btn").hasClass("disabled"))return b.preventDefault(),l.slideDown(),!1}),a(document).on("click",".cart-checkout [data-cart-modal]",function(c){c.preventDefault();var e=a(this),f=new b;return f.load({url:e.attr("href")}),a(f).on("ekyna.modal.response",function(a){"xml"===a.contentType&&d(a.content)&&(a.preventDefault(),a.modal.close())}),!1}),a(document).on("click",".cart-checkout [data-cart-xhr]",function(b){b.preventDefault();var c=a(this),e=c.data("confirm");if(e&&e.length&&!confirm(e))return!1;var f=a.ajax({url:a(this).attr("href"),method:"post",dataType:"xml"});return f.done(function(a){d(a)}),!1}),!a("html").data("debug")){var o=!1;a(window).on("focus",function(){!o&&e()&&(o=!0,setTimeout(function(){o=!1},1e4))})}});