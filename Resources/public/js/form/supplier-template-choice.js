define(["jquery","routing","tinymce"],function(a,b){"use strict";if("undefined"==typeof tinymce)throw"Tinymce is not available.";return a.fn.supplierOrderTemplateWidget=function(){this.each(function(){var c,d=a(this),e=d.closest("form"),f=d.find(".template-choice"),g=d.find(".locale-choice"),h=e.find(".notify-subject"),i=e.find(".notify-message"),j=i.attr("id");return d.on("change","select",function(){a.getJSON(b.generate("ekyna_commerce_supplier_order_admin_template",{supplierOrderId:d.data("order-id"),id:f.val(),_locale:g.val()}),function(a){a.hasOwnProperty("subject")&&h.val(a.subject),a.hasOwnProperty("message")&&(c=tinymce.get(j),c?c.setContent(a.message):i.val(a.message))})}),this})},{init:function(a){a.supplierOrderTemplateWidget()}}});