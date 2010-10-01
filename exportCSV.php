<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBTableData.php');
require_once('CF7DBPlugin.php');

function CF7DBPlugin_exportToCSV($formName) {
    $plugin = new CF7DBPlugin();

    $roleAllowed = $plugin->getRoleOption('CanSeeSubmitData');
    $canSeeData = $plugin->isRoleOrBetter($roleAllowed);
    if (!$canSeeData) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $tableData = $plugin->getRowsPivot($formName);

    //  Notes on getting Excel-friendly encoding right: http://dev.piwik.org/trac/ticket/309
    header("Content-Type: application/vnd.ms-excel");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Content-Disposition: attachment; filename=\"$formName.csv\"");

    echo chr(255) . chr(254); // File encoding UTF-16LE

    $eol = mb_convert_encoding("\n", 'UTF-16LE', 'UTF-8');
    $comma = mb_convert_encoding(",", 'UTF-16LE', 'UTF-8');

    // Column Headers
    echo mb_convert_encoding(__("\"Submitted\""), 'UTF-16LE', 'UTF-8');
    echo $comma;
    foreach ($tableData->columns as $aCol) {
        echo mb_convert_encoding("\"$aCol\",", 'UTF-16LE', 'UTF-8');
    }
    echo $eol;

    // Rows
    foreach ($tableData->pivot as $submitTime => $data) {
        echo mb_convert_encoding(date('Y-m-d', $submitTime), 'UTF-16LE', 'UTF-8');
        echo $comma;
        foreach ($tableData->columns as $aCol) {
            $cell = isset($data[$aCol]) ? $data[$aCol] : "";
            echo mb_convert_encoding("\"$cell\"", 'UTF-16LE', 'UTF-8');
            echo $comma;
        }
        echo $eol;
    }
}

if (isset($_GET['form_name'])) {
    CF7DBPlugin_exportToCSV($_GET['form_name']);
}
else if (isset($_POST['form_name'])) {
    CF7DBPlugin_exportToCSV($_POST['form_name']);
}
else {
    ?>
<html><body>Error: No "form_name" parameter submitted</body></html>
    <?php
}
 
