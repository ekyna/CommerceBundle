define(["jquery","ekyna-modal","ekyna-ui","jquery/form"],function(a,b){"use strict";var c=function(b,c){var d=a(b),e=d.find("view");return 1==e.size()&&(c.replaceWith(a(e.text())),!0)};a(document).on("click",".sale-view [data-sale-modal]",function(d){d.preventDefault();var e=a(this),f=e.closest(".sale-view"),g=new b;return g.load({url:e.attr("href")}),a(g).on("ekyna.modal.response",function(a){"xml"==a.contentType&&c(a.content,f)&&(a.preventDefault(),g.close())}),!1}),a(document).on("click",".sale-view [data-sale-xhr]",function(b){b.preventDefault();var d=a(this),e=d.data("confirm");if(e&&e.length&&!confirm(e))return!1;var f=d.closest(".sale-view").loadingSpinner(),g=d.data("sale-xhr"),h=a.ajax({url:a(this).attr("href"),method:g||"post",dataType:"xml"});return h.done(function(a){c(a,f)}),!1}),a(document).on("click",".sale-view [data-toggle]",function(b){b.preventDefault();var c=a(this),d=a(c.data("toggle"));return 1==d.size()&&(d.is(":visible")?d.hide():d.show()),!1}),a(document).on("submit",".sale-view",function(b){b.preventDefault();var d=a(b.target).closest(".sale-view").loadingSpinner();return d.ajaxSubmit({dataType:"xml",success:function(a){c(a,d)}}),!1})});