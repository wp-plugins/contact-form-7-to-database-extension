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
require_once('ExportToJson.php');

class CFDBShortcodeJson extends ShortCodeLoader {

    /**
     * @param  $atts array of short code attributes
     * @return string JSON. See ExportToJson.php
     */
    public function handle_shortcode($atts) {
        if ($atts['form']) {
            $plugin = new CF7DBPlugin();
            if ($plugin->canUserDoRoleOption('CanSeeSubmitData') ||
                    $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                if ($atts['show']) {
                    $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
                    $atts['showColumns'] = $showColumns;
                }
                if ($atts['hide']) {
                    $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
                    $atts['hideColumns'] = $hideColumns;
                }
                $atts['html'] = true;
                $atts['fromshortcode'] = true;
                $export = new ExportToJson();
                $html = $export->export($atts['form'], $atts);
                return $html;
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }
}
