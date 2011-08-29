function YSFormJS(formId, mapId, centerLat, centerLong, zoom) {

    this.map = null;
    this.marker = null;

    this.getAddress = function() {
        var frm = document.getElementById(formId);
        return "" +
                frm.street.value + ', ' +
                frm.city.value + ' ' +
                frm.state.value + ' ' +
                frm.zip.value;
    };

    this.fetchLatLong = function() {
        if (this.marker) {
            this.marker.setMap(null);
        }
        var geocoder = new google.maps.Geocoder();
        var address = this.getAddress();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                    var latlng = results[0].geometry.location;
                    document.getElementById(formId).latlng.value = latlng;
                    this.marker = new google.maps.Marker(
                            {
                                position: latlng,
                                map: this.map,
                                title: address
                            });
                    this.map.setCenter(latlng);
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

    this.validate = function() {
        var frm = document.getElementById(formId);
        if (frm.email.value == "") return this.error(frm.email);
        if (frm.street.value == "") return this.error(frm.street);
        if (frm.city.value == "") return this.error(frm.city);
        if (frm.state.value == "") return this.error(frm.state);
        if (frm.zip.value == "") return this.error(frm.zip);
        if (frm.listing.value == "") return this.error(frm.listing);
        if (frm.latlng.value == "") {
            alert('Address cannot be located on Google Maps: \n"' +
                          this.getAddress() + '"');
            return false;
        }
        return true;
    };

    this.error = function(field) {
        alert('Please fill-in required fields marked with a "*"');
        field.focus();
        return false;
    };

    this.initGoogleMap = function() {
        var ysFormJs = this;
        google.maps.event.addDomListener(window, 'load', function() {
            var myLatlng = new google.maps.LatLng(centerLat, centerLong);
            var myOptions = {
                zoom: zoom,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            this.map = new google.maps.Map(document.getElementById(mapId), myOptions);

            if (document.getElementById(formId).street.value.length > 0) {
                ysFormJsfetchLatLong();
            }
        });
    }
}
