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

require_once('CF7DBPlugin.php');

class ExportBase {

    protected function isAuthorized($options = null) {
        $plugin = new CF7DBPlugin();
        return (isset($options['fromshortcode']) && $options['fromshortcode'] === true) ?
                $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode') :
                $plugin->canUserDoRoleOption('CanSeeSubmitData');
    }

    protected function assertSecurityErrorMessage($options = null) {
        $errMsg = __('You do not have sufficient permissions to access this data.', 'contact-form-7-to-database-extension');
        if (isset($options['fromshortcode']) && $options['fromshortcode'] === true) {
           echo $errMsg;
        }
        else {
            wp_die($errMsg);
        }
    }


    /**
     * @param string|array|null $headers mixed string header-string or array of header strings.
     * E.g. Content-Type, Content-Disposition, etc.
     * @return void
     */
    protected function echoHeaders($headers = null) {
        if (!headers_sent()) {
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            // Hoping to keep the browser from timing out if connection from Google SS Live Data
            // script is calling this page to get information
            header("Keep-Alive: timeout=60"); // Not a standard HTTP header; browsers may disregard

            if ($headers) {
                if (is_array($headers)) {
                    foreach ($headers as $aheader) {
                        header($aheader);
                    }
                }
                else {
                    header($headers);
                }
            }
            flush();
        }
    }
}
