define(["jquery","ekyna-modal","ekyna-dispatcher","ekyna-ui","jquery/form","bootstrap"],function(a,b,c){"use strict";function d(){a('.sale-view [data-toggle="popover"]').popover({trigger:"hover",placement:"top",html:!0})}function e(b,e){var f=a(b),g=f.find("view");return 1===g.size()&&(e.replaceWith(a(g.text())),c.trigger("ekyna_commerce.sale_view_response",b),d(),!0)}a(document).on("click",".sale-view [data-sale-modal]",function(c){c.preventDefault();var d=a(this),f=d.closest(".sale-view"),g=new b;return g.load({url:d.attr("href")}),a(g).on("ekyna.modal.response",function(a){"xml"===a.contentType&&e(a.content,f)&&(a.preventDefault(),a.modal.close())}),!1}),a(document).on("click",".sale-view [data-sale-xhr]",function(b){b.preventDefault();var c=a(this),d=c.data("confirm");if(d&&d.length&&!confirm(d))return!1;var f=c.closest(".sale-view"),g=c.data("sale-xhr");f.loadingSpinner();var h=a.ajax({url:a(this).attr("href"),method:g||"post",dataType:"xml"});return h.done(function(a){e(a,f)}),!1}),a(document).on("click",".sale-view [data-sale-toggle-all-children]",function(b){b.stopPropagation(),b.preventDefault();var c=a(b.currentTarget),d=c.closest(".sale-view"),e=d.find("tr[data-parent]"),f=e.filter(":not(:visible)").length,g=e.filter(":visible").length;return f>g?e.show():e.hide(),!1}),a(document).on("click",".sale-view [data-sale-toggle-children]",function(b){function c(b){b.each(function(){a(this).hide().find("[data-sale-toggle-children]").each(function(){var b=a(this),d=b.data("sale-toggle-children"),f=!!b.data("sale-toggle-shown");d&&f&&(c(e.find('tr[data-parent="'+d+'"]')),b.data("sale-toggle-shown",!1))})})}b.stopPropagation(),b.preventDefault();var d=a(b.currentTarget),e=d.closest(".sale-view"),f=d.data("sale-toggle-children"),g=!!d.data("sale-toggle-shown");if(f){var h=e.find('tr[data-parent="'+f+'"]');g?c(h):h.show(),d.data("sale-toggle-shown",!g)}return!1}),a(document).on("submit",".sale-view",function(b){b.preventDefault();var c=a(b.target).closest(".sale-view").loadingSpinner();return c.ajaxSubmit({dataType:"xml",success:function(a){e(a,c)}}),!1}),d()});