<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require_once('CFDBShortCodeLoaderSecurityCheck.php');

class CFDBShortcodeValue extends CFDBShortCodeLoaderSecurityCheck {

    /**
     * @param  $atts array of short code attributes
     * @return string value submitted to a form field as selected by $atts. See ExportToValue.php
     */
    public function handleShortcodePostSecurityCheck($atts) {
        if ($atts['show']) {
            $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
            $atts['showColumns'] = $showColumns;
        }
        if ($atts['hide']) {
            $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
            $atts['hideColumns'] = $hideColumns;
        }
        $atts['fromshortcode'] = true;
        $export = new ExportToValue();
        $html = $export->export($atts['form'], $atts);
        return $html;
    }

}
