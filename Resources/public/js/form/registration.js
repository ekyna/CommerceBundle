define(["jquery","validator"],function(w,k){"use strict";function D(s,r){s.prop("required",r);s=w('label[for="'+s.attr("id")+'"]');1===s.length&&(r?s.addClass("required"):s.removeClass("required"))}return w.fn.registrationWidget=function(){var g="#registration_customer_email_first",v="#registration_customer_email_second",m="#registration_customer_plainPassword_first",p="#registration_customer_plainPassword_second",_="#registration_customer_company",C="#registration_customer_vatNumber",y="#registration_applyGroup > input",b="#registration_business",q="#registration_regular";return this.each(function(){var s=w(this),r=s.find(g),e=r.closest(".form-group"),a=s.find(v),i=a.closest(".form-group"),n=s.find(m),t=n.closest(".form-group"),u=s.find(p),o=u.closest(".form-group"),d=s.find(_),f=(s.find(C),s.find(y)),l=s.find(q),c=s.find(b);function h(){console.log(f.val());var s=f.filter(":checked");if(1===s.length&&1===parseInt(s.data("business")))return D(d,!0),l.slideUp(function(){l.find("select,input").each(function(){w(this).val(null)})}),void c.slideDown();l.slideDown(),c.slideUp(function(){c.find("select,input").each(function(){w(this).val(null)})}),D(d,!1)}1===r.length&&s.on("change keyup",g+", "+v,function(){e.removeClass("has-success has-error"),i.removeClass("has-success has-error");var s=r.val();if(6<s.length&&k.isEmail(s))return e.addClass("has-success"),void(s===a.val()?i.addClass("has-success"):i.addClass("has-error"));e.addClass("has-error")}),1===n.length&&s.on("change keyup",m+", "+p,function(){t.removeClass("has-success has-error"),o.removeClass("has-success has-error");var s=n.val();if(6<=s.length)return t.addClass("has-success"),void(s===u.val()?o.addClass("has-success"):o.addClass("has-error"));t.addClass("has-error")}),s.on("change",y,h),h()}),this},{init:function(s){s.registrationWidget()}}});