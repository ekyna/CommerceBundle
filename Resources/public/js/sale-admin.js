define(["jquery","ekyna-dispatcher"],function(a,b){"use strict";var c={state:".sale-state",grandTotal:".sale-grand-total",paidTotal:".sale-paid-total",outstandingAccepted:".sale-outstanding-accepted",outstandingExpired:".sale-outstanding-expired",outstandingLimit:".sale-outstanding-limit",outstandingDate:".sale-outstanding-date",paymentTerm:".sale-payment-term",paymentState:".sale-payment-state",weightTotal:".sale-weight-total",shipmentMethod:".sale-shipment-method",shipmentState:".sale-shipment-state",invoiceTotal:".sale-invoice-total",creditTotal:".sale-credit-total",invoiceState:".sale-invoice-state"},d=function(b){var d=a(b);for(var e in c)if(c.hasOwnProperty(e)){var f=d.find(e);1===f.size()&&a(c[e]).html(f.text())}};b.on("ekyna_commerce.sale_view_response",function(a){d(a)})});