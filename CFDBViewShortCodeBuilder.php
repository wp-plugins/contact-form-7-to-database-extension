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
    <h2>Short Code Builder</h2>
        <table cellpadding="5px">
            <tbody>
            <tr>
                <td><label for="shortcode">Short Code</label></td>
                <td>
                    <select name="shortcode" id="shortcode">
                        <option value=""><?php _e('* Select a short code *', 'contact-form-7-to-database-extension') ?></option>
                        <option value="[cfdb-html]">[cfdb-html]</option>
                        <option value="[cfdb-table]">[cfdb-table]</option>
                        <option value="[cfdb-datatable]">[cfdb-datatable]</option>
                        <option value="[cfdb-value]">[cfdb-value]</option>
                        <option value="[cfdb-count]">[cfdb-count]</option>
                        <option value="[cfdb-json]">[cfdb-json]</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="form_name">form</label></td>
                <td>
                    <select name="form_name" id="form_name">
                    <option value=""><?php _e('* Select a form *', 'contact-form-7-to-database-extension') ?></option>
                    <?php foreach ($rows as $aRow) {
                        $formName = $aRow->form_name;
                        $selected = ($formName == $currSelection) ? "selected" : "";
                        ?>
                            <option value="<?php echo $formName ?>" <?php echo $selected ?>><?php echo $formName ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="3"><?php _e('Which fields/columns do you want to display?', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="show">show</label></td>
                <td><input name="show" id="show" type="text" size="100" /></td>
            </tr>
            <tr>
                <td><label for="hide">hide</label></td>
                <td><input name="hide" id="hide" type="text" size="100" /></td>
            </tr>
            <tr>
                <td><label for="orderby">orderby</label></td>
                <td><input name="orderby" id="orderby" type="text" size="100" /></td>
                <td>
                    <select id="orderbydir" name="orderbydir">
                        <option value=""></option>
                        <option value="ASC">ASC</option>
                        <option value="DESC">DESC</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="3"><?php _e('Which rows/submissions do you want to display?', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="search">search</label></td>
                <td><input name="search" id="search" type="text" size="30" /></td>
            </tr>
            <tr>
                <td><label for="filter">filter</label></td>
                <td><input name="filter" id="filter" type="text" size="100" /></td>
            </tr>
            <tr>
                <td><label for="limit">limit</label></td>
                <td>
                    Start Row (0)<input name="limit_start" id="limit_start" type="text" size="10"/>
                    Num Rows <input name="limit" id="limit" type="text" size="10"/>
                </td>
            </tr>

            <tr>
                <td colspan="3"><?php _e('HTML Table Formatting', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="id">id</label></td>
                <td><input name="id" id="id" type="text" size="10" /></td>
            </tr>
            <tr>
                <td><label for="class">class</label></td>
                <td><input name="class" id="class" type="text" size="10" /></td>
            </tr>
            <tr>
                <td><label for="style">style</label></td>
                <td><input name="style" id="style" type="text" size="100" /></td>
            </tr>

            <tr>
                <td colspan="3"><?php _e('DataTable Options', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="dt_options">dt_options</label></td>
                <td><input name="dt_options" id="dt_options" type="text" size="100" /></td>
            </tr>

            <tr>
                <td colspan="3"><?php _e('JSON Options', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="var">var</label></td>
                <td><input name="var" id="var" type="text" size="10" /></td>
            </tr>
            <tr>
                <td><label for="format">format</label></td>
                <td>
                    <select id="format" name="format">
                        <option value=""></option>
                        <option value="map">map</option>
                        <option value="array">array</option>
                        <option value="arraynoheader">arraynoheader</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td colspan="3"><?php _e('VALUE Options', 'contact-form-7-to-database-extension'); ?></td>
            </tr>
            <tr>
                <td><label for="function">function</label></td>
                <td>
                    <select id="function" name="function">
                        <option value=""></option>
                        <option value="min">min</option>
                        <option value="max">max</option>
                        <option value="sum">sum</option>
                        <option value="mean">mean</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="delimiter">delimiter</label></td>
                <td><input name="delimiter" id="delimiter" type="text" size="10" /></td>
            </tr>

            <tr>
                <td><label for="content">Template text</label></td>
            </tr>
            <tr>
                <td colspan="3"><textarea name="content" id="content" cols="30" rows="10"></textarea></td>
            </tr>
            </tbody>
        </table>


    <?php

    }
}