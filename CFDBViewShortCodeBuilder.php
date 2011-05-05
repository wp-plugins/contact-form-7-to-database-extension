<?php
/*
    "Contact Form to Database Extension" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database Extension.

    Contact Form to Database Extension is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database Extension is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
*/

require_once('CF7DBPlugin.php');
require_once('CFDBView.php');

class CFDBViewShortCodeBuilder extends CFDBView {

    /**
     * @param  $plugin CF7DBPlugin
     * @return void
     */
    function display(&$plugin) {
        if ($plugin == null) {
            $plugin = new CF7DBPlugin;
        }
        $this->pageHeader($plugin);

        // Identify which forms have data in the database
        global $wpdb;
        $tableName = $plugin->getSubmitsTableName();
        $rows = $wpdb->get_results("select distinct `form_name` from `$tableName` order by `form_name`");
        //        if ($rows == null || count($rows) == 0) {
        //            _e('No form submissions in the database', 'contact-form-7-to-database-extension');
        //            return;
        //        }
        $currSelection = ''; // todo
        ?>
    <script type="text/javascript" language="JavaScript">

        var shortCodeDocUrls = {
            "" : 'http://cfdbplugin.com/?page_id=89',
            "[cfdb-html]" : "http://cfdbplugin.com/?page_id=284",
            "[cfdb-table]" : "http://cfdbplugin.com/?page_id=93",
            "[cfdb-datatable]" : "http://cfdbplugin.com/?page_id=91",
            "[cfdb-value]" : "http://cfdbplugin.com/?page_id=98",
            "[cfdb-count]" : "http://cfdbplugin.com/?page_id=278",
            "[cfdb-json]" : "http://cfdbplugin.com/?page_id=96"
        };

        function showHideOptionDivs() {
            var shortcode = jQuery('#shortcode').val();
            jQuery('#doc_url_tag').attr('href', shortCodeDocUrls[shortcode]);
            jQuery('#doc_url_tag').html(shortcode + " <?php _e('Documentation', 'contact-form-7-to-database-extension'); ?>");
            switch (shortcode) {
                case "[cfdb-html]":
                    jQuery('#html_format_div').hide();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').show();
                    break;
                case "[cfdb-table]":
                    jQuery('#html_format_div').show();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').hide();
                    break;
                case "[cfdb-datatable]":
                    jQuery('#html_format_div').show();
                    jQuery('#dt_options_div').show();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').hide();
                    break;
                case "[cfdb-value]":
                    jQuery('#html_format_div').hide();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').show();
                    jQuery('#template_div').hide();
                    break;
                case "[cfdb-count]":
                    jQuery('#html_format_div').hide();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').hide();
                    break;
                case "[cfdb-json]":
                    jQuery('#html_format_div').hide();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').show();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').hide();
                    break;
                default:
                    jQuery('#html_format_div').hide();
                    jQuery('#dt_options_div').hide();
                    jQuery('#json_div').hide();
                    jQuery('#value_div').hide();
                    jQuery('#template_div').hide();
                    break;
            }
        }

        jQuery(document).ready(function() {
            showHideOptionDivs();
        });

    </script>
    <style type="text/css">
        div.shortcodeoptions {
            border: #ccccff groove;
            margin-bottom: 10px;
            padding: 5px;
        }
        div.shortcodeoptions label {
            font-weight: bold;
            font-family:Arial sans-serif;
            margin-top: 5px;
        }
    </style>

    <h2>Short Code Builder</h2>
    <div style="margin-bottom:10px">
        <span>
            <label for="shortcode">Short Code</label>
            <select name="shortcode" id="shortcode" onchange="showHideOptionDivs()">
                <option value=""><?php _e('* Select a short code *', 'contact-form-7-to-database-extension') ?></option>
                <option value="[cfdb-html]">[cfdb-html]</option>
                <option value="[cfdb-table]">[cfdb-table]</option>
                <option value="[cfdb-datatable]">[cfdb-datatable]</option>
                <option value="[cfdb-value]">[cfdb-value]</option>
                <option value="[cfdb-count]">[cfdb-count]</option>
                <option value="[cfdb-json]">[cfdb-json]</option>
            </select>
        </span>
        <span style="margin-left:10px">
            <label for="form_name">form</label>
            <select name="form_name" id="form_name">
                <option value=""><?php _e('* Select a form *', 'contact-form-7-to-database-extension') ?></option>
                <?php foreach ($rows as $aRow) {
                $formName = $aRow->form_name;
                $selected = ($formName == $currSelection) ? "selected" : "";
                ?>
                <option value="<?php echo $formName ?>" <?php echo $selected ?>><?php echo $formName ?></option>
                <?php } ?>
            </select>
        </span>
    </div>
        <div class="shortcodeoptions">
            <a id="doc_url_tag" target="_docs" href="http://cfdbplugin.com/?page_id=89"><?php _e('Documentation', 'contact-form-7-to-database-extension'); ?></a>
        </div>
    <div id="show_hide_div" class="shortcodeoptions">
        <?php _e('Which fields/columns do you want to display?', 'contact-form-7-to-database-extension'); ?>
        <div>
            <label for="show">show</label>
            <input name="show" id="show" type="text" size="100"/>
        </div>
        <div>
            <label for="hide">hide</label>
            <input name="hide" id="hide" type="text" size="100"/>
        </div>
    </div>
    <div id="filter_div" class="shortcodeoptions">
        <div><?php _e('Which rows/submissions do you want to display?', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <label for="search">search</label>
            <input name="search" id="search" type="text" size="30"/>
        </div>
        <div>
            <label for="filter">filter</label>
            <input name="filter" id="filter" type="text" size="100"/>
        </div>
        <div>
            <label for="limit">limit</label>
            Start Row (0)<input name="limit_start" id="limit_start" type="text" size="10"/>
            Num Rows <input name="limit" id="limit" type="text" size="10"/>
        </div>
        <div id="orderby_div">
            <label for="orderby">orderby</label>
            <input name="orderby" id="orderby" type="text" size="100"/>
            <select id="orderbydir" name="orderbydir">
                <option value=""></option>
                <option value="ASC">ASC</option>
                <option value="DESC">DESC</option>
            </select>
        </div>
    </div>
    <div id="html_format_div" class="shortcodeoptions">
        <div><?php _e('HTML Table Formatting', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <td><label for="id">id</label></td>
            <td><input name="id" id="id" type="text" size="10"/></td>
        </div>
        <div>
            <td><label for="class">class</label></td>
            <td><input name="class" id="class" type="text" size="10"/></td>
        </div>
        <div>
            <td><label for="style">style</label></td>
            <td><input name="style" id="style" type="text" size="100"/></td>
        </div>
    </div>
    <div id="dt_options_div" class="shortcodeoptions">
        <div><?php _e('DataTable Options', 'contact-form-7-to-database-extension'); ?></div>
        <label for="dt_options">dt_options</label>
        <input name="dt_options" id="dt_options" type="text" size="100"/>
    </div>
    <div id="json_div" class="shortcodeoptions">
        <div><?php _e('JSON Options', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <label for="var">var</label>
            <input name="var" id="var" type="text" size="10"/>
        </div>
        <div>
            <label for="format">format</label>
            <select id="format" name="format">
                <option value=""></option>
                <option value="map">map</option>
                <option value="array">array</option>
                <option value="arraynoheader">arraynoheader</option>
            </select>
        </div>
    </div>
    <div id="value_div" class="shortcodeoptions">
        <div><?php _e('VALUE Options', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <label for="function">function</label>
            <select id="function" name="function">
                <option value=""></option>
                <option value="min">min</option>
                <option value="max">max</option>
                <option value="sum">sum</option>
                <option value="mean">mean</option>
            </select>
        </div>
        <div>
            <label for="delimiter">delimiter</label>
            <input name="delimiter" id="delimiter" type="text" size="10"/>
        </div>
    </div>
    <div id="template_div" class="shortcodeoptions">
        <div>
            <label for="filelinks">filelinks</label>
            <select id="filelinks" name="filelinks">
                <option value=""></option>
                <option value="url">url</option>
                <option value="name">name</option>
                <option value="link">link</option>
                <option value="img">img</option>
            </select>
        </div>
        <div>
            <label for="content">Template text</label><br/>
            <textarea name="content" id="content" cols="30" rows="10"></textarea>
        </div>
    </div>

    <?php

    }
}