define(["jquery","ekyna-dispatcher","ekyna-modal","ekyna-commerce/templates","ekyna-ui"],function(a,b,c,d){var e=function(d){this.$element=a("#"+d),this.prefix=this.$element.data("prefix"),this.$tbody=this.$element.find("> table > tbody");var e=this;this.$element.on("click","[data-stock-unit-modal]",function(d){d.preventDefault(),d.stopPropagation();var f=new c,g=a(d.currentTarget),h=e.$element.find("tr#"+g.data("rel"));return f.load({url:a(d.currentTarget).attr("href"),method:"GET"}),a(f).on("ekyna.modal.response",function(a){"json"===a.contentType&&(a.preventDefault(),e.render(h,a.content),a.modal.close(),b.trigger("ekyna_commerce.stock_units.change"))}),!1})};return e.prototype.render=function(a,b){var c=a.attr("id"),e=d["stock_unit_rows.html.twig"].render({prefix:this.prefix,stock_units:b.stock_units});this.$tbody.html(e),this.$tbody.find("tr#"+c+"_adjustments").show()},e});