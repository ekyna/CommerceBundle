define(["jquery"],function(e){"use strict";var n=e("button.cart-checkout-submit"),i=e('input[name="shipment[shipmentMethod]"]'),a=e("div.submit-prevented"),o=e(".address-mobile input.number"),t=o.closest(".form-group"),r=150;function d(){o.val()?t.removeClass("has-error"):t.addClass("has-error")}function s(){var e,s;i.filter(":checked").val()?(n.removeClass("disabled"),a.slideUp(r)):n.addClass("disabled"),0!==t.length&&(s=!1,(s=1===(e=i.filter(":checked").eq(0)).length?e.data("mobile"):s)?(t.slideDown(r),o.on("keyup",d),d()):(t.slideUp(r,function(){t.removeClass("has-error")}),o.off("keyup",d)))}i.on("change",s),s(),n.on("click",function(){n.hasClass("disabled")&&a.slideDown(r)})});