define(["jquery","routing","ekyna-commerce/templates"],function(a,b,c){"use strict";function d(d){if(!d.data("summary-xhr")){var e=a.ajax({url:b.generate(f,d.data("summary-parameters")),dataType:"json"});e.done(function(a){d.data("summary",a),d.popover({content:c["sale_summary.html.twig"].render(d.data("summary")),template:'<div class="popover summary" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',container:"body",html:!0,placement:"auto",trigger:"hover",viewport:".ekyna-table"}),d.is(":hover")&&d.popover("show")}),e.always(function(a){d.removeData("summary-xhr")}),d.data("summary-xhr",e)}}if(!("ontouchstart"in window)){var e=a(".ekyna-table > form > div > table"),f=e.data("summary-route");f&&e.on("mouseenter","tbody > tr",function(b){var c=a(b.currentTarget);c.data("summary")||d(c)})}});