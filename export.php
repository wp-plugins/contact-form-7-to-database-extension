<?php

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