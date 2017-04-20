define(["require","exports","jquery","ekyna-commerce/templates","underscore","ekyna-dispatcher","ekyna-form","ekyna-spinner","bootstrap","ekyna-modal","ekyna-flags"],function(a,b,c,d,e,f,g,h,i,j,k){"use strict";function l(a,b,c){var d={type:j.prototype.getContentType(b),data:a,jqXHR:b,success:"1"==b.getResponseHeader("X-Commerce-Success"),modal:c};return f.trigger("ekyna_commerce.add_to_cart",d),d}function m(a){a=c.extend({debug:!1,customer:{selector:"#customer-widget",event:"ekyna_commerce.customer",template:d["@EkynaCommerce/Js/widget.html.twig"],debug:!1},cart:{selector:"#cart-widget",event:"ekyna_commerce.cart",template:d["@EkynaCommerce/Js/widget.html.twig"],debug:!1},context:{selector:"#context-widget",event:"ekyna_commerce.context",template:d["@EkynaCommerce/Js/widget.html.twig"],debug:!1}},a);var b,e,h;a.customer&&(a.customer.debug=a.debug,b=new n(a.customer)),a.cart&&(a.cart.debug=a.debug,e=new n(a.cart)),a.context&&(a.context.debug=a.debug,h=new n(a.context),k.load()),(b||e||h)&&(f.on("ekyna_user.authentication",function(){b&&b.reload(),e&&e.reload(),h&&h.reload()}),e&&(f.on("ekyna_commerce.sale_view_response",function(){e.reload()}),f.on("ekyna_commerce.add_to_cart",function(a){a.success&&e.reload()}))),c(document).on("click",'a[data-resupply-alert]:not([data-resupply-alert=""])',function(a){if(a.ctrlKey||a.shiftKey||2===a.button)return!0;a.preventDefault(),a.stopPropagation();var b=new j;return b.load({url:c(a.currentTarget).data("resupply-alert"),method:"GET"}),!1}).on("click",'a[data-add-to-cart]:not([data-add-to-cart=""])',function(a){if(a.ctrlKey||a.shiftKey||2===a.button)return!0;a.preventDefault(),a.stopPropagation();var b=new j;return b.load({url:c(a.currentTarget).data("add-to-cart"),method:"GET"}),c(b).on("ekyna.modal.response",function(a){l(a.content,a.jqXHR,a.modal)}),!1}).on("submit",'form[data-add-to-cart]:not([data-add-to-cart=""])',function(a){var b=c(a.currentTarget).closest("form");return a.preventDefault(),a.stopPropagation(),b.loadingSpinner("on"),b.ajaxSubmit({url:b.data("add-to-cart"),success:function(a,d,e){var f=j.prototype.getContentType(e);if("xml"===f){var h=c(a),i=h.find("content");if(1===i.length&&(i=c(i.text()),i.is("form"))){b.data("form").destroy(),b.replaceWith(i),b=i.eq(0);var k=g.create(b);return void k.init()}}l(a,e,null);var m=new j;m.handleResponse(a,d,e),c(m).on("ekyna.modal.response",function(a){l(a.content,a.jqXHR,a.modal)})},complete:function(){b.loadingSpinner("off")}}),!1})}var n=function(){function a(a){if(this.config=e.defaults(a,{tag:"li",icon:"> a > span",button:"> a.dropdown-toggle",dropdown:"> div.dropdown-menu",template:d["@EkynaCommerce/Js/widget.html.twig"],debug:!1}),this.$element=c(this.config.selector),1!=this.$element.length)throw"Widget not found ! ("+this.config.selector+")";this.config.url=this.$element.data("url"),this.dropdownShowHandler=e.bind(this.onDropdownShow,this),this.config.debug||c(window).on("focus",e.bind(this.onWindowFocus,this)),this.defaultData={tag:this.$element.prop("tagName").toLowerCase(),"class":this.$element.attr("class"),icon:null};var b=this.$element.find(this.config.icon);1===b.length&&(this.defaultData.icon=b.attr("class")),this.initialize()}return a.prototype.reload=function(){var a=this;if(!this.busy){this.busy=!0;var b=c.ajax({url:this.config.url.widget,method:"GET",dataType:"json",cache:!1});b.done(function(b){a.renderWidget(b),a.config.event&&f.trigger(a.config.event,b)}),b.fail(function(){console.log("Failed to reload widget.")}),b.always(function(){a.busy=!1})}},a.prototype.initialize=function(){if(this.$button=this.$element.find(this.config.button),1!=this.$button.length)throw"Widget toggle button not found ! ("+this.config.button+")";if(this.$dropdown=this.$element.find(this.config.dropdown),1!=this.$dropdown.length)throw"Widget content not found ! ("+this.config.dropdown+")";this.$element.on("show.bs.dropdown",this.dropdownShowHandler)},a.prototype.renderWidget=function(a){var b=c(this.config.template.render(e.defaults(a,this.defaultData)));this.$element.replaceWith(b),this.$element=b,this.initialize()},a.prototype.loadDropdown=function(){var a=this;if(!this.busy){this.busy=!0,this.$dropdown.loadingSpinner("on");var b=c.ajax({url:this.config.url.dropdown,method:"GET",dataType:"html",data:this.$element.data("data"),cache:!1});b.done(function(b){a.$dropdown.html(b);var c=a.$dropdown.find("form");if(c.length){var d=g.create(c);d.init()}}),b.fail(function(){console.log("Failed to load widget dropdown.")}),b.always(function(){a.$dropdown.loadingSpinner("off"),a.busy=!1})}},a.prototype.onDropdownShow=function(){this.$dropdown.is(":empty")&&this.loadDropdown()},a.prototype.onWindowFocus=function(){var a=this;this.busy||this.preventReload||(this.preventReload=!0,setTimeout(function(){a.preventReload=!1},1e4),this.reload())},a}();return{init:m,Widget:n}});