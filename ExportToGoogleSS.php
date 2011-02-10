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
require_once('CF7DBPlugin.php');
require_once('CJ7DBCheckZendFramework.php');
require_once('ExportToCsvUtf8.php');

class ExportToGoogleSS {

    public function export($formName, $guser, $gpwd) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Type: text/html; charset=UTF-8');

        // Hoping to keep the browser from timing out if connection to Google Docs is slow
        // Not a standard HTTP header; browsers may disregard
        header("Keep-Alive: timeout=60");

        flush(); // try to prevent the browser from timing out on slow uploads by giving it something

        if (!CJ7DBCheckZendFramework::checkIncludeZend()) {
            return;
        }

        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        //Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
        Zend_Loader::loadClass('Zend_Gdata_App_AuthException');
        Zend_Loader::loadClass('Zend_Http_Client');
        Zend_Loader::loadClass('Zend_Gdata_Docs');

        try {
            $client = Zend_Gdata_ClientLogin::getHttpClient(
                $guser, $gpwd,
                Zend_Gdata_Docs::AUTH_SERVICE_NAME); //Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME
        }
        catch (Zend_Gdata_App_AuthException $ae) {
            wp_die("<p>Login failed for: '$guser' </p><p>Error: " . $ae->getMessage() . '</p>',
                   __('Login Failed', 'contact-form-7-to-database-extension'),
                   array('response' => 200, 'back_link' => true));
        }

        try {
            // Generate CSV file contents into buffer
            $exporter = new ExportToCsvUtf8;
            $exporter->setUseBom(false);
            ob_start();
            $exporter->echoCsv($formName);
            $csvFileContents = ob_get_contents();
            ob_end_clean();

            // Put the contents in a tmp file because Google upload API only reads from a file
            $tmpfname = tempnam(sys_get_temp_dir(), "$formName.csv");
            $handle = fopen($tmpfname, 'w');
            fwrite($handle, $csvFileContents);
            fclose($handle);

            // Upload the tmp file to Google Docs
            $docs = new Zend_Gdata_Docs($client);
            $newDocumentEntry = $docs->uploadFile($tmpfname, $formName, 'text/csv');
            unlink($tmpfname); // delete tmp file

            // Get the URL of the new Google doc
            $alternateLink = '';
            foreach ($newDocumentEntry->link as $link) {
                if ($link->getRel() === 'alternate') {
                    $alternateLink = $link->getHref();
                    break;
                }
            }

            //header("Location: $alternateLink");
            //$title = $newDocumentEntry->title;

            $title = __('New Google Spreadsheet', 'contact-form-7-to-database-extension');
            $output =
                    utf8_encode("$title: <a target=\"_blank\" href=\"$alternateLink\">") .
                            $formName .
                            utf8_encode('</a>');
            wp_die($output, $title,  array('response' => 200, 'back_link' => true));
        }
        catch (Exception $ex) {
            wp_die($ex->getMessage() . '<pre>' . $ex->getTraceAsString() . '</pre>',
                   __('Error', 'contact-form-7-to-database-extension'),
                   array('back_link' => true));
        }
    }
}
