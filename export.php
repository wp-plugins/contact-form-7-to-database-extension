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

require_once('ExportToCsvUtf8.php');
require_once('ExportToCsvUtf16le.php');
require_once('ExportToIqy.php');
require_once('ExportToGoogleSS.php');
require_once('ExportToGoogleLiveData.php');
require_once('CF7DBPluginExporter.php');
require_once('CF7DBUtil.php');

function cF7DBExport_export() {
    $form = CF7DBUtil::getParam('form');
    if (!$form) {
        ?>
        <html>
        <body>Error: No "form" parameter submitted</body>
        </html>
        <?php
        return;
    }

    CF7DBPluginExporter::export(
       $form,
       CF7DBUtil::getParam('enc'),
       CF7DBUtil::getParam('guser'),
       CF7DBUtil::getParam('gpwd'));
}

cF7DBExport_export();