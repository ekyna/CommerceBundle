define(["jquery","ekyna-form/collection"],function(a){"use strict";return a.fn.saleShipmentWidget=function(){return this.each(function(){function b(){i.val()?j.removeClass("has-error"):j.addClass("has-error")}function c(){var a=g.find('option[value="'+g.val()+'"]'),c=!1;1===a.length&&(c=a.data("mobile")),c?(j.slideDown(k),i.on("keyup",b),b()):(j.slideUp(k,function(){j.removeClass("has-error")}),i.off("keyup",b))}function d(){var a=g.find('option[value="'+g.val()+'"]');h.val(a.data("price"))}function e(){d(),c()}var f=a(this),g=f.find("select.sale-shipment-method"),h=f.find("input.sale-shipment-amount"),i=a(".address-mobile input.number"),j=i.closest(".form-group"),k=150;g.on("change",e),f.on("click",".sale-shipment-amount-apply",d).on("click",".sale-shipment-amount-clear",function(){h.val("")}),c()}),this},{init:function(a){a.saleShipmentWidget()}}});