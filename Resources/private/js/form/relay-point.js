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
                $modalForm = null, $modalList = null, $modalButton = null, $selectSearchPoint = null,
                initial = $input.data('initial'), search = $input.data('search'),
                support = false, gateway = false, platform = false,
                dialog = null, queryTimeout = null, queryXhr = null,
                map = null, geocoder = null, searchMarker = null, pointMarkers = [],
                infoWindow, defaultIcon, selectedIcon;

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
                $selectSearchPoint = dialog.getModalBody().find('.select-search-point');
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

                    $selectSearchPoint.addClass('disabled');

                    var data = getFormData();
                    if (!data) {
                        return;
                    }

                    $modalButton.prop('disabled', true);

                    $modalList.empty().loadingSpinner();

                    queryTimeout = setTimeout(function () {
                        updateMapSearchLocation(data);
                        queryRelayPoints(data);
                    }, 1000);
                });

                $modalList.on('change', 'input[type="radio"]', function () {
                    var $choice = $modalList.find('input[type="radio"]:checked');
                    if (1 === $choice.length) {
                        selectRelayPoint($choice.val());
                    }
                });

                $modalButton.on('click', function () {
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

                $selectSearchPoint.on('click', function() {
                    if ($selectSearchPoint.hasClass('disabled')) {
                        return;
                    }
                    if (map && searchMarker) {
                        new google.maps.event.trigger(searchMarker, 'click');
                    }
                });

                dialog.onShown(function () {
                    if (search) {
                        $modalForm.find('input[name="street"]').val(search.street).trigger('keyup');
                        $modalForm.find('input[name="postalCode"]').val(search.postal_code).trigger('keyup');
                        $modalForm.find('input[name="city"]').val(search.city).trigger('keyup');
                    }

                    if (typeof google === 'object' && google.hasOwnProperty('maps')) {
                        initGMap();

                        return;
                    }

                    window.initRelayPointMap = initGMap;

                    var script = document.createElement('script');
                    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + $form.data('map-api-key') + '&callback=initRelayPointMap';
                    document.body.appendChild(script);
                });

                dialog.open();
            }

            function getFormData() {
                var data = {street: '', postalCode: '', city: ''},
                    formArray = dialog.getModalBody().find('form').serializeArray();

                for (var i = 0; i < formArray.length; i++) {
                    data[formArray[i]['name']] = formArray[i]['value'].trim();
                }
                if (data.street.length && data.postalCode.length && data.city.length) {
                    return data;
                }

                return null;
            }

            function initGMap() {
                defaultIcon = {
                    path: 'M14.21,42C14.21,29,26,23.94,26,13.5a12.5,12.5,0,0,0-25,0C1,24,12.79,29,12.79,42Z',
                    size: new google.maps.Size(27, 43),
                    anchor: new google.maps.Point(13, 43),
                    fillColor: 'white',
                    fillOpacity: 0.6,
                    strokeColor: '#337ab7',
                    strokeWeight: 1,
                    strokeOpacity: 0.6
                };
                selectedIcon = {
                    path: 'M14.21,42C14.21,29,26,23.94,26,13.5a12.5,12.5,0,0,0-25,0C1,24,12.79,29,12.79,42Z',
                    size: new google.maps.Size(27, 43),
                    anchor: new google.maps.Point(13, 43),
                    fillColor: '#337ab7',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 1,
                    strokeOpacity: 1
                };

                map = new google.maps.Map(document.getElementById('relay-point-map'), {
                    center: {lat: 46.52863469527167, lng: 2.43896484375},
                    zoom: 5,
                    disableDefaultUI: true
                });
                geocoder = new google.maps.Geocoder();

                updateMapSearchLocation(getFormData());
            }

            function updateMapSearchLocation(data) {
                if (!map) {
                    return;
                }

                if (searchMarker) {
                    searchMarker.setMap(null);
                    searchMarker = null;
                }

                if (!data) {
                    return;
                }

                geocoder.geocode(
                    {'address': data.street + ' ' + data.postalCode + ' ' + data.city},
                    function (results, status) {
                        if (status === 'OK') {
                            map.setCenter(results[0].geometry.location);

                            searchMarker = new google.maps.Marker({
                                map: map,
                                position: results[0].geometry.location
                            });

                            searchMarker.addListener('click', function () {
                                if (infoWindow) {
                                    infoWindow.setMap(null);
                                    infoWindow = null;
                                }

                                infoWindow = new google.maps.InfoWindow({
                                    content: '<p>' + data.street + '<br>' + data.postalCode + ' ' + data.city + '</p>'
                                });
                                infoWindow.open(map, searchMarker);

                                map.setZoom(12);
                                map.setCenter(searchMarker.getPosition());
                            });

                            $selectSearchPoint.removeClass('disabled');
                        } else {
                            console.log('Geocode was not successful for the following reason: ' + status);
                        }
                    }
                );
            }

            function updateMapRelayPoints(response) {
                if (!map) {
                    return;
                }

                // Clear markers
                for (var i = 0; i < pointMarkers.length; i++) {
                    pointMarkers[i].setMap(null);
                }

                // Creates markers
                pointMarkers = [];
                $(response.relay_points).each(function (index, point) {
                    var marker = new google.maps.Marker({
                        map: map,
                        position: {lat: parseFloat(point.latitude), lng: parseFloat(point.longitude)},
                        icon: defaultIcon
                    });
                    marker.set('id', point.number);

                    marker.addListener('click', function () {
                        selectRelayPoint(point.number);
                    });

                    pointMarkers.push(marker);
                });

                // Auto zoom
                var bounds = new google.maps.LatLngBounds();
                for (i = 0; i < pointMarkers.length; i++) {
                    //  And increase the bounds to take this point
                    bounds.extend(pointMarkers[i].getPosition());
                }
                map.fitBounds(bounds);
            }

            function selectRelayPoint(number) {
                $modalList
                    .find('.rp-point.active')
                    .removeClass('active')
                    .find('.rp-details')
                    .slideUp();

                $modalList.find('input[type="radio"]').prop('checked', false);

                if (map) {
                    for (var i = 0; i < pointMarkers.length; i++) {
                        pointMarkers[i].setIcon(defaultIcon);
                    }
                }

                var $choice = $modalList.find('input[type="radio"][value="' + number + '"]');

                if (1 === $choice.length) {
                    $choice.prop('checked', true);
                    $choice
                        .closest('.rp-point')
                        .addClass('active')
                        .find('.rp-details')
                        .slideDown();

                    $modalList.parent().scrollTop($choice.closest('.rp-point').position().top);
                    $modalButton.prop('disabled', false);

                    if (!map) {
                        return;
                    }

                    if (infoWindow) {
                        infoWindow.setMap(null);
                        infoWindow = null;
                    }

                    var point = $choice.data('point');
                    for (i = 0; i < pointMarkers.length; i++) {
                        if (pointMarkers[i].get('id') === number) {
                            pointMarkers[i].setIcon(selectedIcon);
                            infoWindow = new google.maps.InfoWindow({
                                content: Templates['relay_point.html.twig'].render(point)
                            });
                            infoWindow.open(map, pointMarkers[i]);

                            map.setZoom(15);
                            map.setCenter(pointMarkers[i].getPosition());

                            break;
                        }
                    }
                }
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

                // TODO Google map
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

                    updateMapRelayPoints(response);
                });
                queryXhr.always(function () {
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
