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

require_once('CF7DBPluginLifeCycle.php');
require_once('CF7DBTableData.php');
require_once('ExportToHtml.php');
require_once('ExportToJson.php');
require_once('ExportToValue.php');

/**
 * Implementation for CF7DBPluginLifeCycle.
 */

class CF7DBPlugin extends CF7DBPluginLifeCycle {

    public function getPluginDisplayName() {
        return 'Contact Form to DB Extension';
    }

    public function getOptionMetaData() {
        return array(
            //'_version' => array('Installed Version'), // For testing upgrades
            'CanSeeSubmitData' => array(__('Can See Submission data', 'contact-form-7-to-database-extension'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone'),
            'CanSeeSubmitDataViaShortcode' => array(__('Can See Submission when using shortcodes', 'contact-form-7-to-database-extension'),
                                                    'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone'),
            'CanChangeSubmitData' => array(__('Can Delete Submission data', 'contact-form-7-to-database-extension'),
                                           'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
            'ShowLineBreaksInDataTable' => array(__('Show line breaks in submitted data table', 'contact-form-7-to-database-extension'), 'true', 'false'),
            'SubmitDateTimeFormat' => array('<a target="_blank" href="http://php.net/manual/en/function.date.php">' . __('Date-Time Display Format') . '</a>'),
            'ShowFileUrlsInExport' => array(__('Export URLs instead of file names for uploaded files', 'contact-form-7-to-database-extension'), 'false', 'true'),
            'NoSaveFields' => array(__('Do not save <u>fields</u> in DB named (comma-separated list, no spaces)', 'contact-form-7-to-database-extension')),
            'NoSaveForms' => array(__('Do not save <u>forms</u> in DB named (comma-separated list, no spaces)', 'contact-form-7-to-database-extension'))
            //'SubmitTableNameOverride' => array(__('Use this table to store submission data rather than the default (leave blank for default)', 'contact-form-7-to-database-extension'))
        );
    }

    public function upgrade() {
        global $wpdb;
        $upgradeOk = true;
        $version = $this->getVersionSaved();
        if (!$version || $version == "") { // Prior to storing version in options (pre 1.2)
            // DB Schema Upgrade to support i18n using UTF-8
            $tableName = $this->getSubmitsTableName();
            $wpdb->show_errors();
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` MODIFY form_name VARCHAR(127) CHARACTER SET utf8");
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` MODIFY field_name VARCHAR(127) CHARACTER SET utf8");
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` MODIFY field_value longtext CHARACTER SET utf8");
            $wpdb->hide_errors();

            // Remove obsolete options
            $this->deleteOption('_displayName');
            $this->deleteOption('_metatdata');
        }

        $submitDateTimeFormat = $this->getOption('SubmitDateTimeFormat');
        if (!$submitDateTimeFormat || $submitDateTimeFormat == '') {
            $this->addOption('SubmitDateTimeFormat', 'Y-m-d H:i:s P');
        }

        if ($this->isSavedVersionLessThan('1.3.1')) {
            $tableName = $this->getSubmitsTableName();
            $wpdb->show_errors();
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` ADD COLUMN `field_order` INTEGER");
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` ADD COLUMN `file` LONGBLOB");
            $upgradeOk &= false !== $wpdb->query("ALTER TABLE `$tableName` ADD INDEX `submit_time_idx` ( `submit_time` )");
            $wpdb->hide_errors();
        }

        if ($this->isSavedVersionLessThanEqual('1.4.5') && !$this->getOption('CanSeeSubmitDataViaShortcode')) {
            $this->addOption('CanSeeSubmitDataViaShortcode', 'Anyone');
        }

        // Post-upgrade, set the current version in the options
        if ($upgradeOk && $version != $this->getVersion()) {
            $this->saveInstalledVersion();
        }
    }

    /**
     * Called by install()
     * You should: Prefix all table names with $wpdb->prefix
     * Also good: additionally use the prefix for this plugin:
     * $table_name = $wpdb->prefix . $this->prefix('MY_TABLE');
     * @return void
     */
    protected function installDatabaseTables() {
        global $wpdb;
        $tableName = $this->getSubmitsTableName();
        $wpdb->show_errors();
        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
            `submit_time` INTEGER NOT NULL,
            `form_name` VARCHAR(127) CHARACTER SET utf8,
            `field_name` VARCHAR(127) CHARACTER SET utf8,
            `field_value` LONGTEXT CHARACTER SET utf8,
            `field_order` INTEGER,
            `file` LONGBLOB)");
        $wpdb->query("ALTER TABLE `$tableName` ADD INDEX `submit_time_idx` ( `submit_time` )");
        $wpdb->hide_errors();
    }


    /**
     * Called by uninstall()
     * You should: Prefix all table names with $wpdb->prefix
     * Also good: additionally use the prefix for this plugin:
     * $table_name = $wpdb->prefix . $this->prefix('MY_TABLE');
     * @return void
     */
    protected function unInstallDatabaseTables() {
        global $wpdb;
        $tableName = $this->getSubmitsTableName();
        $wpdb->query("DROP TABLE IF EXISTS $tableName");
        //        $tables = array('SUBMITS');
        //        foreach ($tables as $aTable) {
        //            $tableName = $this->prefixTableName($aTable);
        //            $wpdb->query("DROP TABLE IF EXISTS $tableName");
        //        }
    }

    public function addActionsAndFilters() {
        // Add the Admin Config page for this plugin

        // Add Config page as a top-level menu item on the Admin page
        add_action('admin_menu', array(&$this, 'createAdminMenu'));

        // Add Config page into the Plugins menu
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Hook into Contact Form 7 when a form post is made to save the data to the DB
        add_action('wpcf7_before_send_mail', array(&$this, 'saveFormData'));

        // Hook into Fast Secure Contact Form
        add_action('fsctf_mail_sent', array(&$this, 'saveFormData'));
        add_action('fsctf_menu_links', array(&$this, 'fscfMenuLinks'));

        // Shortcode to add a table to a page
        add_shortcode('cf7db-table', array(&$this, 'showTableShortCode')); // deprecated
        add_shortcode('cfdb-table', array(&$this, 'showTableShortCode'));

        // Shortcode to add a JSON to a page
        add_shortcode('cfdb-json', array(&$this, 'showJsonShortCode'));

        // Shortcode to add a value (just text) to a page
        add_shortcode('cfdb-value', array(&$this, 'showValueShortCode'));
    }

    public function addSettingsSubMenuPage() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_submenu_page('wpcf7', //$this->getDBPageSlug(),
                         $displayName . ' Options',
                         'Database Options',
                         'manage_options',
                         get_class($this) . 'Settings',
                         array(&$this, 'settingsPage'));
    }


    /**
     * Function courtesy of Mike Challis, author of Fast Secure Contact Form.
     * Displays Admin Panel links in FSCF plugin menu
     * @return void
     */
    public function fscfMenuLinks() {
        $displayName = $this->getPluginDisplayName();
        echo '
        <p>
      ' . $displayName .
                ' | <a href="admin.php?page=CF7DBPluginSubmissions">' .
                __('Database', 'contact-form-7-to-database-extension') .
                '</a>  | <a href="admin.php?page=CF7DBPluginSettings">' .
                __('Database Options', 'contact-form-7-to-database-extension') .
                '</a> | <a href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/">' .
                __('FAQ', 'contact-form-7-to-database-extension') . '</a>
       </p>
      ';
    }

    /**
     * Callback from Contact Form 7. CF7 passes an object with the posted data which is inserted into the database
     * by this function.
     * Also callback from Fast Secure Contact Form
     * @param  $cf7 object either WPCF7_ContactForm object when coming from CF7 or $fsctf_posted_data object variable
     * if coming from FSCF
     * @return void
     */
    public function saveFormData($cf7) {
        $title = $this->stripSlashes($cf7->title);
        if (in_array($title, $this->getNoSaveForms())) {
            return; // Don't save in DB
        }

        global $wpdb;
        $time = $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME'] : time();
        $ip = (isset($_SERVER['X_FORWARDED_FOR'])) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $tableName = $this->getSubmitsTableName();
        $parametrizedQuery = "INSERT INTO `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value`, `field_order`) VALUES (%s, %s, %s, %s, %s)";
        $parametrizedFileQuery = "UPDATE `$tableName` SET `file` =  '%s' WHERE `submit_time` = '%s' AND `form_name` = '%s' AND `field_name` = '%s' AND `field_value` = '%s'";

        $order = 0;
        $noSaveFields = $this->getNoSaveFields();
        foreach ($cf7->posted_data as $name => $value) {
            $nameClean = $this->stripSlashes($name);
            if (in_array($nameClean, $noSaveFields)) {
                continue; // Don't save in DB
            }

            $value = is_array($value) ? implode($value, ', ') : $value;
            $valueClean = $this->stripSlashes($value);
            $wpdb->query($wpdb->prepare($parametrizedQuery,
                                        $time,
                                        $title,
                                        $nameClean,
                                        $valueClean,
                                        $order++));

            // Store uploaded files - Do as a separate query in case it fails due to max size or other issue
            if ($cf7->uploaded_files) {
                $filePath = $cf7->uploaded_files[$nameClean];
                if ($filePath) {
                    // $content=$wpdb->escape_by_ref(file_get_contents($filePath));
                    $content = file_get_contents($filePath);
                    $wpdb->query($wpdb->prepare($parametrizedFileQuery,
                                                $content,
                                                $time,
                                                $title,
                                                $nameClean,
                                                $valueClean));
                }
            }
        }

        // If the submitter is logged in, capture his id
        if (is_user_logged_in()) {
            $order = ($order < 9999) ? 9999 : $order + 1; // large order num to try to make it always next-to-last
            $current_user = wp_get_current_user(); // WP_User
            $wpdb->query($wpdb->prepare($parametrizedQuery,
                                        $time,
                                        $title,
                                        'Submitted Login',
                                        $current_user->user_login,
                                        $order));
        }

        // Capture the IP Address of the submitter
        $order = ($order < 10000) ? 10000 : $order + 1; // large order num to try to make it always last
        $wpdb->query($wpdb->prepare($parametrizedQuery,
                                    $time,
                                    $title,
                                    'Submitted From',
                                    $ip,
                                    $order));

    }

    /**
     * @param  $time form submit time
     * @param  $formName form name
     * @param  $fieldName field name (should be an upload file field)
     * @return array of (file-name, file-contents) or null if not found
     */
    public function getFileFromDB($time, $formName, $fieldName) {
        global $wpdb;
        $tableName = $this->getSubmitsTableName();
        $parametrizedQuery = "SELECT `field_value`, `file` FROM `$tableName` WHERE `submit_time` = '%s' AND `form_name` = %s AND `field_name` = '%s'";
        $rows = $wpdb->get_results($wpdb->prepare($parametrizedQuery, $time, $formName, $fieldName));
        if ($rows == null || count($rows) == 0) {
            return null;
        }

        return array($rows[0]->field_value, $rows[0]->file);
    }

    public function stripSlashes($text) {
        return get_magic_quotes_gpc() ? stripslashes($text) : $text;
    }

    public function createAdminMenu() {
        $displayName = $this->getPluginDisplayName();
        $roleAllowed = $this->getRoleOption('CanSeeSubmitData');

        //create new top-level menu
//        add_menu_page($displayName . ' Plugin Settings',
//                      'Contact Form Submissions',
//                      'administrator', //$roleAllowed,
//                      $this->getDBPageSlug(),
//                      array(&$this, 'whatsInTheDBPage'));

        // Needed for dialog in whatsInTheDBPage
        wp_enqueue_script('jquery');
        wp_enqueue_style('jquery-ui.css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/themes/base/jquery-ui.css');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('CF7DBdes', $this->getPluginDirUrl() . 'des.js');

        // Put page under CF7's "Contact" page
        add_submenu_page('wpcf7',
                         $displayName . ' Submissions',
                         'Database',
                         $this->roleToCapability($roleAllowed),
                         $this->getDBPageSlug(),
                         array(&$this, 'whatsInTheDBPage'));
    }

    /**
     * Shortcode callback for writing the table of form data. Can be put in a page or post to show that data.
     * Shortcode options:
     * [cfdb-table form="your-form"]                             (shows the whole table with default options)
     * Controlling the Display: Apply your CSS to the table; set the table's 'class' or 'id' attribute:
     * [cfdb-table form="your-form" class="css_class"]           (outputs <table class="css_class"> (default: class="cf7-db-table")
     * [cfdb-table form="your-form" id="css_id"]                 (outputs <table id="css_id"> (no default id)
     * [cfdb-table form="your-form" id="css_id" class="css_class"] (outputs <table id="css_id" class="css_class">
     * Filtering Columns:
     * [cfdb-table form="your-form" show="field1,field2,field3"] (optionally show selected fields)
     * [cfdb-table form="your-form" hide="field1,field2,field3"] (optionally hide selected fields)
     * [cfdb-table form="your-form" show="f1,f2,f3" hide="f1"]   (hide trumps show)
     * Filtering Rows:
     * [cfdb-table form="your-form" filter="field1=value1"]      (show only rows where field1=value1)
     * [cfdb-table form="your-form" filter="field1!=value1"]      (show only rows where field1!=value1)
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2"] (Logical AND the filters using '&&')
     * [cfdb-table form="your-form" filter="field1=value1||field2!=value2"] (Logical OR the filters using '||')
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2||field3=value3&&field4=value4"] (Mixed &&, ||)
     * @param  $atts array short code attributes
     * @return HTML output of shortcode
     */
    public function showTableShortCode($atts) {
        if ($atts['form']) {
            if ($this->canUserDoRoleOption('CanSeeSubmitData') ||
                    $this->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                $options = array('canDelete' => false);
                if ($atts['debug']) {
                    $options['debug'] = $atts['debug'];
                }
                if ($atts['show']) {
                    $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['showColumns'] = $showColumns;
                }
                if ($atts['hide']) {
                    $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['hideColumns'] = $hideColumns;
                }
                if ($atts['id']) {
                    $options['id'] = $atts['id'];
                }
                if ($atts['class']) {
                    $options['class'] = $atts['class'];
                }
                if ($atts['filter']) {
                    $options['filter'] = $atts['filter'];
                }
                $options['fromshortcode'] = true;
                $export = new ExportToHtml();
                $html = $export->export($atts['form'], $options);
                return $html;
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }

    public function showJsonShortCode($atts) {
        if ($atts['form']) {
            if ($this->canUserDoRoleOption('CanSeeSubmitData') ||
                    $this->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                if ($atts['debug']) {
                    $options['debug'] = $atts['debug'];
                }
                if ($atts['show']) {
                    $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['showColumns'] = $showColumns;
                }
                if ($atts['hide']) {
                    $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['hideColumns'] = $hideColumns;
                }
                if ($atts['var']) {
                    $options['var'] = $atts['var'];
                }
                if ($atts['filter']) {
                    $options['filter'] = $atts['filter'];
                }
                $options['html'] = true;
                $options['fromshortcode'] = true;
                $export = new ExportToJson();
                $html = $export->export($atts['form'], $options);
                return $html;
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }

    public function showValueShortCode($atts) {
        if ($atts['form']) {
            if ($this->canUserDoRoleOption('CanSeeSubmitData') ||
                    $this->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                if ($atts['debug']) {
                    $options['debug'] = $atts['debug'];
                }
                if ($atts['show']) {
                    $showColumns = preg_split('/,/', $atts['show'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['showColumns'] = $showColumns;
                }
                if ($atts['hide']) {
                    $hideColumns = preg_split('/,/', $atts['hide'], -1, PREG_SPLIT_NO_EMPTY);
                    $options['hideColumns'] = $hideColumns;
                }
                if ($atts['filter']) {
                    $options['filter'] = $atts['filter'];
                }
                $options['fromshortcode'] = true;
                $export = new ExportToValue();
                $html = $export->export($atts['form'], $options);
                return $html;
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }

    public function getDBPageSlug() {
        return get_class($this) . 'Submissions';
    }

    public function whatsInTheDBPage() {
        $canDelete = $this->canUserDoRoleOption('CanChangeSubmitData');

        ?>
        <table style="width:100%;">
            <tbody>
            <tr>
                <td width="25%" style="font-size:x-small;">
                    <a target="_donate"
                       href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">
                        <img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" alt="Donate">
                    </a>
                </td>
                <td width="25%" style="font-size:x-small;">
                    <a target="_cf7todb"
                       href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension">
                    <?php _e('Rate this Plugin', 'contact-form-7-to-database-extension') ?>
                    </a>
                </td>
                <td width="25%" style="font-size:x-small;">
                    <a target="_cf7todb"
                       href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/">
                    <?php _e('FAQ', 'contact-form-7-to-database-extension') ?>
                    </a>
                </td>
                <td width="25%" style="font-size:x-small;">
                    <a target="_cf7todb"
                       href="http://wordpress.org/tags/contact-form-7-to-database-extension">
                    <?php _e('Support', 'contact-form-7-to-database-extension') ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
        <?php

        global $wpdb;
        $tableName = $this->getSubmitsTableName();

        // Identify which forms have data in the database
        $rows = $wpdb->get_results("select distinct `form_name` from `$tableName` order by `form_name`");
        if ($rows == null || count($rows) == 0) {
            _e('No form submissions in the database', 'contact-form-7-to-database-extension');
            return;
        }
        $htmlFormName = $this->prefix('form');
        $currSelection = $rows[0]->form_name;
        if (isset($_POST['form_name'])) {
            $currSelection = $_POST['form_name'];
        }
        else if (isset($_GET['form_name'])) {
            $currSelection = $_GET['form_name'];
        }
        if (isset($currSelection)) {
            // Check for delete operation
            if (isset($_POST['delete']) && $canDelete) {
                if (isset($_POST['submit_time'])) {
                    $submitTime = $_POST['submit_time'];
                    $wpdb->query("delete from `$tableName` where `form_name` = '$currSelection' and `submit_time` = '$submitTime'");
                }
                else  if (isset($_POST['all'])) {
                    $wpdb->query("delete from `$tableName` where `form_name` = '$currSelection'");
                }
                else {
                    foreach ($_POST as $name => $value) { // checkboxes
                        if ($value == 'row') {
                            $wpdb->query("delete from `$tableName` where `form_name` = '$currSelection' and `submit_time` = '$name'");
                        }
                    }
                }
            }
        }
        // Form selection drop-down list
        $pluginDirUrl = $this->getPluginDirUrl();

        ?>
        <table width="100%" cellspacing="20">
            <tr>
                <td>
                    <form method="post" action="" name="<?php echo $htmlFormName ?>" id="<?php echo $htmlFormName ?>">
                        <select name="form_name" id="form_name" onchange="this.form.submit();">
                        <?php foreach ($rows as $aRow) {
                            $formName = $aRow->form_name;
                            $selected = ($formName == $currSelection) ? "selected" : "";
                            ?>
                                <option value="<?php echo $formName ?>" <?php echo $selected ?>><?php echo $formName ?></option>
                            <?php } ?>
                        </select>
                    </form>
                </td>
                <td>
                    <script type="text/javascript" language="Javascript">
                        function exportData(encSelect) {
                            jQuery("#GoogleCredentialsDialog").dialog({ autoOpen: false, title: <?php _e("'Google Login for Upload'", 'contact-form-7-to-database-extension')?> });
                            var enc = encSelect.options[encSelect.selectedIndex].value;
                            if (enc == 'GSS') {
                                jQuery("#GoogleCredentialsDialog").dialog('open');
                                jQuery("#guser").focus();
                            }
                            else {
                                location.href = '<?php echo $pluginDirUrl ?>export.php?form=<?php echo urlencode($currSelection) ?>&enc=' + enc;
                            }
                        }
                        function uploadGoogleSS() {
                            var key = '3fde789a';
                            var guser = printHex(des(key, jQuery('#guser').attr('value'), 1));
                            var gpwd = printHex(des(key, jQuery('#gpwd').attr('value'), 1));
                            jQuery("#GoogleCredentialsDialog").dialog('close');
                            var form = document.createElement("form");
                            form.setAttribute("method", 'POST');
                            form.setAttribute("action", '<?php echo $pluginDirUrl ?>export.php?form=<?php echo urlencode($currSelection) ?>');
                            var params = {enc: 'GSS', guser: encodeURI(guser), gpwd: encodeURI(gpwd)};
                            for (var pkey in params) {
                                var hiddenField = document.createElement("input");
                                hiddenField.setAttribute("type", "hidden");
                                hiddenField.setAttribute("name", pkey);
                                hiddenField.setAttribute("value", params[pkey]);
                                form.appendChild(hiddenField);
                            }
                            document.body.appendChild(form);
                            form.submit();
                        }
                    </script>
                    <form name="exportcsv" action="">
                        <select size="1" name="enc">
                            <option id="IQY" value="IQY">Excel Internet Query</option>
                            <option id="CSVUTF8BOM" value="CSVUTF8BOM">Excel CVS (UTF8-BOM)</option>
                            <option id="TSVUTF16LEBOM" value="TSVUTF16LEBOM">Excel TSV (UTF16LE-BOM)</option>
                            <option id="CSVUTF8" value="CSVUTF8">Plain CVS (UTF-8)</option>
                            <option id="GSS" value="GSS">Google Spreadsheet</option>
                            <option id="GLD" value="GLD">Google Spreadsheet Live Data</option>
                            <option id="HTML" value="HTML">HTML</option>
                            <option id="JSON" value="JSON">JSON</option>
                        </select>
                        <input name="exportButton" type="button"
                               value="<?php _e('Export', 'contact-form-7-to-database-extension'); ?>"
                               onclick="exportData(this.form.elements['enc'])"/>
                    </form>
                </td>
                <td align="right">
                <?php if ($canDelete) { ?>
                    <form action="" method="post">
                        <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
                        <input name="all" type="hidden" value="y"/>
                        <input name="delete" type="submit"
                               value="<?php _e('Delete All This Form\'s Records', 'contact-form-7-to-database-extension'); ?>"
                               onclick="return confirm('Are you sure you want to delete all the data for this form?')"/>
                    </form>
                <?php } ?>
                </td>
            </tr>
        </table>


        <?php

        // Show table of form data
        ?>
        <div style="overflow:auto; max-height:500px;">
        <?php if ($canDelete) { ?>
        <form action="" method="post">
            <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
            <input name="delete" type="hidden" value="rows"/>
        <?php
        }
        $exporter = new ExportToHtml();
        $exporter->export($currSelection, array('canDelete' => $canDelete));
        if ($canDelete) {
            ?>
        </form>
        <?php } ?>
        </div>
        <div style="margin-top:1em"> <?php // Footer ?>
            <table style="width:100%;">
                <tbody>
                <tr>
                    <td align="center" colspan="4">
                        <span style="font-size:x-small; font-style: italic;">
                        Did you know: This plugin captures data from both
                        <a target="_cf7" href="http://wordpress.org/extend/plugins/contact-form-7/">Contact Form 7</a> and
                        <a target="_fscf" href="http://wordpress.org/extend/plugins/si-contact-form/">Fast Secure Contact Form</a>
                    </span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="4">
                        <span style="font-size:x-small; font-style: italic;">
                        Did you know: You can add this data to your posts and pages using shortcodes
                            <a target="_faq" href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/#cfdb-table">[cfdb-table]</a>
                            <a target="_faq" href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/#cfdb-value">[cfdb-value]</a>
                            <a target="_faq" href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/#cfdb-json">[cfdb-json]</a>
                        </span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="GoogleCredentialsDialog" style="display:none; background-color:#EEEEEE;">
            <table>
                <tbody>
                <tr>
                    <td>User</td>
                    <td><input id="guser" type="text" size="25" value="@gmail.com"/></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input id="gpwd" type="password" size="25" value=""/></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="button" value="<?php _e("Cancel", 'contact-form-7-to-database-extension') ?>"
                               onclick="jQuery('#GoogleCredentialsDialog').dialog('close');"/>
                        <input type="button" value="<?php _e("Upload", 'contact-form-7-to-database-extension') ?>"
                               onclick="uploadGoogleSS()"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * @param  $formName
     * @return CF7DBTableData
     */
    public function &getRowsPivot($formName) {
        global $wpdb;
        $tableName = $this->getSubmitsTableName();
        $rows = $wpdb->get_results("select `submit_time`, `field_name`, `field_value`, `file` IS NOT NULL AS has_file from `$tableName` where `form_name` = '$formName' order by `submit_time` desc, `field_order` asc");

        $tableData = new CF7DBTableData();
        $latestSubmitTime = null;
        $columnsSeen = array();
        foreach ($rows as $aRow) {
            if (!isset($tableData->pivot[$aRow->submit_time])) {
                $tableData->pivot[$aRow->submit_time] = array();
            }
            $tableData->pivot[$aRow->submit_time][$aRow->field_name] = $aRow->field_value;
            if ($aRow->has_file) {
                $tableData->files[$aRow->field_name] = $aRow->field_value;
            }

            // Keep track of all column names seen
            if (!in_array($aRow->field_name, $columnsSeen)) {
                $columnsSeen[] = $aRow->field_name;
            }

            if ($latestSubmitTime == null) {
                // assumes query sorted by submit_time in descending order
                $latestSubmitTime = $aRow->submit_time;
            }
            if ($aRow->submit_time == $latestSubmitTime) {
                // Get the column order of the last posted submission
                $tableData->columns[] = $aRow->field_name;
            }
        }

        // We want to maintain the same order of fields as in the CF7 Form
        // The form post parameters are in the order defined in the HTML and we
        // save that order in the DB. But CF7 form definition may change over time,
        // for example:
        // 1. Form elements may be re-ordered
        // 2. Form elements may be added
        // So we use the order of form elements seen in the last form submission.
        // But it is possible the last form no longer has fields that appeared in
        // an earlier version of it and appear in the DB in older form submissions.
        // We track all seen in $columnsSeen and append any that are missed to the
        // end of the ordering.
        foreach ($columnsSeen as $colSeen) {
            if (!in_array($colSeen, $tableData->columns)) {
                $tableData->columns[] = $colSeen;
            }
        }

        return $tableData;
    }

    /**
     * @return string URL to the Plugin directory. Includes ending "/"
     */
    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__));
    }

    public function formatDate($time) {
        $dateFormat = $this->getOption('SubmitDateTimeFormat');
        return date($dateFormat, $time);
    }

    public function getFileUrl($submitTime, $formName, $fileName) {
        $url = $this->getPluginDirUrl() . 'getFile.php?s=%s&form=%s&field=%s';
        return sprintf($url, $submitTime, urlencode($formName), urlencode($fileName));
    }

    public function getNoSaveFields() {
        return preg_split('/,|;/', $this->getOption('NoSaveFields'), -1, PREG_SPLIT_NO_EMPTY);
    }

    public function getNoSaveForms() {
        return preg_split('/,|;/', $this->getOption('NoSaveForms'), -1, PREG_SPLIT_NO_EMPTY);
    }

    public function getSubmitsTableName() {
        //        $overrideTable = $this->getOption('SubmitTableNameOverride');
        //        if ($overrideTable && "" != $overrideTable) {
        //            return $overrideTable;
        //        }
        return $this->prefixTableName('SUBMITS');
    }
}
