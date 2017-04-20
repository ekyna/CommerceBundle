define(["jquery","jquery-ui/widget","ekyna-polyfill"],function(a){"use strict";var b=(navigator.language||navigator.browserLanguage).split("-")[0]||"en";return a.widget("ekyna_commerce.priceType",{options:{taxes:null},_create:function(){if(this.config=a.extend({tax_group:".tax-group-choice",rates:[],precision:2},this.element.data("config")),this.$input=this.element.find(".commerce-price-input"),this.$mode=this.element.find(".commerce-price-mode"),this.$rates=this.element.find(".commerce-price-rates"),this.$value=this.element.find(".commerce-price-value"),this.$group=a(this.config.tax_group),1!==this.$input.length||1!==this.$mode.length||1!==this.$value.length)throw"Missing commerce price type fields";1!==this.$group.length||Array.isArray(this.options.taxes)||this._on(this.$group,{change:this._loadRates}),this._on(this.$mode,{change:this._display}),this._on(this.$input,{keyup:this._calculate,blur:this._display}),this._loadRates()},_destroy:function(){1!==this.$group.length||Array.isArray(this.options.taxes)||this._off(this.$group,"change"),this._off(this.$mode,"change"),this._off(this.$input,"keyup blur"),this.rates=void 0,this.$input=void 0,this.$mode=void 0,this.$value=void 0,this.$group=void 0},_setOption:function(a,b){this._super(a,b),"taxes"===a&&this._loadRates()},_loadRates:function(){if(this.rates=[],this.$rates.empty(),Array.isArray(this.options.taxes))this.rates=this.options.taxes;else if(1===this.$group.length){var b=this.$group.val();if(b){var c=this.$group.find("option[value="+this.$group.val()+"]");if(1===c.length){var d=this;a.each(c.data("taxes"),function(a,b){d.rates.push(b.rate)})}}}else this.rates=this.config.rates;0<this.rates.length&&(this.rates=this.rates.map(parseFloat),this.$rates.html("&nbsp;("+this.rates.map(function(a){return 100*a+"%"}).join(",&nbsp;")+")")),this._display()},_display:function(){var c=parseFloat(this.$value.val()),d=0,e=this.config.precision;isNaN(c)?this.$input.val(null):(d=Math.fRound(c,e),this.$mode.is(":checked")&&a.each(this.rates,function(a,b){d+=Math.fRound(c*b,e)}),this.$input.val(d.toLocaleString(b,{minimumFractionDigits:e,useGrouping:!1}))),this._calculate()},_calculate:function(){var b=parseFloat(this.$input.val().replace(",",".").replace(" ","")),c=0;return isNaN(b)?(this.$value.val(null),void(this.$input.prop("required")&&this.element.find(".input-group").addClass("has-error"))):(this.element.find(".input-group").removeClass("has-error"),c=Math.fRound(b,this.config.precision),this.$mode.is(":checked")||(a.each(this.rates,function(a,b){c*=1+b}),c=Math.fRound(c,this.config.precision)),a.each(this.rates,function(a,b){c/=1+b}),c=Math.fRound(c,5),void this.$value.val(c))},save:function(){this._calculate()}}),{init:function(a){a.priceType()},save:function(a){a.data("ekyna_commerce.priceType")&&a.priceType("save")},destroy:function(a){a.data("ekyna_commerce.priceType")&&a.priceType("destroy")}}});