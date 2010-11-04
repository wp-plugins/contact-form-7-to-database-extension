<?php

require_once('CF7DBPlugin.php');
require_once('ExportToCsvUtf8.php');
require_once('ExportToCsvUtf16le.php');
require_once('ExportToIqy.php');
require_once('ExportToGoogleLiveData.php');

function CF7DBPlugin_exportToCSV($formName, $encoding) {
    $plugin = new CF7DBPlugin();
    if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

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

