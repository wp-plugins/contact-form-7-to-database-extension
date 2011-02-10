<?php
/*
    "Contact Form to Database Extension" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database Extension.

    Contact Form to Database Extension is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database Extension is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
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