define(["jquery","routing","ekyna-commerce/form/address"],function(a,b){"use strict";return a.fn.saleAddressWidget=function(){return this.each(function(){var c=a(this),d=c.data("mode"),e=a("#"+c.data("customer-field")),f=c.find(".sale-address-same"),g=c.find(".sale-address-choice"),h=c.find(".sale-address").address();if(1===e.size()&&e.on("change",function(){g.empty().append(a("<option value>Choose</option>")).prop("disabled",!0);var c=a(this).val();if(c){var e=a.get(b.generate("ekyna_commerce_customer_address_admin_choice_list",{customerId:c}));e.done(function(b){var c=h.address("isEmpty");if("undefined"!=typeof b.choices){for(var e in b.choices)if(b.choices.hasOwnProperty(e)){var i=b.choices[e];if(g.append(a("<option />").attr("value",i.id).text(i.text).data("address",i)),!c)continue;"invoice"===d&&1===i.invoice_default?h.address("set",i):"delivery"===d&&1===i.delivery_default&&(1===i.invoice_default?(1===f.size()&&f.prop("checked",!0).trigger("change"),h.address("clear")):(1===f.size()&&f.prop("checked",!1).trigger("change"),h.address("set",i)))}g.prop("disabled",!1)}g.trigger("form_choices_loaded",b)})}}),g.on("change",function(){var a=g.val();if(a){var b=g.find("option[value="+g.val()+"]");if(0!==b.size()){var c=b.data("address");c&&c.hasOwnProperty("id")&&h.address("set",c)}}}),1===f.size()){var i=c.find(".sale-address-wrap"),j=function(){f.prop("checked")?i.slideUp(function(){h.address("clear"),g.size()&&g.val(null).trigger("change")}):i.slideDown()};f.on("change",j),j()}}),this},{init:function(a){a.saleAddressWidget()}}});