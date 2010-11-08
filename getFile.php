<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBTableData.php');
require_once('CF7DBPlugin.php');
require_once('CF7DBUtil.php');

function cF7ToDBGetFile() {
    $plugin = new CF7DBPlugin();
    if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
    }

    $submitTime = CF7DBUtil::getParam('s');
    $formName = CF7DBUtil::getParam('form');
    $fieldName = CF7DBUtil::getParam('field');
    if (!$submitTime || !$formName || !$fieldName) {
        wp_die(__('Missing form parameters', 'contact-form-7-to-database-extension'));
    }

    $fileInfo = (array) $plugin->getFileFromDB($submitTime, $formName, $fieldName);
    if ($fileInfo == null) {
        wp_die(__("No such file.", 'contact-form-7-to-database-extension'));
    }

    header("Content-Disposition: attachment; filename=\"$fileInfo[0]\"");
    echo($fileInfo[1]);
}


cF7ToDBGetFile();