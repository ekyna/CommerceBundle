define(["jquery","ekyna-spinner"],function(e){"use strict";var o=e("form.checkout-payment");function i(n){return n.preventDefault(),n.stopPropagation(),!1}o.on("submit",function n(t){t=e(t.currentTarget).closest("form");t.loadingSpinner(),o.off("submit",n).on("submit",i).not(t).find('button[type="submit"]').prop("disabled",!0)})});