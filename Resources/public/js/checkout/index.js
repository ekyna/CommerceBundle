define(["jquery","ekyna-modal","ekyna-dispatcher","ekyna-ui","jquery/form"],function(a,b,c){"use strict";function d(b){var c=a(b);for(var d in f)if(f.hasOwnProperty(d)){var e=c.find(d);1===e.size()&&a(f[d]).html(e.text())}o(b);var g=c.find("view");return 1===g.size()&&(a(".cart-checkout-view").html(a(g.text())),!0)}function e(){if(n)return!1;n=!0,h.loadingSpinner();var b=a.ajax({url:h.data("refresh-url"),dataType:"xml",cache:!1});return b.done(function(a){d(a),h.loadingSpinner("off")}),b.fail(function(){console.log("Failed to update cart checkout content.")}),b.always(function(){n=!1}),!0}var f={information:"#cart-checkout-information",invoice:"#cart-checkout-invoice",delivery:"#cart-checkout-delivery",comment:"#cart-checkout-comment",attachments:"#cart-checkout-attachments",content:"#cart-checkout-content"},g={information:"#cart-checkout-information-button",invoice:"#cart-checkout-invoice-button",delivery:"#cart-checkout-delivery-button",comment:"#cart-checkout-comment-button",attachments:"#cart-checkout-attachments-button"},h=a(".cart-checkout"),i=h.find(".cart-checkout-customer"),j=h.find(".cart-checkout-forms"),k=h.find(".cart-checkout-submit"),l=h.find(".cart-checkout-quote"),m=h.find(".submit-prevented"),n=!1,o=function(b){m.clearQueue().slideUp(function(){m.find(".alert-danger").hide().find("p").empty(),m.find(".alert-warning").show()}),h.find("cart-checkout-button").removeClass("btn-primary").addClass("btn-default disabled");var c=a(b).find("view");if(1===c.length){var d=c.data("controls");if(1===d.empty)j.slideUp(),i.slideUp(),k.addClass("disabled").hide(),l.addClass("disabled").hide();else{j.show().slideDown(),k.show();for(var e in g)d.hasOwnProperty(e)&&1===d[e]&&a(g[e]).removeClass("btn-default disabled").addClass("btn-primary");1===d.customer?i.slideUp():(i.show().slideDown(),1===d.user?(i.find(".no-user-case").hide(),i.find(".user-case").show()):(i.find(".no-user-case").show(),i.find(".user-case").hide())),1===d.quote?l.show():l.hide(),1===d.valid?(k.removeClass("disabled"),l.removeClass("disabled")):(k.addClass("disabled"),l.addClass("disabled"))}}};if(c.on("ekyna_commerce.sale_view_response",function(a){o(a)}),c.on("ekyna_user.authentication",function(){e()}),h.on("click",".cart-checkout-footer a.btn",function(b){if(b.stopPropagation(),a(b.target).closest("a.btn").hasClass("disabled"))return b.preventDefault(),m.clearQueue().slideDown(),!1}),a(document).on("click",".cart-checkout [data-cart-modal]",function(c){c.preventDefault();var e=a(this),f=new b;return f.load({url:e.attr("href")}),a(f).on("ekyna.modal.response",function(a){"xml"===a.contentType&&d(a.content)&&(a.preventDefault(),a.modal.close())}),!1}),a(document).on("click",".cart-checkout [data-cart-xhr]",function(b){b.preventDefault();var c=a(this),e=c.data("confirm");if(e&&e.length&&!confirm(e))return!1;var f=a.ajax({url:a(this).attr("href"),method:"post",dataType:"xml"});return f.done(function(a){d(a)}),!1}),!a("html").data("debug")){var p=!1;a(window).on("focus",function(){!p&&e()&&(p=!0,setTimeout(function(){p=!1},1e4))})}});