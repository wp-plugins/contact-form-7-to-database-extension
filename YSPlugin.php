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

include_once('YSLifeCycle.php');

class YSPlugin extends YSLifeCycle {

    public function getOptionMetaData() {
        return array(
            //'_version' => array('Installed Version'), // For testing upgrades
            'DropOnUninstall' => array(__('Drop this plugin\'s Database table on uninstall', 'yardsale'), 'false', 'true')
        );
    }

    public function getPluginDisplayName() {
        return 'Yard Sale';
    }


    /**
     * @return string name of the main plugin file that has the header section with
     * "Plugin Name", "Version", "Description", "Text Domain", etc.
     */
    protected function getMainPluginFileName() {
        return 'yardsale.php';
    }

    /**
     * @return string
     */
    public function getTableName() {
        global $wpdb;
        return $wpdb->prefix . 'yardsale';
    }


    protected function installDatabaseTables() {
        global $wpdb;
        $wpdb->query('CREATE TABLE IF NOT EXISTS `' . $this->getTableName() . '` (
        `id` VARCHAR(64) NOT NULL,
        `event` VARCHAR(64),
        `email` VARCHAR(64),
        `lat` VARCHAR(64) NOT NULL,
        `lng` VARCHAR(64) NOT NULL,
        `street` VARCHAR(128),
        `unit` VARCHAR(64),
        `city` VARCHAR(128),
        `state` VARCHAR(32),
        `zip` VARCHAR(32),
        `ip` VARCHAR(64),
        `listing` LONGTEXT,
        PRIMARY KEY (`id`))');
    }

    protected function unInstallDatabaseTables() {
        if ('true' == $this->getOption('DropOnUninstall', 'false')) {
            global $wpdb;
            $tableName = $this->getTableName();
            $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
        }
    }

    public function addActionsAndFilters() {

        // Add Config page into the Plugins menu
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Register JSON data URL
        add_action('wp_ajax_nopriv_yardsale', array(&$this, 'ajaxGetJson'));
        add_action('wp_ajax_yardsale', array(&$this, 'ajaxGetJson'));

        // Register delete data operations
        add_action('wp_ajax_yardsale-delete-id', array(&$this, 'ajaxDeleteId')); // for Setting page
        add_action('wp_ajax_nopriv_yardsale-delete-id', array(&$this, 'ajaxDeleteId')); // for links emailed to users
        add_action('wp_ajax_yardsale-delete-event', array(&$this, 'ajaxDeleteEvent')); // only for Setting page

        // Add scripts required by the short codes
        wp_enqueue_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
        wp_enqueue_script('yardsale-YSFormJS', plugins_url('/js/YSFormJS.js', __FILE__));
        wp_enqueue_script('jquery');
        wp_enqueue_script('yardsale-uitablefilter', plugins_url('/js/jquery.uitablefilter.js', __FILE__), array('jquery'));

        // TURNED OFF AUTO SCROLLING IN TABLE - see YSListing.js
        //wp_enqueue_script('yardsale-ScrollToAnchor', plugins_url('/js/ScrollToAnchor.js', __FILE__));

        wp_enqueue_script('yardsale-YSListing', plugins_url('/js/YSListing.js', __FILE__), array('jquery'));

        // Needed for the Settings Page
        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
            wp_enqueue_style('jquery-ui', plugins_url('/css/jquery-ui.css', __FILE__));
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-accordion'); // not installed by default
            wp_enqueue_script('yardsale-admin-delete', plugins_url('/js/ysdelete.js', __FILE__));
            wp_enqueue_script('yardsale-admin-shortcodebuilder', plugins_url('/js/ysshortcodebuilder.js', __FILE__));
        }

        // Register short codes
        include_once('YSShortCodeForm.php');
        $sc = new YSShortCodeForm();
        $sc->register('yardsale-form');

        include_once('YSShortCodeListing.php');
        $sc = new YSShortCodeListing();
        $sc->register('yardsale-listing');

    }

    public function ajaxGetJson() {
        header("Content-type: application/json");

        // Don't let IE cache this request
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

        $event = 'Untitled';
        if (isset($_REQUEST['event'])) {
            $event = $_REQUEST['event'];
        }

        $listings = array();
        global $wpdb;
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE `event` = %s ORDER BY SUBSTRING( street, LOCATE( ' ', street ) +1 )";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $event));
        foreach ($rows as $aRow) {
            $address = ($aRow->unit && $aRow->unit != "") ?
                    sprintf("%s #%s, %s %s %s",
                            $aRow->street, $aRow->unit, $aRow->city, $aRow->state, $aRow->zip) :
                    sprintf("%s, %s %s %s",
                            $aRow->street, $aRow->city, $aRow->state, $aRow->zip);

            $aListing = array($aRow->lat, $aRow->lng, htmlspecialchars($address), htmlspecialchars($aRow->listing));
            $listings[] = $aListing;
        }
        echo json_encode($listings);
        die();
    }

    public function settingsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'yardsale'));
        }

        //http://www.quirksmode.org/css/clearing.html
        ?>
    <div style="overflow: auto; width: 100%">
        <div style="float:left;">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="F3FF6MP948QPW">
                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0"
                       name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </div>
        <div style="float:right; text-align: right; margin-right: 50px; margin-top: 15px; font-weight: bold;">
            <a target="_blank"
               href="http://wordpress.org/tags/community-yard-sale">
                <?php _e('Support for this plugin', 'yardsale') ?>
            </a>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(function() {
            jQuery("#yardsale_config_tabs").tabs();
        });
    </script>

    <div class="yardsale_config">
        <div id="yardsale_config_tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul>
                <li><a href="#yardsale_config-1">Build Short Codes</a></li>
                <li><a href="#yardsale_config-2">Delete Entry</a></li>
                <li><a href="#yardsale_config-3">Options</a></li>
            </ul>
            <div id="yardsale_config-1">
                <?php $this->outputShortCodeBuilder(); ?>
            </div>
            <div id="yardsale_config-2">
                <?php $this->outputDeleteForms(); ?>
            </div>
            <div id="yardsale_config-3">
                <?php parent::settingsPage(); ?>
            </div>
        </div>

    </div>
    <?php

    }

    public function outputDeleteForms() {
        ?>
    <p>
        <?php _e('Delete all entries associated with an event tag', 'yardsale'); ?>
    </p>
    <p>
        <label for="ysevent">Event</label>
        <select id="ysevent">
            <option value=""></option>
            <?php
            global $wpdb;
            $sql = "SELECT  `event` , COUNT(  `event` ) AS  'count' FROM  `" . $this->getTableName() .
                   "` GROUP BY  `event` ORDER BY  `event` ";
            $rows = $wpdb->get_results($sql);
            foreach ($rows as $aRow) {
                echo "<option value=\"$aRow->event\">$aRow->event ($aRow->count)</option>";
            }
            ?>
        </select>
        <button id="yseventbutton" type="button"
                onclick="ysDelete('<?php echo $this->getDeleteEventUrl() ?>', '#ysevent', '#yseventbutton', '#ysdeleteeventresults')">
            Delete
        </button>
    </p>
    <p id="ysdeleteeventresults" style="color: red; font-style: italic;"></p>

    <p style="margin-top: 2em;">
        <?php _e('Enter the Yard Sale ID of the entry you wish to delete. (Example: 1314383685_9253)<br/>
    This ID appears in the edit URL sent to the user after he registers (Example: http://&lt;page-url&gt;?ysid=<strong>1314383685_9253</strong>)', 'yardsale'); ?>
    </p>
    <p>
        <label for="ysid">ID</label>
        <input id="ysid" type="text" size="20"/>
        <button id="ysidbutton" type="button"
                onclick="ysDelete('<?php echo $this->getDeleteIdUrl() ?>', '#ysid', '#ysidbutton', '#ysdeleteidresults')">
            Delete
        </button>
    </p>
    <p id="ysdeleteidresults" style="color: red; font-style: italic;"></p>
            <?php

    }

    public function ajaxDeleteId() {
        header("Content-type: text/plain");

        // Don't let IE cache this request
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

        if (!empty($_REQUEST['id'])) {
            global $wpdb;
            $sql = "DELETE FROM " . $this->getTableName() . " WHERE `id` = %s LIMIT 1";
            $result = $wpdb->query($wpdb->prepare($sql, $_REQUEST['id']));
            echo ($result === false) ? __('MySQL error', 'yardsale') : "$result " . __('row(s) deleted', 'yardsale');
        }
        else {
            _e('Error: No ID given', 'yardsale');
        }
        die();
    }

    public function ajaxDeleteEvent() {
        header("Content-type: text/plain");

        // Don't let IE cache this request
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

        if (!empty($_REQUEST['event'])) {
            global $wpdb;
            $sql = "DELETE FROM " . $this->getTableName() . " WHERE `event` = %s";
            $result = $wpdb->query($wpdb->prepare($sql, $_REQUEST['event']));
            echo ($result === false) ? __('MySQL error', 'yardsale') : "$result " . __('row(s) deleted', 'yardsale');
        }
        else {
            _e('Error: No event tag given', 'yardsale');
        }
        die();
    }

    public function getDeleteIdUrl() {
        return sprintf('%s?action=yardsale-delete-id&id=',
                       admin_url('admin-ajax.php'));
    }

    public function getDeleteEventUrl() {
        return sprintf('%s?action=yardsale-delete-event&event=',
                       admin_url('admin-ajax.php'));
    }


    public function outputShortCodeBuilder() {
        ?>
    <h3><?php _e('Fill in the options below to generate short codes to put on your pages', 'yardsale') ?></h3>
    <p>
        <?php _e('Place the [yardsale-form] short code on a page to display the yard sale entry form', 'yardsale') ?>
    </p>

    <div id="sc_form_result_div">
        <pre style="white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"><code
                id="sc_form_result_text">[yardsale-form]</code></pre>
    </div>

    <p>
        <?php _e('Place the [yardsale-listing] short code on a <strong>different page</strong> to display the yard sale listings', 'yardsale') ?>
    </p>
    <div id="sc_listing_result_div">
        <pre style="white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"><code
                id="sc_listing_result_text">[yardsale-listing]</code></pre>
    </div>

    <div id="validations_div">
        <span id="validations_text" style="background-color:#ffff66;"></span>
    </div>
    <hr style="margin-top: 2em;">



    <script type="text/javascript">
        jQuery(function() {
            try {
                jQuery("#accordion").accordion();
            }
            catch (ex) {
            }
        });
    </script>

    <div id="accordion">

        <h3><a href="#"><?php _e('Event Tag', 'yardsale') ?></a></h3>

        <div>
            <p><?php _e('Give your event a unique tag. This is used to distinguish different community yard sale events. Your pair of short codes will need to have the same event tag.', 'yardsale') ?></p>

            <p>
                <label for="sc_event"><?php _e('Event Tag', 'yardsale') ?></label>
                <input id="sc_event" type="text" size="30" value="yardsale" onkeyup="ysCreateShortCodes()"/>
            </p>
        </div>

        <h3><a href="#"><?php _e('Map Size', 'yardsale') ?></a></h3>

        <div>
            <p><?php _e('Set the Google Map height and width. Use pixels or percentage width.', 'yardsale') ?></p>
            <ul>
                <li><?php _e('Warning: if both height and width are percentages then the map may not display', 'yardsale'); ?></li>
                <li><?php _e('Setting a height in pixels and a width=100% is a common choice', 'yardsale'); ?></li>
                <li><?php _e('Note: You can tweak these values later directly in the short code after you have put the short code on a page and looked at it', 'yardsale'); ?></li>
            </ul>

            <table cellspacing="10px">
                <tr>
                    <td><label for="sc_form_map_height"><?php _e('Form Map Height', 'yardsale') ?></label></td>
                    <td><input id="sc_form_map_height" type="text" size="10" value="500px"
                               onkeyup="ysCreateShortCodes()"/></td>
                    <td><label for="sc_form_map_width"><?php _e('Form Map Width', 'yardsale') ?></label></td>
                    <td><input id="sc_form_map_width" type="text" size="10" value="100%"
                               onkeyup="ysCreateShortCodes()"/></td>
                </tr>
                <tr>
                    <td><label for="sc_listing_map_height"><?php _e('Listing Map Height', 'yardsale') ?></label></td>
                    <td><input id="sc_listing_map_height" type="text" size="10" value="500px"
                               onkeyup="ysCreateShortCodes()"/></td>
                    <td><label for="sc_listing_map_width"><?php _e('Listing Map Width', 'yardsale') ?></label></td>
                    <td><input id="sc_listing_map_width" type="text" size="10" value="100%"
                               onkeyup="ysCreateShortCodes()"/></td>
                </tr>
            </table>
        </div>

        <h3><a href="#"><?php _e('Map Location', 'yardsale') ?></a></h3>

        <div>
            <?php
                    // The White House as a generic starting location
            $centerLat = '38.897678';
            $centerLng = '-77.036517';
            $zoom = '14';
            ?>
            <script type="text/javascript">
                var scMap = new ScGoogleMap();
                scMap.initGoogleMap(<?php echo $centerLat ?>, <?php echo $centerLng ?>, <?php echo $zoom ?>);
            </script>

            <p><?php _e('Center The Google Map to display your community yard sale area.', 'yardsale') ?></p>
            <ul>
                <li><?php _e('Type in an address', 'yardsale'); ?></li>
                <li><?php _e('Click on the map to adjust the center', 'yardsale'); ?></li>
            </ul>
            <p>
                <label for="sc_address"><?php _e('Address', 'yardsale') ?></label>
                <input id="sc_address" type="text" size="50"
                       onchange="scMap.centerMapOnAddress(jQuery('#sc_address').val())"/>
                <button onclick="scMap.centerMapOnAddress(jQuery('#sc_address').val())"><?php _e('Center Map', 'yardsale') ?></button>
            </p>

            <div id="map_div">
                <div id="map_canvas" style="height: 400px; width: 400px"></div>
            </div>


            <p>
                <label for="sc_lat"><?php _e('Latitude', 'yardsale') ?></label>
                <input id="sc_lat" type="text" size="20" value="<?php echo $centerLat ?>" onkeyup="ysCreateShortCodes()"
                       onchange="scMap.centerMapOnLatLng()"/>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label for="sc_lng"><?php _e('Longitude', 'yardsale') ?></label>
                <input id="sc_lng" type="text" size="20" value="<?php echo $centerLng ?>" onkeyup="ysCreateShortCodes()"
                       onchange="scMap.centerMapOnLatLng()"/>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label for="sc_zoom"><?php _e('Zoom', 'yardsale') ?></label>
                <input id="sc_zoom" type="text" size="5" value="<?php echo $zoom ?>" onchange="scMap.zoomMap()"/>
            </p>
        </div>


        <h3><a href="#"><?php _e('Input Form Pick Lists (Optional)', 'yardsale') ?></a></h3>

        <div>
            <p><?php _e('Optional: if you want any of City, State, or Zip fields to be a pick-list on the entry form instead of a text field, enter a comma-delimited list of values.', 'yardsale') ?></p>

            <table cellspacing="10px">
                <tr>
                    <td><label for="sc_city"><?php _e('City', 'yardsale') ?></label></td>
                    <td><input id="sc_city" type="text" size="50" onkeyup="ysCreateShortCodes()"/></td>
                </tr>
                <tr>
                    <td><label for="sc_state"><?php _e('State', 'yardsale') ?></label></td>
                    <td><input id="sc_state" type="text" size="50" onkeyup="ysCreateShortCodes()"/></td>
                    <td><?php _e('Example: "DC,MD,VA"', 'yardsale') ?></td>
                </tr>
                <tr>
                    <td><label for="sc_zip"><?php _e('Zip', 'yardsale') ?></label></td>
                    <td><input id="sc_zip" type="text" size="50" onkeyup="ysCreateShortCodes()"/></td>
                    <td><?php _e('Example: "12345,12346,12347"', 'yardsale') ?></td>
                </tr>
            </table>
        </div>


        <h3><a href="#"><?php _e('Input Form Defaults (Optional)', 'yardsale') ?></a></h3>

        <div>
            <p><?php _e('Optional: if you would like the entry form to pre-populate fields with default values, enter them here. If you are also setting field to be pick-lists, be sure that the values you put in this section are included in the comma-delimited list. That option will be selected by default in the pick-list', 'yardsale') ?></p>

            <table cellspacing="10px">
                <tr>
                    <td><label for="sc_citydefault"><?php _e('City', 'yardsale') ?></label></td>
                    <td><input id="sc_citydefault" type="text" size="40" onkeyup="ysCreateShortCodes()"/></td>
                </tr>
                <tr>
                    <td><label for="sc_statedefault"><?php _e('State', 'yardsale') ?></label></td>
                    <td><input id="sc_statedefault" type="text" size="20" onkeyup="ysCreateShortCodes()"/></td>
                </tr>
                <tr>
                    <td><label for="sc_zipdefault"><?php _e('Zip', 'yardsale') ?></label></td>
                    <td><input id="sc_zipdefault" type="text" size="20" onkeyup="ysCreateShortCodes()"/></td>
                </tr>
            </table>
        </div>

    </div>

    <h3><a href="#"><?php _e('Hide On Print (Optional)', 'yardsale') ?></a></h3>

    <div>
        <p><?php _e('Optional: When printing a listing page, you may wish to hide certain HTML DIVs (such as a header) to save paper. Put a comma-delimited list of HTML IDs of to hide. This is not guaranteed to work.', 'yardsale') ?></p>
        <p>
            <label for="sc_hideonprint"><?php _e('HTML IDs to hide on print', 'yardsale') ?></label>
            <input id="sc_hideonprint" type="text" size="50" onkeyup="ysCreateShortCodes()" />
        </p>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            ysCreateShortCodes();
        });
    </script>

            <?php

    }

}
