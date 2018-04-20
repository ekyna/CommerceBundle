define([
    'jquery', 'bootstrap/dialog', 'ekyna-commerce/templates', 'routing', 'ekyna-ui'
], function ($, BootstrapDialog, Templates, Router) {
    "use strict";

    $.fn.requiredInputWidget = function () {
        this.each(function () {
            var $input = $(this),
                $group = $input.closest('.form-group');

            $input.on('keyup', function (e) {
                if (0 === $input.val().trim().length) {
                    $group.removeClass('has-success').addClass('has-error');
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    $group.removeClass('has-error').addClass('has-success');
                }
            });
        });

        return this;
    };

    /**
     * Relay point widget
     */
    $.fn.relayPointWidget = function () {

        this.each(function () {
            var $form = $(this),
                $input = $form.find('input[type="hidden"]'),
                $address = $form.find('p.relay-point-address'),
                $none = $form.find('p.relay-point-none'),
                $searchButton = $form.find('button.relay-point-search'),
                $clearButton = $form.find('button.relay-point-clear'),
                $method = $form.closest('form').find('.shipment-method, input[name="shipment[shipmentMethod]"]'),
                $modalForm = null, $modalList = null, $modalButton = null,
                initial = $input.data('initial'), search = $input.data('search'),
                support = false, gateway = false, platform = false,
                dialog = null, queryTimeout = null, queryXhr = null;

            $searchButton.addClass('disabled');

            function onSearchClick() {
                dialog = new BootstrapDialog({
                    title: 'Choisissez un point relais',
                    size: BootstrapDialog.SIZE_WIDE,
                    cssClass: 'commerce-relay-point-dialog',
                    message: '<p>Please wait...</p>',
                    buttons: [{
                        label: 'Fermer',
                        cssClass: 'btn-default',
                        action: function (dialog) {
                            dialog.close();
                        }
                    }, {
                        label: 'Valider',
                        cssClass: 'btn-primary'
                    }]
                });

                dialog.realize();

                dialog.getModalBody().html(Templates['pick_relay_point.html.twig'].render());

                $modalList = dialog.getModalBody().find('.rp-list');
                $modalForm = dialog.getModalBody().find('.rp-form');
                $modalButton = dialog.getModalFooter().find('.btn-primary').prop('disabled', true);

                $modalForm.find('input[type="text"][required]').requiredInputWidget();

                $modalForm.on('keyup', 'input[type="text"]', function () {
                    if (queryXhr) {
                        queryXhr.abort();
                        queryXhr = null;
                    }

                    if (queryTimeout) {
                        clearTimeout(queryTimeout);
                        queryTimeout = null;
                    }

                    var data = {street: '', postalCode: '', city: ''},
                        formArray = dialog.getModalBody().find('form').serializeArray();
                    for (var i = 0; i < formArray.length; i++) {
                        data[formArray[i]['name']] = formArray[i]['value'].trim();
                    }
                    if (!(data.street.length && data.postalCode.length && data.city.length)) {
                        return;
                    }

                    $modalButton.prop('disabled', true);

                    $modalList.empty().loadingSpinner();

                    queryTimeout = setTimeout(function () {
                        queryRelayPoints(data)
                    }, 1000);
                });

                $modalList.on('change', 'input[type="radio"]', function () {
                    $modalList
                        .find('.rp-point.active')
                        .removeClass('active')
                        .find('.rp-details')
                        .slideUp();

                    var $choice = $modalList.find('input[type="radio"]:checked');
                    if (1 === $choice.length) {
                        $choice
                            .closest('.rp-point')
                            .addClass('active')
                            .find('.rp-details')
                            .slideDown();

                        $modalButton.prop('disabled', false);
                    }
                });

                $modalButton.on('click', function() {
                    if ($modalButton.prop('disabled')) {
                        return;
                    }

                    var $choice = $modalList.find('input[type="radio"]:checked');
                    if (1 === $choice.length) {
                        dialog.getModalContent().loadingSpinner();

                        setRelayPoint();

                        var rpXhr = $.ajax({
                            url: Router.generate('ekyna_commerce_api_shipment_gateway_get_relay_point', {gateway: gateway}),
                            method: 'GET',
                            data: {
                                number: $choice.val()
                            },
                            dataType: 'json'
                        });

                        rpXhr.done(function(response) {
                            if (response.hasOwnProperty('relay_point') && response['relay_point']) {
                                setRelayPoint(response['relay_point']);
                                dialog.close();
                            }
                        });

                        rpXhr.always(function() {
                            dialog.getModalContent().loadingSpinner('off');
                        });
                    }
                });

                if (search) {
                    dialog.onShown(function() {
                        $modalForm.find('input[name="street"]').val(search.street).trigger('keyup');
                        $modalForm.find('input[name="postalCode"]').val(search.postal_code).trigger('keyup');
                        $modalForm.find('input[name="city"]').val(search.city).trigger('keyup');
                    });
                }

                dialog.open();
            }

            function setRelayPoint(point) {
                if ((undefined === point) && initial && (initial.platform === platform)) {
                    point = initial;
                }

                if (point) {
                    $input.val(point.number);
                    $address.html(Templates['relay_point.html.twig'].render(point)).show();
                    $none.hide();
                } else {
                    $input.val(null);
                    $address.empty().hide();
                    $none.show();
                }
            }

            function onClearClick() {
                setRelayPoint();
            }

            function queryRelayPoints(data) {
                queryXhr = $.ajax({
                    url: Router.generate('ekyna_commerce_api_shipment_gateway_list_relay_points', {gateway: gateway}),
                    method: 'GET',
                    data: data
                });

                queryXhr.done(function (response) {
                    $modalList.html(Templates['relay_point_list.html.twig'].render(response));
                });
                queryXhr.always(function() {
                    $modalList.loadingSpinner('off');
                })
            }

            function onMethodChange() {
                var $selectedMethod = null;

                if (1 === $method.length) { // Select
                    $selectedMethod = $method.find('option:selected');
                } else { // Radio
                    $selectedMethod = $method.filter(':checked').eq(0);
                }

                if (1 === $selectedMethod.length) {
                    support = $selectedMethod.data('relay');
                    platform = $selectedMethod.data('platform');
                    gateway = $selectedMethod.data('gateway');
                } else {
                    support = platform = gateway = false;
                }

                if (support && platform && gateway) {
                    $searchButton.removeClass('disabled').on('click', onSearchClick);
                    setRelayPoint();
                    $form.slideDown();
                } else {
                    $form.slideUp();
                    setRelayPoint(false);
                    $searchButton.addClass('disabled').off('click', onSearchClick);
                }
            }

            $clearButton.on('click', onClearClick);

            $method.on('change', onMethodChange);
            onMethodChange();
        });

        return this;
    };

    return {
        init: function ($element) {
            $element.relayPointWidget();
        }
    };
});
