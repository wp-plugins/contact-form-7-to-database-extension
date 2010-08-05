<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBTableData.php');
require_once('CF7DBPlugin.php');

function CF7DBPlugin_exportToCSV($formName) {
    $plugin = new CF7DBPlugin();
    $tableData = $plugin->getRowsPivot($formName);

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=\"$formName.csv\"");

    // Column Headers
    echo "Submitted,";
    foreach ($tableData->columns as $aCol) {
        echo "\"$aCol\",";
    }
    echo "\n";

    // Rows
    foreach ($tableData->pivot as $submitTime => $data) {
        echo date('Y-m-d', $submitTime);
        echo ",";
        foreach ($tableData->columns as $aCol) {
            $cell = isset($data[$aCol]) ? $data[$aCol] : "";
            echo "\"$cell\",";
        }
        echo "\n";
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
 
