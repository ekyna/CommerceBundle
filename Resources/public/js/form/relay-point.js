define(["jquery","bootstrap/dialog","ekyna-commerce/templates","routing","ekyna-ui"],function(a,b,c,d){"use strict";return a.fn.requiredInputWidget=function(){return this.each(function(){var b=a(this),c=b.closest(".form-group");b.on("keyup",function(a){0===b.val().trim().length?(c.removeClass("has-success").addClass("has-error"),a.preventDefault(),a.stopPropagation()):c.removeClass("has-error").addClass("has-success")})}),this},a.fn.relayPointWidget=function(){return this.each(function(){function e(){y=new b({title:"Choisissez un point relais",size:b.SIZE_WIDE,cssClass:"commerce-relay-point-dialog",message:"<p>Please wait...</p>",buttons:[{label:"Fermer",cssClass:"btn-default",action:function(a){a.close()}},{label:"Valider",cssClass:"btn-primary"}]}),y.realize(),y.getModalBody().html(c["pick_relay_point.html.twig"].render()),r=y.getModalBody().find(".rp-list"),q=y.getModalBody().find(".rp-form"),s=y.getModalFooter().find(".btn-primary").prop("disabled",!0),q.find('input[type="text"][required]').requiredInputWidget(),q.on("keyup",'input[type="text"]',function(){A&&(A.abort(),A=null),z&&(clearTimeout(z),z=null);for(var a={street:"",postalCode:"",city:""},b=y.getModalBody().find("form").serializeArray(),c=0;c<b.length;c++)a[b[c].name]=b[c].value.trim();a.street.length&&a.postalCode.length&&a.city.length&&(s.prop("disabled",!0),r.empty().loadingSpinner(),z=setTimeout(function(){h(a)},1e3))}),r.on("change",'input[type="radio"]',function(){r.find(".rp-point.active").removeClass("active").find(".rp-details").slideUp();var a=r.find('input[type="radio"]:checked');1===a.length&&(a.closest(".rp-point").addClass("active").find(".rp-details").slideDown(),s.prop("disabled",!1))}),s.on("click",function(){if(!s.prop("disabled")){var b=r.find('input[type="radio"]:checked');if(1===b.length){y.getModalContent().loadingSpinner(),f();var c=a.ajax({url:d.generate("ekyna_commerce_api_shipment_gateway_get_relay_point",{gateway:w}),method:"GET",data:{number:b.val()},dataType:"json"});c.done(function(a){a.hasOwnProperty("relay_point")&&a.relay_point&&(f(a.relay_point),y.close())}),c.always(function(){y.getModalContent().loadingSpinner("off")})}}}),u&&y.onShown(function(){q.find('input[name="street"]').val(u.street).trigger("keyup"),q.find('input[name="postalCode"]').val(u.postal_code).trigger("keyup"),q.find('input[name="city"]').val(u.city).trigger("keyup")}),y.open()}function f(a){void 0===a&&t&&t.platform===x&&(a=t),a?(k.val(a.number),l.html(c["relay_point.html.twig"].render(a)).show(),m.hide()):(k.val(null),l.empty().hide(),m.show())}function g(){f()}function h(b){A=a.ajax({url:d.generate("ekyna_commerce_api_shipment_gateway_list_relay_points",{gateway:w}),method:"GET",data:b}),A.done(function(a){r.html(c["relay_point_list.html.twig"].render(a))}),A.always(function(){r.loadingSpinner("off")})}function i(){var a=null;a=1===p.length?p.find("option:selected"):p.filter(":checked").eq(0),1===a.length?(v=a.data("relay"),x=a.data("platform"),w=a.data("gateway")):v=x=w=!1,v&&x&&w?(n.removeClass("disabled").on("click",e),f(),j.slideDown()):(j.slideUp(),f(!1),n.addClass("disabled").off("click",e))}var j=a(this),k=j.find('input[type="hidden"]'),l=j.find("p.relay-point-address"),m=j.find("p.relay-point-none"),n=j.find("button.relay-point-search"),o=j.find("button.relay-point-clear"),p=j.closest("form").find('.shipment-method, input[name="shipment[shipmentMethod]"]'),q=null,r=null,s=null,t=k.data("initial"),u=k.data("search"),v=!1,w=!1,x=!1,y=null,z=null,A=null;n.addClass("disabled"),o.on("click",g),p.on("change",i),i()}),this},{init:function(a){a.relayPointWidget()}}});