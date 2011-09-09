function YSListing(centerLat, centerLng, zoom, markerDirUrl, jsonUrl) {
    this.scrollOnClick = true;
    this.map = null;
    this.infowindow = null;
    this.markers = new Array();
    this.abbreviateAddresses = true; // TODO: hook up to a short code option to turn off
    //this.bounds = new google.maps.LatLngBounds();

    this.getMarkerLink = function(markerNum) {
        return markerDirUrl + markerNum + ".png";
    };

    this.makeMarker = function(lat, lng, address, listing, markerNum) {
        var shortAddr = address;
        var marker = new google.maps.Marker(
                {
                    position: new google.maps.LatLng(lat, lng),
                    map: this.map,
                    title:shortAddr
                });
        if (markerNum && markerNum <= 300) {
            marker.setIcon(this.getMarkerLink(markerNum));
        }
        this.markers[address] = marker;
        //bounds.extend(results[0].geometry.location);

        var ys = this;
        google.maps.event.addListener(marker, 'click', function() {
            if (ys.infowindow) {
                ys.infowindow.close();
            }
            ys.infowindow = new google.maps.InfoWindow(
                    { content: "<small><strong>" + shortAddr + "</strong><br/>" + listing + "</small>",
                        size: new google.maps.Size(150, 50)
                    });
            ys.infowindow.open(this.map, marker);

            // Highlight in the table
            jQuery('#yardsale_table > tbody > tr').removeClass('yshighlight');
            jQuery('#yardsale_table > tbody > tr > td:contains("' + address + '")').parent().addClass('yshighlight');

            // Scroll to it
            //jQuery('#table_div').offsetTop = jQuery('#yardsale_table > tbody > tr > td:contains("' + address + '")').scrollTop;
            if (this.scrollOnClick) {
                // TURNED OFF AUTO SCROLLING IN TABLE
                // Used to be we had table in a pane on the side of the google map so we would auto-scroll
                // the table when someone clicked on a marker on the map. But now the map sits above the
                // table and this would cause a click on the map to scroll the page where you can't see the map
                // PS. I also turned off including the script file that does this in the main plugin class file (YSPlugin.php)
                //ss.smoothScroll('marker' + markerNum);
            }
            this.scrollOnClick = true;
        });
    };

    this.toggleHighlight = function(element) {
        jQuery('#yardsale_table > tbody > tr').removeClass('yshighlight');
        if (element.className == 'yshighlight') {
            if (this.infowindow) {
                this.infowindow.close();
            }
        }
        else {
            element.className = 'yshighlight';
            var ys = this;
            jQuery("#yardsale_table > tbody > tr.yshighlight > td").each(
                    function() {
                        var marker = ys.markers[this.innerHTML];
                        if (marker) {
                            google.maps.event.trigger(marker, 'click')
                        }
                    });
        }
    };

    this.initGoogleMap = function() {
        var ys = this;
        google.maps.event.addDomListener(window, 'load', function() {
            var myLatlng = new google.maps.LatLng(centerLat, centerLng);
            var myOptions = {
                zoom: zoom,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var canvas = document.getElementById("map_canvas");
            if (canvas) {
                ys.map = new google.maps.Map(canvas, myOptions);
            }
            else {
                alert("No 'map_canvas' DIV to put Google Map");
            }

            // Retrieve data
            jQuery.getJSON(jsonUrl, function(json) {
                // Give the array to jQuery and iterate the returned jQuery array object using its each method
                var markerNum = 0;
                jQuery(json).each(function(index) {
                    markerNum = markerNum + 1;

                    // Insert rows into table
                    var lat = this[0];
                    var lng = this[1];
                    var address = this[2];
                    var listing = this[3];

                    // Allow URLs to be links
                    listing = listing.replace(/(https?:\/\/[^\)\s]+)/gi, '<a target="_blank" href="$1">$1</a>');

                    // Reduce multiple new lines to one
                    // Hack against people who like to put in lot of new lines to make their posting big
                    listing = listing.replace(/(\r\n)+/g, "\n");
                    listing = listing.replace(/\n+/g, "<br/>");

                    var shortAddr = address;
                    if (ys.abbreviateAddresses) {
                        // Abbreviate the address if possible by chopping off the City and Zip at the end
                        // looks for city/state/zip in the form: "..... CITY, XX 12345"
                        shortAddr = shortAddr.replace(/, (\w| |')+ \w\w \d\d\d\d\d(-\d\d\d\d)?$/, "");
                    }

                    jQuery('#yardsale_table > tbody').append(
                            '<tr onclick="ys.scrollOnClick=false;ys.toggleHighlight(this)"><td><image src="' + ys.getMarkerLink(markerNum) + '" alt="' + markerNum + '"/></td><td class="addrcol">' +
                                    shortAddr + '</td><td class="listingcol"><a name="marker' + markerNum + '"></a>' + listing + '</td></tr>');
                    // Put marker on map
                    try {
                        var markerHtml = '<span style="font-size: small;">' + listing + '</span>';
                        ys.makeMarker(lat, lng, shortAddr, markerHtml, markerNum);
                    }
                    catch (err) {
                        alert(err);
                    }
                });
                //ys.map.fitBounds(bounds);
            });
        });
    };

    this.initKeyFilter = function() {
        var ys = this;
        jQuery("#filter").keyup(function() {
            var theTable = jQuery("#yardsale_table");

            if (navigator.appName == 'Microsoft Internet Explorer') {
                // Filter the table
                jQuery.uiTableFilter(theTable, this.value, null, null,
                                     function(tr) {
                                         //tr.find(":first-child").each(
                                         tr.find(":nth-child(2)").each(
                                                 function() {
                                                     var marker = ys.markers[this.innerHTML];
                                                     if (marker) {
                                                         marker.setVisible(true);
                                                     }
                                                 }
                                         );
                                     },
                                     function(tr) {
                                         //tr.find(":first-child").each(
                                         tr.find(":nth-child(2)").each(
                                                 function() {
                                                     var marker = ys.markers[this.innerHTML];
                                                     if (marker) {
                                                         marker.setVisible(false);
                                                     }
                                                 }
                                         );
                                     });
            }
            else {
                // Filter the table
                jQuery.uiTableFilter(theTable, this.value);
                // Show markers for visible table rows
                jQuery("#yardsale_table > tbody > tr:visible > td").each(
                        function() {
                            var marker = ys.markers[this.innerHTML];
                            if (marker) {
                                marker.setVisible(true);
                            }
                        });

                // Hide markers for hidden table rows
                jQuery("#yardsale_table > tbody > tr:hidden > td").each(
                        function() {
                            var marker = ys.markers[this.innerHTML];
                            if (marker) {
                                marker.setVisible(false);
                            }
                        });

            }
        });
    };

    this.init = function() {
        this.initGoogleMap();
        this.initKeyFilter();
    };


    this.toggleVisibleFromCheckbox = function() {
        var showMapChecked = jQuery("#show_map").attr('checked');
        var showTableChecked = jQuery("#show_table").attr('checked');
        if (showMapChecked && showTableChecked) {
            jQuery('#map_div').width('48%');
            jQuery('#table_div').width('48%');
            jQuery('#map_div').show();
            jQuery('#table_div').show();
        }
        else if (showMapChecked) {
            jQuery('#table_div').hide();
            jQuery('#map_div').width('100%');
            jQuery('#map_div').show();
        }
        else if (showTableChecked) {
            jQuery('#map_div').hide();
            jQuery('#table_div').width('100%');
            jQuery('#table_div').show();
        }
        else {
            jQuery('#map_div').hide();
            jQuery('#table_div').hide();
        }
    };

}

