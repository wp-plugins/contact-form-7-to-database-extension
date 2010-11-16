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

require_once('CF7DBPlugin.php');

class ExportToIqy {

    public function export($formName) {
        header('Content-Type: text/x-ms-iqy');
        header("Content-Disposition: attachment; filename=\"$formName.iqy\"");

        $url = get_bloginfo('url');
        $plugin = new CF7DBPlugin();
        $encFormName = urlencode($formName);
        $encRedir = urlencode($plugin->getPluginDirUrl() . "export.php?form=$encFormName&enc=HTML");

        // To get this to work right, we have to submit to the same page that the login form does and post
        // the same parameters that the login form does. This includes "log" and "pwd" for the login and
        // also "redirect_to" which is the URL of the page where we want to end up including a "form_name" parameter
        // to tell that final page to select which contact form data is to be displayed.
        //
        // "Selection=3" references the 3rd table in the page which is the data table.
        // "Formatting" can be "None", "All", or "RTF"
        echo (
"WEB
1
$url/wp-login.php?redirect_to=$encRedir
log=[\"Username for $url\"]&pwd=[\"Password for $url\"]

Selection=1
Formatting=All
PreFormattedTextToColumns=True
ConsecutiveDelimitersAsOne=True
SingleBlockTextImport=False
DisableDateRecognition=False
DisableRedirections=False
");
    }
}