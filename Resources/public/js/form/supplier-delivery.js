define(["jquery","routing"],function(a,b){"use strict";return a.fn.supplierDeliveryWidget=function(){return this.each(function(){a(this).on("click","a.print-label",function(c){var d=a(c.currentTarget),e=parseInt(d.data("order-id")),f=parseInt(d.data("item-id"));if(!e||!f)return console.log("Undefined order or item id."),!1;var g=b.generate("admin_ekyna_commerce_supplier_order_label",{supplierOrderId:e,id:[f],geocode:d.closest("tr").find("input.geocode").val()}),h=window.open(g,"_blank");h.focus()})}),this},{init:function(a){a.supplierDeliveryWidget()}}});