define(["jquery","ekyna-polyfill","bootstrap"],function(a){"use strict";function b(b){this.$element=b,this.$element.data("moneyWidget",this),this.$base=this.$element.find(".commerce-money-base"),this.$quote=this.$element.find(".commerce-money-quote"),0!==this.$base.size()&&0!==this.$quote.size()&&(this.config=this.$element.data("config"),this.options={minimumFractionDigits:this.config.scale,useGrouping:!1},this.$base.on("change keyup",a.proxy(this.onBaseChange,this)),this.$quote.on("change keyup",a.proxy(this.onQuoteChange,this)),this.onBaseChange(),this.$element.tooltip())}return b.prototype.onBaseChange=function(){var a=parseFloat(this.$base.val().replace(" ","").replace(",","."));isNaN(a)&&(a=0),this.$quote.val((a*this.config.rate).localizedNumber(null,this.options))},b.prototype.onQuoteChange=function(){var a=parseFloat(this.$quote.val().replace(" ","").replace(",","."));isNaN(a)&&(a=0),this.$base.val((a/this.config.rate).localizedNumber(null,this.options))},{init:function(c){c.each(function(){void 0===a(this).data("moneyWidget")&&new b(a(this))})}}});