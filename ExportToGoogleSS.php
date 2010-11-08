<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBPlugin.php');
require_once('CJ7DBCheckZendFramework.php');
require_once('ExportToCsvUtf8.php');

class ExportToGoogleSS {

    public function export($formName, $guser, $gpwd) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: text/html; charset=UTF-8");

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
            wp_die("<p>Login failed for: '$guser' </p><p>Error: " . $ae->getMessage() . "</p>");
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
            $handle = fopen($tmpfname, "w");
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

            $output =
                    utf8_encode("New Google Spreadsheet: <a target=\"_blank\" href=\"$alternateLink\">") .
                            $formName .
                            utf8_encode("</a>");
            wp_die($output);
        }
        catch (Exception $ex) {
            wp_die($ex->getMessage() . "<pre>" . $ex->getTraceAsString() . "</pre>");
        }
    }
}
