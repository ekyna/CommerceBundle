define(["jquery","ekyna-form/collection"],function(u){"use strict";return u.fn.saleShipmentWidget=function(){return this.each(function(){var n=u(this),i=n.find("select.sale-shipment-method"),e=n.find("input.sale-shipment-amount"),t=u(".address-mobile input.number"),o=t.closest(".form-group"),a=150;function s(){t.val()?o.removeClass("has-error"):o.addClass("has-error")}function l(){var n=i.find('option[value="'+i.val()+'"]'),e=!1;(e=1===n.length?n.data("mobile"):e)?(o.slideDown(a),t.on("keyup",s),s()):(o.slideUp(a,function(){o.removeClass("has-error")}),t.off("keyup",s))}function r(){var n=i.find('option[value="'+i.val()+'"]');e.val(n.data("price"))}i.on("change",function(){r(),l()}),n.on("click",".sale-shipment-amount-apply",r).on("click",".sale-shipment-amount-clear",function(){e.val("0.00000")}),l()}),this},{init:function(n){n.saleShipmentWidget()}}});