define(["jquery","bootstrap"],function(a){"use strict";return a.fn.vatNumberWidget=function(){return this.each(function(){function b(a){k=a,1===h.length&&h.prop("checked",a.valid),d.removeClass("btn-default"),a.valid?d.addClass("btn-success").popover({container:"body",content:a.content,html:!0,placement:"top"}):d.addClass("btn-danger")}var c=a(this),d=c.find('button[type="button"]');if(1===d.length){var e=c.data("config"),f=d.find(".fa"),g=c.find('input[type="text"]'),h=a(e.checkbox),i=null,j=e.lastNumber,k=e.lastResult;k&&b(k),g.on("keyup",function(){k&&g.val()===j?k.valid?d.removeClass("btn-default btn-danger").addClass("btn-success"):d.removeClass(" btn-default btn-success").addClass("btn-danger"):d.removeClass("btn-success btn-danger").addClass("btn-default")}),d.on("click",function(){if(i&&i.abort(),g.val()!==j&&(j=g.val(),k=null,0!==j.length)){try{d.popover("destroy")}catch(c){}f.removeClass("fa-check").addClass("fa-spinner fa-pulse"),i=a.ajax({url:e.path,data:{number:j},method:"GET",dataType:"json"}),i.done(function(a){b(a)}),i.always(function(){f.removeClass("fa-spinner fa-pulse").addClass("fa-check")})}})}}),this},{init:function(a){a.vatNumberWidget()}}});