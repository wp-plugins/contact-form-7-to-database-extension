<?php

require_once('CF7DBPlugin.php');

class ExportToGoogleLiveData {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        $scriptLink = $plugin->getPluginDirUrl() . "Cf7ToDBGGoogleSS.js.php";
        $siteUrl = get_option("home");
        ob_start();
        ?>
        Setting up a Google Spreadsheet to pull in data from WordPress requires these manual steps:
        <ol>
            <li>Log into Google Docs and create a new Google Spreadsheet</li>
            <li>Go to <b>Tools</b> menu -> <b>Scripts</b> -> <b>Script Editor...</b></li>
            <li>Delete any text that is already there</li>
            <li>Copy the text from <a target="_gscript" href="<?php echo($scriptLink) ?>">this file</a> and paste it
                into the Google script editor
            </li>
            <li><b>Save</b> and close the script editor.</li>
            <li>Click on a cell A1 in the Spreadsheet (or any cell)</li>
            <li>Enter in the cell the formula: <br/>
                <code><?php echo("=CF7ToDBData(\"$siteUrl\", \"$formName\", \"user\", \"pwd\")") ?></code><br/>
                Replacing <b>user</b> and <b>pwd</b> with your <u>WordPress</u> site user name and password
            </li>
        </ol>
        <span style="color:red; font-weight:bold;">
            WARNING: since you are putting your login information into the Google Spreadsheet, be sure not to share
        the spreadsheet with others.</span>
        <?php
            $html = ob_get_contents();
        ob_end_clean();
        wp_die($html,
               __("How to Set up Google Spreadsheet to pull data from WordPress", 'contact-form-7-to-database-extension'),
               array('response' => 200, 'back_link' => true));
    }
}
