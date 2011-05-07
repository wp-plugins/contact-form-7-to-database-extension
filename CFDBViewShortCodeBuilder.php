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
            '' : 'http://cfdbplugin.com/?page_id=89',
            '[cfdb-html]' : 'http://cfdbplugin.com/?page_id=284',
            '[cfdb-table]' : 'http://cfdbplugin.com/?page_id=93',
            '[cfdb-datatable]' : 'http://cfdbplugin.com/?page_id=91',
            '[cfdb-value]' : 'http://cfdbplugin.com/?page_id=98',
            '[cfdb-count]' : 'http://cfdbplugin.com/?page_id=278',
            '[cfdb-json]' : 'http://cfdbplugin.com/?page_id=96'
        };

        function showHideOptionDivs() {
            var shortcode = jQuery('#shortcode_ctrl').val();
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

        function getValue(attr, value, errors) {
            if (value) {
                if (errors && value.indexOf('"') > -1) {
                    errors.push('<?php _e('Error: "', 'contact-form-7-to-database-extension'); ?>'
                                        + attr +
                                        '<?php _e('" should not contain double-quotes (")', 'contact-form-7-to-database-extension'); ?>');
                    value = value.replace('"', "'");
                }
                return attr + '="' + value + '"';
            }
            return '';
        }

        function join(arr) {
            var tmp = [];
            for (idx=0; idx<arr.length; idx++) {
                if (arr[idx] != '') {
                    tmp.push(arr[idx]);
                }
            }
            return tmp.join(' ');
        }

        function chopLastChar(text) {
            return text.substr(0, text.length - 1);
        }

        function createShortCode() {
            var scElements = [];
            var validationErrors = [];
            var shortcode = jQuery('#shortcode_ctrl').val();
            if (shortcode == '') {
                jQuery('#shortcode_result_text').html('');
                return;
            }
            scElements.push(chopLastChar(shortcode));

            var formName = jQuery('#form_name_cntl').val();
            if (!formName) {
                validationErrors.push('<?php _e('Error: no form is chosen', 'contact-form-7-to-database-extension') ?>');
            }
            else {
                scElements.push('form="' + formName + '"');
            }

            scElements.push(getValue('show', jQuery('#show_cntl').val(), validationErrors));
            scElements.push(getValue('hide', jQuery('#hide_cntl').val(), validationErrors));
            var filter = getValue('filter', jQuery('#filter_cntl').val(), validationErrors);
            if (filter) {
                scElements.push(filter);
                if (jQuery('#search_cntl').val()) {
                    validationErrors.push('<?php _e('Warning: "search" field ignored because "filter" is used (use one but not both)', 'contact-form-7-to-database-extension'); ?>');
                }
            }
            else {
                scElements.push(getValue('search', jQuery('#search_cntl').val(), validationErrors));
            }

            var limitRows = jQuery('#limit_rows_cntl').val();
            var limitStart = jQuery('#limit_start_cntl').val();
            if (limitStart && !limitRows) {
                validationErrors.push('<?php _e('Error: "limit": if you provide a value for "Start Row" then you must also provide a value for "Num Rows"', 'contact-form-7-to-database-extension'); ?>');
            }
            if (limitRows) {
                if (! /^\d+$/.test(limitRows)) {
                    validationErrors.push('<?php _e('Error: "limit": "Num Rows" must be a positive integer', 'contact-form-7-to-database-extension'); ?>');
                }
                else {
                    var limitOption = ' limit="';
                    if (limitStart) {
                        if (! /^\d+$/.test(limitStart)) {
                            validationErrors.push('<?php _e('Error: "limit": "Start Row" must be a positive integer', 'contact-form-7-to-database-extension'); ?>');
                        }
                        else {
                        limitOption += limitStart + ",";
                        }
                    }
                    limitOption += limitRows;
                    scElements.push(limitOption + '"');
                }
            }
            var orderByElem = getValue('orderby', jQuery('#orderby_cntl').val(), validationErrors);
            if (orderByElem) {
                var orderByDir = jQuery('#orderbydir_cntl').val();
                if (orderByDir) {
                    orderByElem = chopLastChar(orderByElem) + ' ' + orderByDir + '"';
                }
                scElements.push(orderByElem);
            }


            var scText;
            switch (shortcode) {
                case '[cfdb-html]':
                    scElements.push(getValue('filelinks', jQuery('#filelinks_cntl').val(), validationErrors));
                    var content = jQuery('#content_cntl').val();
                    scText = join(scElements) + ']' + content + '[/cfdb-html]';
                    break;
                case '[cfdb-table]':
                    scElements.push(getValue('id', jQuery('#id_cntl').val(), validationErrors));
                    scElements.push(getValue('class', jQuery('#class_cntl').val(), validationErrors));
                    scElements.push(getValue('style', jQuery('#style_cntl').val(), validationErrors));
                    scText = join(scElements) + ']';
                    break;
                case '[cfdb-datatable]':
                    scElements.push(getValue('id', jQuery('#id_cntl').val(), validationErrors));
                    scElements.push(getValue('class', jQuery('#class_cntl').val(), validationErrors));
                    scElements.push(getValue('style', jQuery('#style_cntl').val(), validationErrors));
                    scElements.push(getValue('dt_options', jQuery('#dt_options_cntl').val(), validationErrors));
                    scText = join(scElements) + ']';
                    break;
                case '[cfdb-value]':
                    scElements.push(getValue('function', jQuery('#function_cntl').val(), validationErrors));
                    scElements.push(getValue('delimiter', jQuery('#delimiter_cntl').val(), validationErrors));
                    scText = join(scElements) + ']';
                    break;
                case '[cfdb-count]':
                    scText = cjoin(scElements) + ']'; // hopLastChar(scElements.join(' ')) + ']';
                    break;
                case '[cfdb-json]':
                    scElements.push(getValue('var', jQuery('#var_cntl').val(), validationErrors));
                    scElements.push(getValue('format', jQuery('#format_cntl').val(), validationErrors));
                    scText = join(scElements) + ']'; 
                    break;
                default:
                    scText = shortcode;
                    break;
            }
            jQuery('#shortcode_result_text').html(scText);
            jQuery('#validations_text').html(validationErrors.join('<br/>'));
        }

        jQuery(document).ready(function() {
            showHideOptionDivs();
            createShortCode();
            jQuery('#shortcode_ctrl').change(showHideOptionDivs);
            jQuery('#shortcode_ctrl').change(createShortCode);
            jQuery('select[id$="cntl"]').change(createShortCode);
            jQuery('input[id$="cntl"]').keyup(createShortCode);
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
            font-family: Arial sans-serif;
            margin-top: 5px;
        }
    </style>

    <h2>Short Code Builder</h2>
    <div style="margin-bottom:10px">
        <span>
            <label for="shortcode_ctrl">Short Code</label>
            <select name="shortcode_ctrl" id="shortcode_ctrl">
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
            <label for="form_name_cntl">form</label>
            <select name="form_name_cntl" id="form_name_cntl">
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
        <a id="doc_url_tag" target="_docs"
           href="http://cfdbplugin.com/?page_id=89"><?php _e('Documentation', 'contact-form-7-to-database-extension'); ?></a>
    </div>
    <div id="show_hide_div" class="shortcodeoptions">
        <?php _e('Which fields/columns do you want to display?', 'contact-form-7-to-database-extension'); ?>
        <div>
            <label for="show_cntl">show</label>
            <input name="show_cntl" id="show_cntl" type="text" size="100"/>
        </div>
        <div>
            <label for="hide_cntl">hide</label>
            <input name="hide_cntl" id="hide_cntl" type="text" size="100"/>
        </div>
    </div>
    <div id="filter_div" class="shortcodeoptions">
        <div><?php _e('Which rows/submissions do you want to display?', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <label for="search_cntl">search</label>
            <input name="search_cntl" id="search_cntl" type="text" size="30"/>
        </div>
        <div>
            <label for="filter_cntl">filter</label>
            <input name="filter_cntl" id="filter_cntl" type="text" size="100"/>
        </div>
        <div>
            <label for="limit_rows_cntl">limit</label>
            Num Rows <input name="limit_rows_cntl" id="limit_rows_cntl" type="text" size="10"/>
            Start Row (0)<input name="limit_start_cntl" id="limit_start_cntl" type="text" size="10"/>
        </div>
        <div id="orderby_div">
            <label for="orderby_cntl">orderby</label>
            <input name="orderby_cntl" id="orderby_cntl" type="text" size="100"/>
            <select id="orderbydir_cntl" name="orderbydir_cntl">
                <option value=""></option>
                <option value="ASC">ASC</option>
                <option value="DESC">DESC</option>
            </select>
        </div>
    </div>
    <div id="html_format_div" class="shortcodeoptions">
        <div><?php _e('HTML Table Formatting', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <td><label for="id_cntl">id</label></td>
            <td><input name="id_cntl" id="id_cntl" type="text" size="10"/></td>
        </div>
        <div>
            <td><label for="class_cntl">class</label></td>
            <td><input name="class_cntl" id="class_cntl" type="text" size="10"/></td>
        </div>
        <div>
            <td><label for="style_cntl">style</label></td>
            <td><input name="style_cntl" id="style_cntl" type="text" size="100"/></td>
        </div>
    </div>
    <div id="dt_options_div" class="shortcodeoptions">
        <div><?php _e('DataTable Options', 'contact-form-7-to-database-extension'); ?></div>
        <label for="dt_options_cntl">dt_options</label>
        <input name="dt_options_cntl" id="dt_options_cntl" type="text" size="100"/>
    </div>
    <div id="json_div" class="shortcodeoptions">
        <div><?php _e('JSON Options', 'contact-form-7-to-database-extension'); ?></div>
        <div>
            <label for="var_cntl">var</label>
            <input name="var_cntl" id="var_cntl" type="text" size="10"/>
        </div>
        <div>
            <label for="format_cntl">format</label>
            <select id="format_cntl" name="format_cntl">
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
            <label for="function_cntl">function</label>
            <select id="function_cntl" name="function_cntl">
                <option value=""></option>
                <option value="min">min</option>
                <option value="max">max</option>
                <option value="sum">sum</option>
                <option value="mean">mean</option>
            </select>
        </div>
        <div>
            <label for="delimiter_cntl">delimiter</label>
            <input name="delimiter_cntl" id="delimiter_cntl" type="text" size="10"/>
        </div>
    </div>
    <div id="template_div" class="shortcodeoptions">
        <div>
            <label for="filelinks_cntl">filelinks</label>
            <select id="filelinks_cntl" name="filelinks_cntl">
                <option value=""></option>
                <option value="url">url</option>
                <option value="name">name</option>
                <option value="link">link</option>
                <option value="img">img</option>
            </select>
        </div>
        <div>
            <label for="content_cntl">Template text</label><br/>
            <textarea name="content_cntl" id="content_cntl" cols="100" rows="10"></textarea>
        </div>
    </div>

    <h2>Short Code Text</h2>
    <div id="shortcode_result_div" class="shortcodeoptions">
        <pre><code><span id="shortcode_result_text"></span></code></pre>
    </div>
    <div id="validations_div">
        <span id="validations_text" style="background-color:#ffff66;"></span>
    </div>

    <?php

    }
}