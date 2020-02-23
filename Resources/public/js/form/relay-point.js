define(["jquery","bootstrap/dialog","ekyna-commerce/templates","routing","ekyna-ui"],function(a,b,c,d){"use strict";return a.fn.requiredInputWidget=function(){return this.each(function(){var b=a(this),c=b.closest(".form-group");b.on("keyup",function(a){0===b.val().trim().length?(c.removeClass("has-success").addClass("has-error"),a.preventDefault(),a.stopPropagation()):c.removeClass("has-error").addClass("has-success")})}),this},a.fn.relayPointWidget=function(){return this.each(function(){function e(){I=new b({title:"Choisissez un point relais",size:b.SIZE_WIDE,cssClass:"commerce-relay-point-dialog",message:"<p>Please wait...</p>",buttons:[{label:"Fermer",cssClass:"btn-default",action:function(a){a.close()}},{label:"Valider",cssClass:"btn-primary"}]}),I.realize(),I.getModalBody().html(c["@EkynaCommerce/Js/pick_relay_point.html.twig"].render()),A=I.getModalBody().find(".rp-list"),y=I.getModalBody().find(".rp-form"),z=I.getModalBody().find(".rp-error"),C=I.getModalBody().find(".select-search-point"),B=I.getModalFooter().find(".btn-primary").prop("disabled",!0),y.find('input[type="text"][required]').requiredInputWidget(),y.on("keyup",'input[type="text"]',function(){K&&(K.abort(),K=null),J&&(clearTimeout(J),J=null),C.addClass("disabled");var a=f();a&&(B.prop("disabled",!0),A.empty().loadingSpinner(),J=setTimeout(function(){h(a),m(a)},1e3))}),A.on("change",'input[type="radio"]',function(){var a=A.find('input[type="radio"]:checked');1===a.length&&j(a.val())}),B.on("click",function(){if(!B.prop("disabled")){var b=A.find('input[type="radio"]:checked');if(1===b.length){I.getModalContent().loadingSpinner(),k();var c=a.ajax({url:d.generate("ekyna_commerce_api_shipment_gateway_get_relay_point",{gateway:G}),method:"GET",data:{number:b.val()},dataType:"json"});c.done(function(a){a.hasOwnProperty("relay_point")&&a.relay_point&&(k(a.relay_point),I.close())}),c.always(function(){I.getModalContent().loadingSpinner("off")})}}}),C.on("click",function(){C.hasClass("disabled")||L&&N&&new google.maps.event.trigger(N,"click")}),I.onShown(function(){if(E&&(y.find('input[name="street"]').val(E.street).trigger("keyup"),y.find('input[name="postalCode"]').val(E.postal_code).trigger("keyup"),y.find('input[name="city"]').val(E.city).trigger("keyup")),"object"==typeof google&&google.hasOwnProperty("maps"))return void g();window.initRelayPointMap=g;var a=document.createElement("script");a.src="https://maps.googleapis.com/maps/api/js?key="+r.data("map-api-key")+"&callback=initRelayPointMap",document.body.appendChild(a)}),I.open()}function f(){for(var a={street:"",postalCode:"",city:""},b=I.getModalBody().find("form").serializeArray(),c=0;c<b.length;c++)a[b[c].name]=b[c].value.trim();return a.street.length&&a.postalCode.length&&a.city.length?a:null}function g(){p={path:"M14.21,42C14.21,29,26,23.94,26,13.5a12.5,12.5,0,0,0-25,0C1,24,12.79,29,12.79,42Z",size:new google.maps.Size(27,43),anchor:new google.maps.Point(13,43),fillColor:"white",fillOpacity:.6,strokeColor:"#337ab7",strokeWeight:1,strokeOpacity:.6},q={path:"M14.21,42C14.21,29,26,23.94,26,13.5a12.5,12.5,0,0,0-25,0C1,24,12.79,29,12.79,42Z",size:new google.maps.Size(27,43),anchor:new google.maps.Point(13,43),fillColor:"#337ab7",fillOpacity:1,strokeColor:"white",strokeWeight:1,strokeOpacity:1},L=new google.maps.Map(document.getElementById("relay-point-map"),{center:{lat:46.52863469527167,lng:2.43896484375},zoom:5,disableDefaultUI:!0}),M=new google.maps.Geocoder,h(f())}function h(a){L&&(N&&(N.setMap(null),N=null),a&&M.geocode({address:a.street+" "+a.postalCode+" "+a.city},function(b,c){"OK"===c?(L.setCenter(b[0].geometry.location),N=new google.maps.Marker({map:L,position:b[0].geometry.location}),N.addListener("click",function(){o&&(o.setMap(null),o=null),o=new google.maps.InfoWindow({content:"<p>"+a.street+"<br>"+a.postalCode+" "+a.city+"</p>"}),o.open(L,N),L.setZoom(12),L.setCenter(N.getPosition())}),C.removeClass("disabled")):console.log("Geocode was not successful for the following reason: "+c)}))}function i(b){if(L){for(var c=0;c<O.length;c++)O[c].setMap(null);if(b.hasOwnProperty("error")&&b.error&&z.show().find("> div").html(b.error),b.hasOwnProperty("relay_points")){O=[],a(b.relay_points).each(function(a,b){var c=new google.maps.Marker({map:L,position:{lat:parseFloat(b.latitude),lng:parseFloat(b.longitude)},icon:p});c.set("id",b.number),c.addListener("click",function(){j(b.number)}),O.push(c)});var d=new google.maps.LatLngBounds;for(c=0;c<O.length;c++)d.extend(O[c].getPosition());L.fitBounds(d)}}}function j(a){if(A.find(".rp-point.active").removeClass("active").find(".rp-details").slideUp(),A.find('input[type="radio"]').prop("checked",!1),L)for(var b=0;b<O.length;b++)O[b].setIcon(p);var d=A.find('input[type="radio"][value="'+a+'"]');if(1===d.length){if(d.prop("checked",!0),d.closest(".rp-point").addClass("active").find(".rp-details").slideDown(),A.parent().scrollTop(d.closest(".rp-point").position().top),B.prop("disabled",!1),!L)return;o&&(o.setMap(null),o=null);var e=d.data("point");for(b=0;b<O.length;b++)if(O[b].get("id")===a){O[b].setIcon(q),o=new google.maps.InfoWindow({content:c["@EkynaCommerce/Js/relay_point.html.twig"].render(e)}),o.open(L,O[b]),L.setZoom(15),L.setCenter(O[b].getPosition());break}}}function k(a){void 0===a&&D&&D.platform===H&&(a=D),a?(s.val(a.number),t.html(c["@EkynaCommerce/Js/relay_point.html.twig"].render(a)).show(),u.hide()):(s.val(null),t.empty().hide(),u.show())}function l(){k()}function m(b){z.hide(),K=a.ajax({url:d.generate("ekyna_commerce_api_shipment_gateway_list_relay_points",{gateway:G}),method:"GET",data:b}),K.done(function(a){A.html(c["@EkynaCommerce/Js/relay_point_list.html.twig"].render(a)),i(a)}),K.always(function(){A.loadingSpinner("off")})}function n(){var a=null;a=1===x.length?x.find("option:selected"):x.filter(":checked").eq(0),1===a.length?(F=a.data("relay"),H=a.data("platform"),G=a.data("gateway")):F=H=G=!1,F&&H&&G?(v.removeClass("disabled").on("click",e),k(),r.slideDown()):(r.slideUp(),k(!1),v.addClass("disabled").off("click",e))}var o,p,q,r=a(this),s=r.find('input[type="hidden"]'),t=r.find("p.relay-point-address"),u=r.find("p.relay-point-none"),v=r.find("button.relay-point-search"),w=r.find("button.relay-point-clear"),x=r.closest("form").find('.shipment-method, input[name="shipment[shipmentMethod]"]'),y=null,z=null,A=null,B=null,C=null,D=s.data("initial"),E=s.data("search"),F=!1,G=!1,H=!1,I=null,J=null,K=null,L=null,M=null,N=null,O=[];v.addClass("disabled"),w.on("click",l),x.on("change",n),n()}),this},{init:function(a){a.relayPointWidget()}}});