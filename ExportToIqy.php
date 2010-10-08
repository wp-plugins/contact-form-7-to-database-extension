<?php
class ExportToIqy {

    public function export($formName) {
        header("Content-Type: text/x-ms-iqy");
        header("Content-Disposition: attachment; filename=\"$formName.iqy\"");
        echo (
"WEB
1
http://blog.michael-simpson.com/wp-admin/admin.php?page=CF7DBPluginSubmissions
form_name=$formName

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