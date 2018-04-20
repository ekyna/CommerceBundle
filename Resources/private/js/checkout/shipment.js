define(['jquery'], function($) {
    "use strict";

    var $submit = $('button.cart-checkout-submit'),
        $method = $('input[name="shipment[shipmentMethod]"]'),
        $submitPrevented = $('div.submit-prevented'),
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

    function onMethodChange() {
        if ($method.filter(':checked').val()) {
            $submit.removeClass('disabled');
            $submitPrevented.slideUp(animDuration);
        } else {
            $submit.addClass('disabled');
        }

        if (0 === $mobileGroup.length) {
            return;
        }

        var $selectedMethod = $method.filter(':checked').eq(0);

        var mobile = false;
        if (1 === $selectedMethod.length) {
            mobile = $selectedMethod.data('mobile');
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

    $method.on('change', onMethodChange);
    onMethodChange();

    $submit.on('click', function() {
        if ($submit.hasClass('disabled')) {
            $submitPrevented.slideDown(animDuration);
        }
    });
});
