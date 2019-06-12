define(["jquery","ekyna-commerce/templates","ekyna-modal","ekyna-ui"],function(a,b,c){function d(a){this.$element=a,this.$element.data("customerBalance",this),this.$form=this.$element.find("form"),this.$from=this.$form.find('input[name="balance[from]"]'),this.$to=this.$form.find('input[name="balance[to]"]'),this.$filter=this.$form.find('select[name="balance[filter]"]'),this.$submit=this.$form.find('button[name="balance[submit]"]'),this.init()}return d.prototype.init=function(){this.bindEvents(),this.onFilterChange()},d.prototype.bindEvents=function(){this.$filter.on("change",a.proxy(this.onFilterChange,this)),this.$submit.on("click",a.proxy(this.onSubmit,this))},d.prototype.unbindEvents=function(){this.$filter.off("change"),this.$submit.off("click")},d.prototype.onFilterChange=function(){var a="all"!==this.$filter.val();this.$from.prop("disabled",a),this.$to.prop("disabled",a)},d.prototype.onSubmit=function(a){a.preventDefault(),a.stopPropagation();var c=this.$element;return this.$element.loadingSpinner(),this.$form.ajaxSubmit({dataType:"json",success:function(a){var d=b["@EkynaCommerce/Js/customer_balance_rows.html.twig"].render({balance:a});c.find("table > tbody").html(d)},complete:function(){c.loadingSpinner("off")}}),!1},a.fn.customerBalance=function(){return this.each(function(){void 0===a(this).data("customerBalance")&&new d(a(this))})},a(".customer-balance").customerBalance(),d});