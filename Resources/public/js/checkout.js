define(["jquery","ekyna-modal","ekyna-dispatcher","jquery/form"],function(a,b,c){"use strict";function d(){e&&e.abort(),g.loadingSpinner(),e=a.ajax({url:g.data("refresh-url"),dataType:"xml",cache:!1}),e.done(function(a){f(a),g.loadingSpinner("off")}),e.fail(function(){console.log("Failed to update cart checkout content.")})}var e,f=function(b){var c=a(b),d={information:".cart-checkout-information","invoice-address":".cart-checkout-invoice-address","delivery-address":".cart-checkout-delivery-address"};for(var e in d)if(d.hasOwnProperty(e)){var f=c.find(e);1==f.length&&a(d[e]).html(a(f.text()))}var g=c.find("view");return 1==g.length&&(a(".sale-view").replaceWith(a(g.text())),!0)},g=a(".cart-checkout"),h=a(".cart-sign-in-or-register");c.on("ekyna_user.user_status",function(a){a.authenticated&&(d(),h.slideUp())}),a(document).on("click",".cart-checkout [data-cart-modal]",function(c){c.preventDefault();var d=a(this),e=new b;return e.load({url:d.attr("href")}),a(e).on("ekyna.modal.response",function(a){"xml"==a.contentType&&f(a.content)&&(a.preventDefault(),e.close())}),!1})});