<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2010 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require_once('ShortCodeLoader.php');
require_once('CF7DBPlugin.php');

class CFDBShortcodeTable extends ShortCodeLoader {

    /**
     * Shortcode callback for writing the table of form data. Can be put in a page or post to show that data.
     * Shortcode options:
     * [cfdb-table form="your-form"]                             (shows the whole table with default options)
     * Controlling the Display: Apply your CSS to the table; set the table's 'class' or 'id' attribute:
     * [cfdb-table form="your-form" class="css_class"]           (outputs <table class="css_class"> (default: class="cf7-db-table")
     * [cfdb-table form="your-form" id="css_id"]                 (outputs <table id="css_id"> (no default id)
     * [cfdb-table form="your-form" id="css_id" class="css_class"] (outputs <table id="css_id" class="css_class">
     * Filtering Columns:
     * [cfdb-table form="your-form" show="field1,field2,field3"] (optionally show selected fields)
     * [cfdb-table form="your-form" hide="field1,field2,field3"] (optionally hide selected fields)
     * [cfdb-table form="your-form" show="f1,f2,f3" hide="f1"]   (hide trumps show)
     * Filtering Rows:
     * [cfdb-table form="your-form" filter="field1=value1"]      (show only rows where field1=value1)
     * [cfdb-table form="your-form" filter="field1!=value1"]      (show only rows where field1!=value1)
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2"] (Logical AND the filters using '&&')
     * [cfdb-table form="your-form" filter="field1=value1||field2!=value2"] (Logical OR the filters using '||')
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2||field3=value3&&field4=value4"] (Mixed &&, ||)
     * @param  $atts array of short code attributes
     * @return HTML output of shortcode
     */
    public function handle_shortcode($atts) {
        if ($atts['form']) {
            $plugin = new CF7DBPlugin();
            if ($plugin->canUserDoRoleOption('CanSeeSubmitData') ||
                    $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                $atts['canDelete'] = false;
                if ($atts['show']) {
                    $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
                    $atts['showColumns'] = $showColumns;
                }
                if ($atts['hide']) {
                    $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
                    $atts['hideColumns'] = $hideColumns;
                }
                $atts['fromshortcode'] = true;
                $export = new ExportToHtml();
                $html = $export->export($atts['form'], $atts);
                return $html;
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }

}
