<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2010 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

include_once('../../../wp-config.php');
include_once('../../../wp-includes/functions.php');
require_wp_db();
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
        wp_die(__('No such file.', 'contact-form-7-to-database-extension'));
    }

    header("Content-Disposition: attachment; filename=\"$fileInfo[0]\"");
    echo($fileInfo[1]);
}


cF7ToDBGetFile();