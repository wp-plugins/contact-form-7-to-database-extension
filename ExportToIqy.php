<?php

require_once('CF7DBPlugin.php');

class ExportToIqy {

    public function export($formName) {
        header("Content-Type: text/x-ms-iqy");
        header("Content-Disposition: attachment; filename=\"$formName.iqy\"");

        $plugin = new CF7DBPlugin();
        echo (
"WEB
1
" . get_bloginfo('url') ."/wp-admin/admin.php?page=" . $plugin->getDBPageSlug() . "
form_name=$formName&log=[\"WordPress Username\"]&pwd=[\"WordPress Password\"]

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