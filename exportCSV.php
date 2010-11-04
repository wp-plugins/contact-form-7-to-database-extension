<?php

require_once('ExportToCsvUtf8.php');
require_once('ExportToCsvUtf16le.php');
require_once('ExportToIqy.php');
//require_once('ExportToGoogleSS.php');
require_once('ExportToGoogleLiveData.php');

function CF7DBPlugin_exportToCSV($formName, $encoding) {

    switch ($encoding) {
        case 'IQY':
            $exporter = new ExportToIqy();
            $exporter->export($formName);
            break;
        case 'UTF8':
            $exporter = new ExportToCsvUtf8();
            $exporter->export($formName);
            break;
        case 'UTF16LE':
            $exporter = new ExportToCsvUtf16le();
            $exporter->export($formName);
            break;
        case 'GLD':
            $exporter = new ExportToGoogleLiveData();
            $exporter->export($formName);
            break;
//        case 'GSS':
//            $exporter = new ExportToGoogleSS();
//            $exporter->export($formName);
//            break;
        default:
            break;
    }
}

if (isset($_GET['form_name'])) {
    CF7DBPlugin_exportToCSV($_GET['form_name'], $_GET['encoding']);
}
else if (isset($_POST['form_name'])) {
    CF7DBPlugin_exportToCSV($_POST['form_name'], $_POST['encoding']);
}
else {
    ?>
<html><body>Error: No "form_name" parameter submitted</body></html>
    <?php
}

