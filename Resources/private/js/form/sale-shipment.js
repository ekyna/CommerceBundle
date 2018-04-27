define(['jquery', 'ekyna-form/collection'], function($) {
    "use strict";

    /**
     * Sale shipment widget
     */
    $.fn.saleShipmentWidget = function(/*config*/) {

        //config = $.extend({}, config);

        this.each(function() {

            var $this = $(this),
                $methodSelect = $this.find('select.sale-shipment-method'),
                $amountInput = $this.find('input.sale-shipment-amount'),
                $mobile = $('input.address-mobile'),
                $mobileGroup = $mobile.closest('.form-group'),
                animDuration = 150;

            function onMobileKeyUp() {
                if ($mobile.val()) {
                    $mobileGroup.removeClass('has-error');
                } else {
                    $mobileGroup.addClass('has-error');
                }
            }

            function updateMobileState() {
                var $methodOption = $methodSelect.find('option[value="' + $methodSelect.val() + '"]');

                var mobile = false;
                if (1 === $methodOption.length) {
                    mobile = $methodOption.data('mobile');
                }

                if (mobile) {
                    $mobileGroup.slideDown(animDuration);
                    $mobile.on('keyup', onMobileKeyUp);
                    onMobileKeyUp();
                } else {
                    $mobileGroup.slideUp(animDuration, function() {
                        $mobileGroup.removeClass('has-error');
                    });
                    $mobile.off('keyup', onMobileKeyUp);
                }
            }

            function applyAmountFromSelectedMethod() {
                var $methodOption = $methodSelect.find('option[value="' + $methodSelect.val() + '"]');

                $amountInput.val($methodOption.data('price'));
            }

            function onMethodChange() {
                applyAmountFromSelectedMethod();
                updateMobileState();
            }

            $methodSelect.on('change', onMethodChange);

            $this
                .on('click', '.sale-shipment-amount-apply', applyAmountFromSelectedMethod)
                .on('click', '.sale-shipment-amount-clear', function() {
                    $amountInput.val('');
                });

            updateMobileState();
        });

        return this;
    };

    return {
        init: function($element) {
            $element.saleShipmentWidget();
        }
    };
});
