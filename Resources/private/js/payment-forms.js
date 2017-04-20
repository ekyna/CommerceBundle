define(['jquery', 'ekyna-spinner'], function($) {
    "use strict";

    var $forms = $('form.checkout-payment');

    function preventSubmit(e) {
        e.preventDefault();
        e.stopPropagation();

        return false;
    }

    function handleSubmit(e) {
        var $clicked = $(e.currentTarget).closest('form');

        $clicked.loadingSpinner();

        $forms
            .off('submit', handleSubmit)
            .on('submit', preventSubmit)
            .not($clicked)
            .find('button[type="submit"]')
            .prop('disabled', true)
    }

    $forms.on('submit', handleSubmit);
});
