<?php

require_once('ExportToCsvUtf8.php');
require_once('ExportToCsvUtf16le.php');
require_once('ExportToIqy.php');
require_once('ExportToGoogleSS.php');
require_once('ExportToGoogleLiveData.php');

function CF7DBPlugin_exportToCSV($formName, $encoding) {

    switch ($encoding) {
        case 'IQY':
            $exporter = new ExportToIqy();
            $exporter->export($formName);
            break;
        case 'CSVUTF8BOM':
            $exporter = new ExportToCsvUtf8();
            $exporter->setUseBom(true);
            $exporter->export($formName);
            break;
        case 'UTF16LEBOM':
            $exporter = new ExportToCsvUtf16le();
            $exporter->export($formName);
            break;
        case 'GLD':
            $exporter = new ExportToGoogleLiveData();
            $exporter->export($formName);
            break;
        case 'GSS':
            $exporter = new ExportToGoogleSS();
            $exporter->export($formName);
            break;
        case 'CSVUTF8':
        default:
            $exporter = new ExportToCsvUtf8();
            $exporter->setUseBom(false);
            $exporter->export($formName);
            break;
    }
}

if (isset($_GET['form'])) {
    CF7DBPlugin_exportToCSV($_GET['form'], $_GET['enc']);
}
else if (isset($_POST['form'])) {
    CF7DBPlugin_exportToCSV($_POST['form'], $_POST['enc']);
}
elseif (isset($_GET['form_name'])) {
    CF7DBPlugin_exportToCSV($_GET['form'], $_GET['encoding']);
}
else if (isset($_POST['form_name'])) {
    CF7DBPlugin_exportToCSV($_POST['form'], $_POST['encoding']);
}
else {
    ?>
    <html>
    <body>Error: No "form" parameter submitted</body>
    </html>
    <?php
}

