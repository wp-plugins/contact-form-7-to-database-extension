<?php

require_once('CF7DBPlugin.php');

class ExportToGoogleLiveData {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        $scriptLink = $plugin->getPluginDirUrl() . "Cf7ToDBGGoogleSS.js.php";
        $siteUrl = get_option("home");
        ?>
        <html>
        <body>
        Setting up a Google Spreadsheet to pull in data from WordPress requires these manual steps:
        <ol>
            <li>Log into Google Docs and create a new Google Spreadsheet</li>
            <li>Go to Tools menu -> Scripts -> Script Editor...</li>
            <li>Copy the text from <a target="_gscript" href="<?php echo($scriptLink) ?>">this file</a> and paste it into the Google script editor</li>
            <li>Save and close the script editor.</li>
            <li>Click on a cell A1 in the Spreadsheet (or any cell)</li>
            <li>Enter in the cell the formula: <br/>
                <code><?php echo("=CF7ToDBData(\"$siteUrl\", \"$formName\", \"user\", \"pwd\")") ?></code><br/>
                Replacing <b>user</b> and <b>pwd</b> with your <u>WordPress</u> site user name and password
            </li>
        </ol>
        <body>
        </html>
        <?php
    }
}
