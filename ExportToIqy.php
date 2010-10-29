<?php

require_once('CF7DBPlugin.php');

class ExportToIqy {

    public function export($formName) {
        header("Content-Type: text/x-ms-iqy");
        header("Content-Disposition: attachment; filename=\"$formName.iqy\"");

        $url = get_bloginfo('url');
        $plugin = new CF7DBPlugin();
        $slug = $plugin->getDBPageSlug();
        $encFormName = urlencode($formName);
        $encRedir = urlencode("/wp-admin/admin.php?page=$slug&form_name=$encFormName");

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

Selection=3
Formatting=RTF
PreFormattedTextToColumns=True
ConsecutiveDelimitersAsOne=True
SingleBlockTextImport=False
DisableDateRecognition=False
DisableRedirections=False
");
    }
}