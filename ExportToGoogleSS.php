<?php
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBPlugin.php');
require_once('CJ7DBCheckZendFramework.php');

class ExportToGoogleSS {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        if (!CJ7DBCheckZendFramework::checkIncludeZend()) {
            return;
        }

        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
//        Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
        Zend_Loader::loadClass('Zend_Gdata_App_AuthException');
        Zend_Loader::loadClass('Zend_Http_Client');
        Zend_Loader::loadClass('Zend_Gdata_Docs');

        // todo: Get Google Login info
        $email = "xxxx@gmail.com";
        $password = 'xxxx';

        try {
            $client = Zend_Gdata_ClientLogin::getHttpClient(
                $email, $password,
                Zend_Gdata_Docs::AUTH_SERVICE_NAME);
//                Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME);
        }
        catch (Zend_Gdata_App_AuthException $ae) {
            wp_die("Error: " . $ae->getMessage() . "\nCredentials provided were email: [$email] and password [$password].\n");
        }

        try {
            $docs = new Zend_Gdata_Docs($client);
//            $feed = $docs->getDocumentListFeed();

            // todo: create CSV temp file. 
            $loc = 'Contact form 1.csv';
            $newDocumentEntry = $docs->uploadFile($loc, $formName, 'text/csv');
            $alternateLink = '';
            foreach ($newDocumentEntry->link as $link) {
                if ($link->getRel() === 'alternate') {
                    $alternateLink = $link->getHref();
                    break;
                }
            }
            // Make the title link to the document on docs.google.com.
            $title = $newDocumentEntry->title;
            header("Location: $alternateLink");
        }
        catch (Exception $ex) {
            wp_die($ex->getMessage() . "<pre>" . $ex->getTraceAsString() . "</pre>");
        }
    }
}
