<?php

require_once('CF7DBPlugin.php');

class ExportToIqy {

    public function export($formName) {
        header("Content-Type: text/x-ms-iqy");
        header("Content-Disposition: attachment; filename=\"$formName.iqy\"");

        $url = get_bloginfo('url');
        $plugin = new CF7DBPlugin();
        $slug = $plugin->getDBPageSlug();

        // To get this to work right, we have to submit to the same page that the login form does and post
        // the same parameters that the login form does. This includes "log" and "pwd" for the login and
        // also "redirect_to" which is the page where we want to end up. We also add the "form_name" parameter
        // for the final page to select which contact form data is to be displayed.
        //
        // the "Selection=3" part references the 3rd table in the page which is the data table.
        echo (
"WEB
1
$url/wp-login.php
form_name=$formName&log=[\"Username for $url\"]&pwd=[\"Password for $url\"]&redirect_to=$url/wp-admin/admin.php?page=$slug

Selection=3
Formatting=None
PreFormattedTextToColumns=True
ConsecutiveDelimitersAsOne=True
SingleBlockTextImport=False
DisableDateRecognition=False
DisableRedirections=False
");
    }
}