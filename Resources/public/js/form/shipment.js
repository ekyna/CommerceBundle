define(["jquery","ekyna-form/collection"],function(a){"use strict";return a.fn.shipmentWidget=function(){return this.each(function(){var b=a(this),c=b.attr("name"),d=b.find(".shipment-items > tbody > tr input"),e=b.find('[name="'+c+'[method]"]'),f=b.find("#shipment-parcels"),g=b.find("#toggle-general"),h=b.find("#toggle-relay-point"),i=b.find("#toggle-quantities"),j=function(){var a=0,b=0,c=e.find('option[value="'+e.val()+'"]');1===c.length&&(a=c.data("parcel"),b=c.data("relay")),a?f.slideDown():f.slideUp(function(){f.find(".ekyna-collection-child-container").empty()}),b?h.show():(h.hide(),g.trigger("click"))};e.on("change",j),j(),d.on("blur",function(){var b=a(this),c=parseInt(b.val());isNaN(c)&&b.val(0).trigger("change")}).on("change keyup",function(){var b=a(this),c=parseInt(b.val()),e=d.find('[data-parent="'+b.attr("id")+'"]');isNaN(c)&&(c=0),c>b.data("max")?b.closest("tr").addClass("has-error danger"):b.closest("tr").removeClass("has-error danger"),e.each(function(){var b=a(this);b.val(c*b.data("quantity")).trigger("change")})}).not(":disabled").trigger("change"),i.on("click",function(){var b=0,c=0;d.each(function(d,e){var f=a(e);b+=parseFloat(f.val()),c+=f.data("max")}),b/c>.5?d.not(":disabled").each(function(b,c){a(c).val(0)}).trigger("change"):d.not(":disabled").each(function(b,c){var d=a(c);d.val(d.data("max"))}).trigger("change")})}),this},{init:function(a){a.shipmentWidget()}}});