define(['jquery', 'routing'], function ($, Router) {

    var initCount, map, cluster, heatmap, $form, busy = false,
    infoWindow, infoXhr;

    function check() {
        var check = true;
        ['commerceMap', 'commerceMarkerCluster', 'commerceHeatmap'].forEach(function(name) {
            if (!(window.hasOwnProperty(name) && typeof window[name] !== "undefined")) {
                check = false;
                return false;
            }
        });

        return check;
    }

    function init() {
        map = window.commerceMap;
        cluster = window.commerceMarkerCluster;
        heatmap = window.commerceHeatmap;

        $form = $('form[name="map"]');
        $form.on('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            loadMarkers();

            return false;
        });

        initAutoComplete();
        loadMarkers();
    }

    function initAutoComplete() {
        var input = document.getElementById('map_search');
        try {
            var autoComplete = new google.maps.places.Autocomplete(input, {
                types: ['(regions)']
            });
        } catch (e) {
            console.log('Failed to load autocomplete');
            return;
        }

        autoComplete.bindTo('bounds', map);
        autoComplete.addListener('place_changed', function() {
            var place = autoComplete.getPlace();
            if (!place.geometry) {
                console.log("Autocomplete's returned place contains no geometry");
                return;
            }

            map.setCenter(place.geometry.location);
        });
    }


    function loadMarkers() {
        if (busy) {
            return;
        }

        busy = true;
        var data = $form.serialize();
        $form.find('input, select, button').prop('disabled', true);

        var xhr = $.ajax({
            url: Router.generate('ekyna_commerce_admin_map_data'),
            method: 'GET',
            data: data,
            dataType: 'json'
        });

        xhr.done(function (data) {
            if (!data.hasOwnProperty('locations')) {
                return;
            }

            cluster.clearMarkers();

            if ($form.find('[name="map[mode]"]').val() === 'order') {
                displayHeatMap(data['locations']);
            } else {
                displayMarkers(data['locations']);
            }

            busy = false;
            $form.find('input, select, button').prop('disabled', false);
        });
    }

    function displayMarkers(locations) {
        // Hide heatmap
        heatmap.setData([]);
        heatmap.setMap(null);

        map.setOptions({styles: markerMapStyles});

        // Show marker cluster
        //cluster.setMap(map);
        cluster.clearMarkers();
        locations.forEach(function (location) {
            //datum.map = map;
            var marker = new google.maps.Marker(location);

            marker.addListener('click', function() {
                showInfoWindow(marker);
            });

            cluster.addMarker(marker);
        });
    }

    function showInfoWindow(marker) {
        if (infoWindow) {
            infoWindow.close();
            infoWindow = null;
        }

        if (marker.infoWindowContent) {
            infoWindow = new google.maps.InfoWindow({
                content: marker.infoWindowContent
            });
            infoWindow.open(map, marker);

            return;
        }

        var xhr = $.ajax({
            url: Router.generate('ekyna_commerce_admin_map_info'),
            method: 'GET',
            data: {customerId: marker.customerId},
            dataType: 'html'
        });

        xhr.done(function (html) {
            marker.infoWindowContent = html;

            infoWindow = new google.maps.InfoWindow({
                content: html
            });
            infoWindow.open(map, marker);
        });
    }

    function displayHeatMap(locations) {
        // Hide marker cluster
        cluster.clearMarkers();
        //cluster.setMap(null);

        map.setOptions({styles: heatMapStyles});

        // Show heatmap
        heatmap.setData(locations.map(function (l) {
            return {location: new google.maps.LatLng(l.lat, l.lng), weight: l.weight};
        }));
        heatmap.setMap(map);
    }

    var i = setInterval(function() {
        if (check()) {
            clearInterval(i);
            init();
        }
    }, 250);

    /** @see https://snazzymaps.com/editor */
    var markerMapStyles = [
        {
            "featureType": "all",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "saturation": 36
                },
                {
                    "color": "#333333"
                },
                {
                    "lightness": 40
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "visibility": "on"
                },
                {
                    "color": "#ffffff"
                },
                {
                    "lightness": 16
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#fefefe"
                },
                {
                    "lightness": 20
                }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#fefefe"
                },
                {
                    "lightness": 17
                },
                {
                    "weight": 1.2
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels",
            "stylers": [
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "on"
                },
                {
                    "color": "#b6b6b6"
                },
                {
                    "weight": "0.20"
                }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#ffffff"
                },
                {
                    "lightness": 20
                }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "poi.park",
            "elementType": "labels",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#dedede"
                },
                {
                    "lightness": 17
                }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#dedede"
                },
                {
                    "lightness": 29
                },
                {
                    "weight": 0.2
                }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "geometry",
            "stylers": [
                {
                    "lightness": 18
                },
                {
                    "color": "#dedede"
                }
            ]
        },
        {
            "featureType": "road.local",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#e3e3e3"
                },
                {
                    "lightness": 16
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#a0d6d1"
                },
                {
                    "lightness": 17
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "labels",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        }
    ];

    var heatMapStyles = [
        {
            "featureType": "all",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "saturation": 36
                },
                {
                    "color": "#000000"
                },
                {
                    "lightness": 40
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "visibility": "on"
                },
                {
                    "color": "#000000"
                },
                {
                    "lightness": 16
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 20
                }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 17
                },
                {
                    "weight": 1.2
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels",
            "stylers": [
                {
                    "visibility": "on"
                },
                {
                    "lightness": "0"
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "lightness": "-10"
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "lightness": "-10"
                }
            ]
        },
        {
            "featureType": "administrative.locality",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "color": "#737373"
                }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 20
                }
            ]
        },
        {
            "featureType": "landscape.natural",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 21
                }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "lightness": 17
                },
                {
                    "color": "#3c3c3c"
                }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "lightness": 29
                },
                {
                    "weight": 0.2
                },
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "geometry",
            "stylers": [
                {
                    "lightness": 18
                },
                {
                    "color": "#393939"
                }
            ]
        },
        {
            "featureType": "road.local",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 16
                }
            ]
        },
        {
            "featureType": "transit",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "transit",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 19
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#000000"
                },
                {
                    "lightness": 17
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "labels",
            "stylers": [
                {
                    "lightness": "0"
                },
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "labels.text",
            "stylers": [
                {
                    "lightness": "0"
                }
            ]
        }
    ];
});
