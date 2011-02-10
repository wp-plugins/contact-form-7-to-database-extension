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