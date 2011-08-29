function ysCreateShortCodes() {
    var formSc = "[yardsale-form";
    var listingSc = "[yardsale-listing";

    // Event
    formSc += ysCreateParam('sc_event', 'event');
    listingSc += ysCreateParam('sc_event', 'event');

    // Map Height & Width
    formSc += ysCreateParam('sc_form_map_height', 'mapheight');
    formSc += ysCreateParam('sc_form_map_width', 'mapwidth');
    listingSc += ysCreateParam('sc_listing_map_height', 'mapheight');
    listingSc += ysCreateParam('sc_listing_map_width', 'mapwidth');

    // Map Center
    formSc += ysCreateParam('sc_lat', 'lat');
    formSc += ysCreateParam('sc_lng', 'lng');
    formSc += ysCreateParam('sc_zoom', 'zoom');
    listingSc += ysCreateParam('sc_lat', 'lat');
    listingSc += ysCreateParam('sc_lng', 'lng');
    listingSc += ysCreateParam('sc_zoom', 'zoom');

    // City State Zip Choices
    formSc += ysCreateParam('sc_city', 'city');
    formSc += ysCreateParam('sc_state', 'state');
    formSc += ysCreateParam('sc_zip', 'zip');

    // City State Zip Default Values
    formSc += ysCreateParam('sc_citydefault', 'citydefault');
    formSc += ysCreateParam('sc_statedefault', 'statedefault');
    formSc += ysCreateParam('sc_zipdefault', 'zipdefault');

    // Hide on Print
    listingSc += ysCreateParam('sc_hideonprint', 'hideonprint');

    // Close Short Codes
    formSc += "]";
    listingSc += "]";

    // Display Short Codes
    jQuery('#sc_form_result_text').text(formSc);
    jQuery('#sc_listing_result_text').text(listingSc);
}

function ysCreateParam(id, param) {
    var tmp = jQuery('#' + id).val();
    if (tmp != null && tmp != "") {
        return ' ' + param + '="' + tmp + '"';
    }
    return '';
}


function ScGoogleMap() {

    var ysGoogleMap;
    var marker;
    var me = this;

    this.initGoogleMap = function (centerLat, centerLng, zoom) {
        google.maps.event.addDomListener(window, 'load', function() {
            var latlng = new google.maps.LatLng(centerLat, centerLng);
            var myOptions = {
                zoom: zoom,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var canvas = document.getElementById("map_canvas");
            ysGoogleMap = new google.maps.Map(canvas, myOptions);
            google.maps.event.addListener(ysGoogleMap, 'click', function(event) {
                document.getElementById('sc_lat').value = event.latLng.lat();
                document.getElementById('sc_lng').value = event.latLng.lng();
                me.setMarker(event.latLng);
                ysGoogleMap.setCenter(event.latLng);
                ysCreateShortCodes();
            });
            google.maps.event.addListener(ysGoogleMap, 'zoom_changed', function() {
                document.getElementById('sc_zoom').value = ysGoogleMap.getZoom();
                ysCreateShortCodes();
            });
        });
    };

    this.centerMapOnAddress = function(address) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                    var latlng = results[0].geometry.location;
                    if (latlng) {
                        document.getElementById('sc_lat').value = latlng.lat();
                        document.getElementById('sc_lng').value = latlng.lng();
                        me.setMarker(latlng);
                        ysGoogleMap.setCenter(latlng);
                        ysCreateShortCodes();
                    }
                }
                else {
                    //alert("No results found for '" + address + "'");
                }
            }
            else {
                //alert("Geocode was not successful for the following reason: " + status);
            }
        });
    };

    this.centerMapOnLatLng = function() {
        try {
            var latlng = new google.maps.LatLng(
                    parseInt(document.getElementById('sc_lat').value),
                    parseInt(document.getElementById('sc_lng').value));
            ysGoogleMap.setCenter(latlng);
            me.setMarker(latlng);
        }
        catch (ex) {
        }
    };


    this.setMarker = function(latlng) {
        if (marker) {
            marker.setVisible(false);
        }
        marker = new google.maps.Marker(
                {
                    position: latlng,
                    map: ysGoogleMap,
                    title: 'Map Center'
                });
    };

    this.zoomMap = function() {
        try {
            ysGoogleMap.setZoom(parseInt(document.getElementById('sc_zoom').value));
        }
        catch (ex) {
        }
    }

}

