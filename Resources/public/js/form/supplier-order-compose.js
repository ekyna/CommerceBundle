define(["jquery","ekyna-form/collection"],function(a){"use strict";return a.fn.supplierOrderItemWidget=function(){return this.each(function(){var b=a(this),c=b.find("input.order-item-product"),d=b.find("input.order-item-net-price");c.on("change",function(){var a=String(c.val()),d=0<a.length;b.find(".order-item-designation, .order-item-reference").prop("readonly",d)}).trigger("change"),d.on("change keyup",function(){var a=d.closest(".input-group"),b=parseFloat(String(d.val()).replace(",","."))||0;0>=b?a.addClass("has-warning"):a.removeClass("has-warning")}).trigger("change")}),this},a.fn.supplierOrderComposeWidget=function(){return this.each(function(){var b=a(this),c=b.find(".order-compose-items").eq(0),d=b.find(".order-compose-quick-add-select").eq(0),e=b.find(".order-compose-quick-add-button").eq(0),f=["designation","reference","net-price"];c.find(".commerce-supplier-order-item").supplierOrderItemWidget(),e.on("click",function(){var a=d.val(),b=d.find("option[value="+a+"]");c.find('[data-collection-role="add"]').trigger("click");for(var e=c.find(".ekyna-collection-child:last-child"),g=0;g<f.length;g++)e.find(".order-item-"+f[g]).val(b.data(f[g]));e.find(".order-item-product").val(a),e.supplierOrderItemWidget()})}),this},{init:function(a){a.supplierOrderComposeWidget()}}});