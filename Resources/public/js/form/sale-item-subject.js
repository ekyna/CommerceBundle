define(["jquery","routing","ekyna-ui"],function(a,b){"use strict";return a.fn.saleItemSubjectWidget=function(){return this.each(function(){function c(){d&&d.abort(),h.empty();var c=String(f.val()),e=parseInt(g.val());0<c.length&&0<e&&(h.loadingSpinner("on"),d=a.ajax({url:b.generate("ekyna_commerce_inventory_admin_subject_stock",{provider:c,identifier:e}),method:"GET",dataType:"html"}),d.done(function(a){0<a.length&&h.html(a)}),d.always(function(){h.loadingSpinner("off")}))}var d,e=a(this),f=e.find(".commerce-subject-choice select.provider"),g=e.find(".commerce-subject-choice select.subject"),h=e.find(".sale-item-subject-stock");f.on("change",c),g.on("change",c),c()}),this},{init:function(a){a.saleItemSubjectWidget()}}});