define(["jquery","ekyna-modal","ekyna-dispatcher","ekyna-ui","jquery/form"],function(a,b,c){"use strict";var d=function(b,d){var e=a(b),f=e.find("view");return 1===f.size()&&(d.replaceWith(a(f.text())),c.trigger("ekyna_commerce.sale_view_response",b),!0)};a(document).on("click",".sale-view [data-sale-modal]",function(c){c.preventDefault();var e=a(this),f=e.closest(".sale-view"),g=new b;return g.load({url:e.attr("href")}),a(g).on("ekyna.modal.response",function(a){"xml"===a.contentType&&d(a.content,f)&&(a.preventDefault(),a.modal.close())}),!1}),a(document).on("click",".sale-view [data-sale-xhr]",function(b){b.preventDefault();var c=a(this),e=c.data("confirm");if(e&&e.length&&!confirm(e))return!1;var f=c.closest(".sale-view").loadingSpinner(),g=c.data("sale-xhr"),h=a.ajax({url:a(this).attr("href"),method:g||"post",dataType:"xml"});return h.done(function(a){d(a,f)}),!1}),a(document).on("click",".sale-view [data-sale-toggle-children]",function(b){b.preventDefault();var c=a(b.currentTarget),d=c.closest(".sale-view"),e=c.data("sale-toggle-children"),f=!!c.data("sale-toggle-shown");if(e){var g=d.find('tr[data-parent="'+e+'"]');f?g.hide():g.show(),c.data("sale-toggle-shown",!f)}return!1}),a(document).on("submit",".sale-view",function(b){b.preventDefault();var c=a(b.target).closest(".sale-view").loadingSpinner();return c.ajaxSubmit({dataType:"xml",success:function(a){d(a,c)}}),!1})});