define(["require","exports","jquery","routing","ekyna-commerce/templates","underscore","ekyna-dispatcher","ekyna-form","ekyna-ui","bootstrap","ekyna-modal"],function(a,b,c,d,e,f,g,h,i,j,k){"use strict";function l(a,b){var c={type:k.prototype.getContentType(b),data:a,jqXHR:b,success:"1"==b.getResponseHeader("X-Commerce-Success")};return g.trigger("ekyna_commerce.add_to_cart",c),c}function m(){c(document).on("click",'a[data-add-to-cart]:not([data-add-to-cart=""])',function(a){if(a.ctrlKey||a.shiftKey||2===a.button)return!0;a.preventDefault(),a.stopPropagation();var b=new k;return b.load({url:c(a.currentTarget).data("add-to-cart"),method:"GET"}),c(b).on("ekyna.modal.response",function(a){l(a.data,a.jqXHR)}),!1}).on("submit",'form[data-add-to-cart]:not([data-add-to-cart=""])',function(a){var b=c(a.currentTarget).closest("form");return a.preventDefault(),a.stopPropagation(),b.loadingSpinner("on"),b.ajaxSubmit({url:b.data("add-to-cart"),success:function(a,d,e){var f=k.prototype.getContentType(e);if("xml"===f){var g=c(a),i=g.find("content");if(1===i.length&&(i=c(i.text()),i.is("form"))){b.data("form").destroy(),b.replaceWith(i),b=i;var j=h.create(b);return void j.init()}}l(a,e);var m=new k;m.handleResponse(a,d,e),c(m).on("ekyna.modal.response",function(a){l(a.data,a.jqXHR)})},complete:function(){b.loadingSpinner("off")}}),!1})}Object.defineProperty(b,"__esModule",{value:!0});b.init=m;var n=function(){function a(a){if(this.config=f.defaults(a,{tag:"li",icon:"> a > span",button:"> a.dropdown-toggle",dropdown:"> div.dropdown-menu",widget_template:e["widget.html.twig"],debug:!1}),this.$element=c(this.config.selector),1!=this.$element.length)throw"Widget not found ! ("+this.config.selector+")";this.dropdownShowHandler=f.bind(this.onDropdownShow,this),this.config.debug||c(window).on("focus",f.bind(this.onWindowFocus,this)),this.defaultData={tag:this.$element.prop("tagName").toLowerCase(),"class":this.$element.attr("class"),icon:null};var b=this.$element.find(this.config.icon);1===b.length&&(this.defaultData.icon=b.attr("class")),this.initialize()}return a.prototype.reload=function(){var a=this;if(!this.busy){this.busy=!0;var b=c.ajax({url:d.generate(this.config.widget_route),method:"GET",dataType:"json",cache:!1});b.done(function(b){a.renderWidget(b),a.config.event&&g.trigger(a.config.event,b)}),b.fail(function(){console.log("Failed to reload widget.")}),b.always(function(){a.busy=!1})}},a.prototype.initialize=function(){if(this.$button=this.$element.find(this.config.button),1!=this.$button.length)throw"Widget toggle button not found ! ("+this.config.button+")";if(this.$dropdown=this.$element.find(this.config.dropdown),1!=this.$dropdown.length)throw"Widget content not found ! ("+this.config.dropdown+")";this.$element.on("show.bs.dropdown",this.dropdownShowHandler)},a.prototype.renderWidget=function(a){var b=c(this.config.widget_template.render(f.defaults(a,this.defaultData)));this.$element.replaceWith(b),this.$element=b,this.initialize()},a.prototype.loadDropdown=function(){var a=this;if(!this.busy){this.busy=!0,this.$dropdown.loadingSpinner("on");var b=c.ajax({url:d.generate(this.config.dropdown_route),method:"GET",dataType:"html",cache:!1});b.done(function(b){a.$dropdown.html(b)}),b.fail(function(){console.log("Failed to load widget dropdown.")}),b.always(function(){a.$dropdown.loadingSpinner("off"),a.busy=!1})}},a.prototype.onDropdownShow=function(){this.$dropdown.is(":empty")&&this.loadDropdown()},a.prototype.onWindowFocus=function(){var a=this;this.busy||this.preventReload||(this.preventReload=!0,setTimeout(function(){a.preventReload=!1},1e4),this.reload())},a}();b.Widget=n});