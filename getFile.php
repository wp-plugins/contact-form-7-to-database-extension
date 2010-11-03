<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBTableData.php');
require_once('CF7DBPlugin.php');

function cF7ToDBGetFile() {
    $plugin = new CF7DBPlugin();
    if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $submitTime = cF7ToDBGetParam('s');
    $formName = cF7ToDBGetParam('form');
    $fieldName = cF7ToDBGetParam('field');
    if (!$submitTime || !$formName || !$fieldName) {
        wp_die(__('Missing form parameters'));
    }

    $fileInfo = (array) $plugin->getFileFromDB($submitTime, $formName, $fieldName);
    if ($fileInfo == null) {
        wp_die(__("No such file."));
    }

    header("Content-Disposition: attachment; filename=\"$fileInfo[0]\"");
    echo($fileInfo[1]);
}

function &cF7ToDBGetParam($paramName) {
    if (isset($_GET[$paramName])) {
        return $_GET[$paramName];
    }
    else if (isset($_POST[$paramName])) {
        return $_POST[$paramName];
    }
    return null;
}

cF7ToDBGetFile();