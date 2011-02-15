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

require_once('CF7DBPluginExporter.php');

function cF7DBExport_export() {

    // Consolidate GET and POST parameters. Allow GET to override POST.
    $params = array_merge($_POST, $_GET);

    //print_r($params);

    if (!isset($params['form'])) {
        wp_die(__('Error: No "form" parameter submitted', 'contact-form-7-to-database-extension'));
        return;
    }

    // Assumes coming from CF7DBPlugin::whatsInTheDBPage()
    $key = '3fde789a'; //substr($_COOKIE['PHPSESSID'], - 5); // session_id() doesn't work
    if (isset($params['guser'])) {
        $params['guser'] = mcrypt_decrypt(MCRYPT_3DES, $key, hexToStr($params['guser']), 'ecb');
    }
    if (isset($params['gpwd'])) {
        $params['gpwd'] = mcrypt_decrypt(MCRYPT_3DES, $key, hexToStr($params['gpwd']), 'ecb');
    }

    CF7DBPluginExporter::export(
        $params['form'],
        $params['enc'],
        $params);
}

// Taken from http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
function hexToStr($hex) {
    $string = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $string;
}


cF7DBExport_export();