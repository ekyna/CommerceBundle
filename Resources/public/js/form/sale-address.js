define(["jquery","routing","ekyna-commerce/form/address"],function(t,o){"use strict";return t.fn.saleAddressWidget=function(){return this.each(function(){var e,d=t(this),s=d.data("mode"),a=t("#"+d.data("customer-field")),n=d.find(".sale-address-same"),i=d.find(".sale-address-choice"),r=d.find(".sale-address").address();1===a.length&&a.on("change",function(){i.empty().append(t("<option value>Choose</option>")).prop("disabled",!0);var e=t(this).val();e&&t.get(o.generate("admin_ekyna_commerce_customer_address_choice_list",{customerId:e})).done(function(e){var d=r.address("isEmpty");if(void 0!==e.choices){for(var a in e.choices)e.choices.hasOwnProperty(a)&&(a=e.choices[a],i.append(t("<option />").attr("value",a.id).text(a.text).data("address",a)),d&&("invoice"===s&&1===a.invoice_default?r.address("set",a):"delivery"===s&&1===a.delivery_default&&(1===a.invoice_default?(1===n.length&&n.prop("checked",!0).trigger("change"),r.address("clear")):(1===n.length&&n.prop("checked",!1).trigger("change"),r.address("set",a)))));i.prop("disabled",!1)}i.trigger("form_choices_loaded",e)})}),i.on("change",function(){var e;i.val()&&(0===(e=i.find("option[value="+i.val()+"]")).length||(e=e.data("address"))&&e.hasOwnProperty("id")&&r.address("set",e))}),1===n.length&&(e=d.find(".sale-address-wrap"),n.on("change",a=function(){n.prop("checked")?e.slideUp(function(){r.address("clear"),i.length&&i.val(null).trigger("change")}):e.slideDown()}),a())}),this},{init:function(e){e.saleAddressWidget()}}});