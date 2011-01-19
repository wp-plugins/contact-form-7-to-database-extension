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

    $guser = CF7DBUtil::getParam('guser');
    $gpwd = CF7DBUtil::getParam('gpwd');

    // Assumes session started in CF7DBPlugin::whatsInTheDBPage()
    $key = '3fde789a'; //substr($_COOKIE['PHPSESSID'], - 5); // session_id() doesn't work
    if ($guser) {
        $guser = mcrypt_decrypt(MCRYPT_3DES, $key, hexToStr($guser), 'ecb');
    }
    if ($gpwd) {
        $gpwd = mcrypt_decrypt(MCRYPT_3DES, $key, hexToStr($gpwd), 'ecb');
    }

    CF7DBPluginExporter::export(
       $form,
       CF7DBUtil::getParam('enc'),
       $guser,
       $gpwd);
}

// Taken from http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
function hexToStr($hex) {
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2) {
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}


cF7DBExport_export();