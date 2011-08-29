<?php
/*
    "Community Yard Sale Plugin for WordPress" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Community Yard Sale Plugin for WordPress.

    Community Yard Sale Plugin for WordPress is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Community Yard Sale Plugin for WordPress is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Community Yard Sale Plugin for WordPress.
    If not, see <http://www.gnu.org/licenses/>.
*/

include_once('YSShortCodeLoader.php');

class YSShortCodeListing extends YSShortCodeLoader {

    /** @var string */
    var $lat = '38.897678';

    /** @var string */
    var $lng = '-77.036517';

    /** @var string */
    var $mapHeight = '500px';

    /** @var string */
    var $mapWidth = '100%';

    /** @var string */
    var $zoom = '14';

    /** @var string comma-delimited html id's */
    var $hideOnPrint;

    /**
     * @param  $atts shortcode inputs
     * @return string shortcode content
     */
    public function handleShortcode($atts) {

        $event = 'Untitled';
        if (isset($atts['event'])) {
            $event = $atts['event'];
        }
        if (isset($atts['mapheight'])) {
            $this->mapHeight = $atts['mapheight'];
        }
        if (isset($atts['mapwidth'])) {
            $this->mapWidth = $atts['mapwidth'];
        }

        if (isset($atts['lat'])) {
            $this->lat = $atts['lat'];
        }
        if (isset($atts['lng'])) {
            $this->lng = $atts['lng'];
        }
        if (isset($atts['zoom'])) {
            $this->zoom = $atts['zoom'];
        }

        if (isset($atts['hideonprint'])) {
            $this->hideOnPrint = $atts['hideonprint'];
        }

        ob_start();

        echo '<link rel="stylesheet" href="' . plugins_url('css/listing.css', __FILE__) . '" type="text/css" media="all" />' . "\n";

        $this->outputTableAndMap($event);

        $retVal = ob_get_contents();
        ob_end_clean();
        return $retVal;

    }

    public function outputTableAndMap($event) {
        if ($this->hideOnPrint) {
            $hideIds = explode(',', $this->hideOnPrint);
            if (count($hideIds) > 0) {
                echo '<style type="text/css">' . "\n";
                echo '@media print {' . "\n";
                foreach ($hideIds as $id) {
                    if ($id) {
                        echo "    #$id { display: none; !important }\n";
                        // todo: output a style
                    }
                }
                echo "}\n</style>\n";
            }
        }

        ?>
    <div id="filter-form">Search: <input name="filter" id="filter" value="" size="30" type="text"></div>
    <p style="margin:0px">&nbsp;</p>
    <?php /*
    <div id="show-form">
        Show:
        <input type="checkbox" name="show_table" id="show_table" onclick="ys.toggleVisibleFromCheckbox()" CHECKED>Table
        &nbsp;&nbsp;&nbsp;
        <input type="checkbox" name="show_map" id="show_map" onclick="ys.toggleVisibleFromCheckbox()" CHECKED>Map
    </div>
    */ ?>
    <div id="map_div">
        <div id="map_canvas" style="height: <?php echo $this->mapHeight ?>; width: <?php echo $this->mapWidth ?>"></div>
    </div>

    <div id="table_div">
        <table id="yardsale_table" border="1" cellspacing="0">
            <thead>
            <tr>
                <th></th>
                <th>Address</th>
                <th>Items<br/><span style="font-size:8pt">(Click table cell to view on map)</span></th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>


    <script type="text/javascript">
        var ys = new YSListing(<?php printf('%s, %s, %s, "%s", "%s"',
                                            $this->lat,
                                            $this->lng,
                                            $this->zoom,
                                            plugins_url('markers/', __FILE__),
                                            $this->getJsonUrl($event)); ?>);
        ys.initGoogleMap();
        ys.initKeyFilter();
    </script>
    <?php

    }


    public function getJsonUrl($event) {
        return sprintf('%s?action=yardsale&event=%s',
                       admin_url('admin-ajax.php'),
                       urlencode($event));
    }

}
