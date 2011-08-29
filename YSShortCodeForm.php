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
include_once('YSPlugin.php');

class YSShortCodeForm extends YSShortCodeLoader {

    /** @var YSPlugin */
    var $plugin;

    /** @var string */
    var $messageToUser = '';

    /** @var bool */
    var $showForm = true;

    /** @var array */
    var $data = array(
        'email' => '',
        'street' => '',
        'unit' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'listing' => '',
        'latlng' => ''
    );

    /** @var string */
    var $formJS;

    /** @var string */
    var $formId;

    /** @var string */
    var $mapId;

    /** @var string */
    var $lat = '39.01684968083152';

    /** @var string */
    var $lng = '-77.51137733459473';

    /** @var string */
    var $mapHeight = '500px';

    /** @var string */
    var $mapWidth = '100%';

    /** @var string */
    var $zoom = '14';

    /** @var array ($key => array($value)) options for city, state, zip */
    var $formOptions = array();

    /** @var array ($key => $value) defaults for city, state, zip */
    var $formDefaults = array(
        'city' => '',
        'state' => '',
        'zip' => ''
    );

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

        // Form Options
        if (isset($atts['city'])) {
            $this->formOptions['city'] = explode(",", $atts['city']);
        }
        if (isset($atts['state'])) {
            $this->formOptions['state'] = explode(",", $atts['state']);
        }
        if (isset($atts['zip'])) {
            $this->formOptions['zip'] = explode(",", $atts['zip']);
        }

        // Form Defaults
        if (isset($atts['citydefault'])) {
            $this->formDefaults['city'] = $atts['citydefault'];
        }
        if (isset($atts['statedefault'])) {
            $this->formDefaults['state'] = $atts['statedefault'];
        }
        if (isset($atts['zipdefault'])) {
            $this->formDefaults['zip'] = $atts['zipdefault'];
        }

        ob_start();

        //        echo "\n<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>\n";
        //        echo "\n<script type='text/javascript' src='" . plugins_url('js/YSFormJS.js', __FILE__) . "'></script>\n";

        $suffix = $this->generateTimeStampString();
        $this->formJS = 'ysFormJs_' . $suffix;
        $this->formId = 'ysForm_' . $suffix;
        $this->mapId = 'ysMap_' . $suffix;

        echo "\n<script type=\"text/javascript\">\n";
        printf('    var %s = new YSFormJS("%s", "%s", %s, %s, %s);',
               $this->formJS,
               $this->formId,
               $this->mapId,
               $this->lat,
               $this->lng,
               $this->zoom);
        echo "\n</script>";

        // Inject CSS. By the time this code is executed, the header is already sent, so
        // we have to in-line the CSS
        //        echo '<link rel="stylesheet" href="' . plugins_url('css/form.css', __FILE__). '" type="text/css" media="all" />' . "\n";

        $this->handleFormSubmission($event);
        $this->outputForm();

        $retVal = ob_get_contents();
        ob_end_clean();
        return $retVal;
    }


    /**
     * @param $event string
     * @return void
     */
    public function handleFormSubmission($event) {
        if (!$this->plugin) {
            $this->plugin = new YSPlugin();
        }

        global $wpdb;
        $this->messageToUser = '';
        $this->showForm = true;
        $tableName = $this->plugin->getTableName();

        // PROCESS FORM SUBMISSION
        //print_r($_POST); die;

        if (isset($_POST['_wpnonce']) &&
            isset($_POST['email']) &&
            isset($_POST['street']) && // no unit is OK
            isset($_POST['city']) &&
            isset($_POST['state']) &&
            isset($_POST['zip']) &&
            isset($_POST['listing']) &&
            isset($_POST['latlng'])
        ) {

            $nonce = $_POST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'yardsale')) die('Security check');

            $matches = array();
            if (preg_match("/\((.+), (.+)\)/", $_POST['latlng'], $matches)) { // e.g. (39.006579, -77.516362)
                $lat = $matches[1];
                $lng = $matches[2];
            }
            else {
                die('No lat/lng');
            }

            $email = $_POST['email'];
            $street = $_POST['street'];
            $unit = $_POST['unit'];
            $city = $_POST['city'];
            $state = $_POST['state'];
            $zip = $_POST['zip'];
            $listing = $_POST['listing'];

            $ip = ($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

            if ($_POST['ysid'] != "") {
                // Update database entry
                $ysid = $wpdb->escape($_POST['ysid']);
                $wpdb->show_errors(); // debug
                $rows = $wpdb->query("UPDATE `$tableName` set
            `email` = '$email', `event`='$event', `lat` = '$lat', `lng` = '$lng',
            `street` = '$street', `unit` = '$unit', `city` = '$city', `state` = '$state', `zip` = '$zip',
            `ip` = '$ip',
            `listing` = '$listing'
        WHERE `id` = '$ysid'");

                $this->messageToUser = $rows ? __('Your entry has been updated', 'yardsale')
                        : __('There was a problem updating your entry. Try again or contact an administrator');
                $this->showForm = false;
            }
            else {
                $time = $this->generateTimeStampString(); // use this as the the key for the entry
                $wpdb->show_errors(); // debug

                // check for duplicate address entry
                $rows = $wpdb->get_results("SELECT * from `$tableName` where `event` = '$event' and upper(street) = upper('$street') and upper(unit) = upper('$unit') and upper(city) = upper('$city') and upper(state) = upper('$state') and upper(zip) = upper('$zip') ");
                if ($rows) {
                    $this->messageToUser = __('There is already an entry for that address. ' .
                                              'If you are updating an entry, use the link sent to you in an email when you first entered your listing. ' .
                                              'If you share the address, use a different unit number', 'yardsale');

                }
                else {

                    // insert into database
                    $rows = $wpdb->query("INSERT INTO `$tableName`
                (`id`, `email`, `event`, `lat`, `lng`, `street`, `unit`, `city`, `state`, `zip`, `ip`, `listing`) VALUES
                ('$time', '$email', '$event', '$lat', '$lng', '$street', '$unit', '$city', '$state', '$zip', '$ip', '$listing')");


                    if ($rows) {
                        $this->messageToUser = __('Your listing has been saved. An email will be send to you with a link you can use to edit your entry if you need to.', 'yardsale');

                        $editUrl = get_permalink();
                        $editUrl .= (strpos($editUrl, '?') === false) ? "?ysid=$time" : "&ysid=$time";

                        $deleteUrl = $this->plugin->getDeleteIdUrl() . $time;

                        $msg = '<p>' . __('Thank you for your community yard sale entry. Use the following links to edit or delete your entry.', 'yardsale') . '</p>' .
                                "<a href=\"$editUrl\">" . __('Edit Yard Sale Entry', 'yardsale') . '</a><br/><br/>' .
                                "<a href=\"$deleteUrl\">" . __('Delete Yard Sale Entry', 'yardsale') . '</a><br/><br/>';

                        $headers = array('From: ' . __('Yard Sale No-Reply', 'yardsale') .
                                         ' <no-reply@' . $this->plugin->getEmailDomain() . '>' ,
                                         'Content-Type: text/html');
                        $h = implode("\r\n", $headers) . "\r\n";
                        wp_mail($_POST['email'], __('Yard Sale Entry', 'yardsale'), $msg, $h);
                    }
                    else {
                        $this->messageToUser = __('There was a problem saving your entry. Try again or contact an administrator', 'yardsale');
                    }
                }

                $this->showForm = false;
            }

        } // END PROCESS FORM SUBMISSION


        // IF UPDATE LINK WAS CLICKED, PULL UP DATA TO PRE-POPULATE FORM
        if (isset($_GET['ysid'])) {
            $id = $_GET['ysid'];
            $wpdb->show_errors(); // debug
            $rows = $wpdb->get_results("select * from `$tableName` where `id` = '$id'");
            if ($rows && count($rows) == 1) {
                $this->data['ysid'] = $_GET['ysid'];
                //$this->data['latlng'] = // don't need this, it gets regenerated
                $this->data['email'] = $rows[0]->email;
                $this->data['street'] = $rows[0]->street;
                $this->data['unit'] = $rows[0]->unit;
                $this->data['city'] = $rows[0]->city;
                $this->data['state'] = $rows[0]->state;
                $this->data['zip'] = $rows[0]->zip;
                $this->data['listing'] = $rows[0]->listing;
            }
        }
        else {
            // SET DEFAULTS ON THE FORM
            $this->data['city'] = $this->formDefaults['city'];
            $this->data['state'] = $this->formDefaults['state'];
            $this->data['zip'] = $this->formDefaults['zip'];
        }

        //        $ysDateArray = get_post_custom_values('yardsale-date', 2447);
        //        $ysDateText = "";
        //        if ($ysDateArray[0]) {
        //            $ysDateText = $ysDateArray[0];
        //        }

        echo $this->messageToUser;
    }


    /**
     * @return void output echoed
     */
    public function outputForm() {

        if (!$this->plugin) {
            $this->plugin = new YSPlugin();
        }


        // IF NO SUBMISSION ERRORS, THEN SHOW THE FORM
        if ($this->showForm) {
            ?>
        <div class="entry_div">
            <p>
                <?php _e('If you wish to edit a listing that you have already made, use the link sent to you in email when you created it.', 'yardsale'); ?>
            </p>

            <form id="<?php echo $this->formId ?>" action="" method="post">
                <?php wp_nonce_field('yardsale'); ?>
                <input name="ysid" type="hidden" value="<?php echo $this->data['ysid'] ?>"/>
                <input name="latlng" type="hidden" value="<?php echo $this->data['latlng'] ?>"/>
                <table cellpadding="0px" cellspacing="0px">
                    <tbody>
                    <tr>
                        <td><label for="email"><?php _e('Email', 'yardsale') ?>*</label></td>
                        <td><input name="email" id="email" type="text" size="30"
                                   value="<?php echo $this->data['email'] ?>"
                                   onblur="<?php echo $this->formJS ?>.fetchLatLong()"/>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="street"><?php _e('Street Address', 'yardsale') ?>*</label></td>
                        <td><input name="street" id="street" type="text" size="30"
                                   value="<?php echo $this->data['street'] ?>"
                                   onblur="<?php echo $this->formJS ?>.fetchLatLong()"/></td>
                    </tr>
                    <tr>
                        <td><label for="unit"><?php _e('Unit/Apartment', 'yardsale') ?></label></td>
                        <td><input name="unit" id="unit" type="text" size="5"
                                   value="<?php echo $this->data['unit'] ?>"
                                   onblur="<?php echo $this->formJS ?>.fetchLatLong()"/></td>
                    </tr>
                    <tr>
                        <?php $this->outputFieldWithOptionsAndDefaults('city', __('City', 'yardsale'), '30') ?>
                    </tr>
                    <tr>
                        <?php $this->outputFieldWithOptionsAndDefaults('state', __('State', 'yardsale'), '2') ?>
                    </tr>
                    <tr>
                        <?php $this->outputFieldWithOptionsAndDefaults('zip', __('Zip', 'yardsale'), '10') ?>
                    </tr>
                    </tbody>
                </table>
                <label for="listing"><?php _e('For Sale Items', 'yardsale') ?>*</label><br/>
                <textarea name="listing" id="listing" rows="10"
                          cols="30"><?php echo $this->data['listing'] ?></textarea>
                <br/>
                <input onclick="<?php echo $this->formJS ?>.fetchLatLong(); return <?php echo $this->formJS ?>.validate();"
                       type="submit" value="Submit"/>
            </form>
        </div>
        <div class="map_div">
            <div class="map_canvas" id="<?php echo $this->mapId ?>"
                 style="height: <?php echo $this->mapHeight ?>; width: <?php echo $this->mapWidth ?>"></div>
        </div>
        <script type="text/javascript">
                <?php echo $this->formJS ?>.initGoogleMap();
        </script>

        <?php

        } // $this->showForm
    }

    public function outputFieldWithOptionsAndDefaults($field, $label, $textFieldSize) {
        ?>
    <td><label for="<?php echo $field ?>"><?php echo $label ?>*</label></td>
    <td>
        <?php
        if (empty($this->formOptions[$field])) {
            // Output a plain text field
            ?>
            <input name="<?php echo $field ?>"
                   id="<?php echo $field ?>"
                   type="text"
                   size="<?php echo $textFieldSize ?>"
                   value="<?php echo $this->data[$field] ?>"
                   onblur="<?php echo $this->formJS ?>.fetchLatLong()"/>
            <?php
        }
        else {
            // Output a select tag
            ?>
            <select name="<?php echo $field ?>" id="<?php echo $field ?>" onchange="<?php echo $this->formJS ?>.fetchLatLong()">
            <?php
            foreach ($this->formOptions[$field] as $val) {
                ?>
                <option value="<?php echo $val ?>" <?php echo ($this->data[$field] == $val ? "selected" : "") ?>><?php echo $val ?></option>
                <?php
            }
            ?>
            </select>
            <?php
        }
        ?>
    </td>
        <?php
    }

    public function generateTimeStampString() {
        return str_replace('.', '_', (function_exists('microtime') ? microtime(true) : time()));
    }
}
