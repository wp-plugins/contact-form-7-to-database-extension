<?php

require_once('CF7DBPluginLifeCycle.php');
require_once('CF7DBTableData.php');

/**
 * Implementation for CF7DBPluginLifeCycle.
 */

class CF7DBPlugin extends CF7DBPluginLifeCycle {

    /**
     * @return string
     */
    public function getVersion() {
        return "1.3.1";
    }


    public function &getPluginDisplayName() {
        return "Contact Form 7 to DB Extension";
    }

    public function &getOptionMetaData() {
        return array(
            'CanSeeSubmitData' => array('Can See Submission data', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
            'CanChangeSubmitData' => array('Can Delete Submission data', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
            'ShowLineBreaksInDataTable' => array('Show line breaks in submitted data table', 'true', 'false'),
            'SubmitDateTimeFormat' => array('Submit <a target="_blank" href="http://php.net/manual/en/function.date.php">Date-Time Display Format</a>')
        );
    }

    public function upgrade() {
        global $wpdb;
        $version = $this->getVersionSaved();
        if (!$version || $version == "") { // Prior to storing version in options (pre 1.2)
            // DB Schema Upgrade to support i18n using UTF-8
            $tableName = $this->prefixTableName('SUBMITS');
            $wpdb->query("ALTER TABLE $tableName MODIFY form_name VARCHAR(127) CHARACTER SET utf8");
            $wpdb->query("ALTER TABLE $tableName MODIFY field_name VARCHAR(127) CHARACTER SET utf8");
            $wpdb->query("ALTER TABLE $tableName MODIFY field_value longtext CHARACTER SET utf8");

            // Remove obsolete options
            $this->deleteOption('_displayName');
            $this->deleteOption('_metatdata');
        }

        $submitDateTimeFormat = $this->getOption('SubmitDateTimeFormat');
        if (!$submitDateTimeFormat || $submitDateTimeFormat == '') {
            $this->addOption('SubmitDateTimeFormat', 'Y-m-d H:i:s P');
        }

        if ($this->isSavedVersionLessThan('1.3.1')) {
            $tableName = $this->prefixTableName('SUBMITS');
            $wpdb->query("ALTER TABLE $tableName ADD COLUMN `field_order` INTEGER");
            $wpdb->query("ALTER TABLE $tableName ADD COLUMN `file` LONGBLOB");
            $wpdb->query("ALTER TABLE  $tableName ADD INDEX  `submit_time_idx` ( `submit_time` )");
        }


        // Post-upgrade, set the current version in the options
        if ($version != $this->getVersion()) {
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
        $tableName = $this->prefixTableName('SUBMITS');
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tableName (
            `submit_time` INTEGER NOT NULL,
            `form_name` VARCHAR(127) CHARACTER SET utf8,
            `field_name` VARCHAR(127) CHARACTER SET utf8,
            `field_value` LONGTEXT CHARACTER SET utf8),
            `field_order` INTEGER,
            `file` LONGBLOB");
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
        $tables = array('SUBMITS');
        foreach ($tables as $aTable) {
            $tableName = $this->prefixTableName($aTable);
            $wpdb->query("DROP TABLE IF EXISTS $tableName");
        }
    }

    public function addActionsAndFilters() {
        // Add the Admin Config page for this plugin

        // Add Config page as a top-level menu item on the Admin page
        add_action('admin_menu', array(&$this, 'createAdminMenu'));

        // Add Config page into the Plugins menu
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Hook into Contact Form 7 when a form post is made to save the data to the DB
        add_action('wpcf7_before_send_mail', array(&$this, 'saveFormData'));
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


    public function saveFormData($cf7) {
        global $wpdb;
        $time = $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME'] : time();
        $ip = ($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $tableName = $this->prefixTableName('SUBMITS');
        $parametrizedQuery = "INSERT INTO `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value`, `field_order`) VALUES (%s, %s, %s, %s, %s)";
        $parametrizedFileQuery = "UPDATE `$tableName` SET `file` =  '%s' WHERE `submit_time` = '%s' AND `form_name` = %s AND `field_name` = '%s' AND `field_value` = '%s'";

        $title = $this->stripSlashes($cf7->title);
        $order = 0;
        foreach ($cf7->posted_data as $name => $value) {
            $value = is_array($value) ? implode($value, ", ") : $value;
            $nameClean = $this->stripSlashes($name);
            $valueClean = $this->stripSlashes($value);
            $wpdb->query($wpdb->prepare($parametrizedQuery,
                                        $time,
                                        $title,
                                        $nameClean,
                                        $valueClean,
                                        $order++));

            // Store uploaded files - Do as a separate query in case it fails due to max size or other issue
            if ( $cf7->uploaded_files) {
                $filePath = $cf7->uploaded_files[$nameClean];
                if ($filePath) {
                   // $content=$wpdb->escape_by_ref(file_get_contents($filePath));
                    $content=file_get_contents($filePath);
                    $wpdb->query($wpdb->prepare($parametrizedFileQuery,
                        $content,
                        $time,
                        $title,
                        $nameClean,
                        $valueClean));
                }
            }
        }

        // Capture the IP Address of the submitter
        $wpdb->query($wpdb->prepare($parametrizedQuery,
                                    $time,
                                    $title,
                                    'Submitted From',
                                    $ip));

    }

    /**
     * @param  $time form submit time
     * @param  $formName form name
     * @param  $fieldName field name (should be an upload file field)
     * @return array of (file-name, file-contents) or null if not found
     */
    public function &getFileFromDB($time, $formName, $fieldName) {
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');
        $parametrizedQuery = "SELECT `field_value`, `file` FROM `$tableName` WHERE `submit_time` = '%s' AND `form_name` = %s AND `field_name` = '%s'";
        $rows = $wpdb->get_results($wpdb->prepare($parametrizedQuery, $time, $formName, $fieldName));
        if ($rows == null || count($rows) == 0) {
            return null;
        }

        return array($rows[0]->field_value, $rows[0]->file);
    }

    public function &stripSlashes($text) {
        return get_magic_quotes_gpc() ? stripslashes($text) : $text;
    }

    public function createAdminMenu() {
        $displayName = $this->getPluginDisplayName();
        $roleAllowed = $this->getRoleOption('CanSeeSubmitData');

        //create new top-level menu
//        add_menu_page($displayName . ' Plugin Settings',
//                      "Contact Form Submissions",
//                      'administrator', //$roleAllowed,
//                      $this->getDBPageSlug(),
//                      array(&$this, 'whatsInTheDBPage'));

        // Put page under CF7's "Contact" page
        add_submenu_page('wpcf7',
                         $displayName . ' Submissions',
                         'Database',
                         $this->roleToCapability($roleAllowed),
                         $this->getDBPageSlug(),
                         array(&$this, 'whatsInTheDBPage'));
    }

    public function getDBPageSlug() {
        return get_class($this) . 'Submissions';
    }

    public function whatsInTheDBPage() {
        //print_r($_POST);
        $canDelete = $this->canUserDoRoleOption('CanChangeSubmitData');

        ?>
        <table style="width:100%;">
            <tbody><tr>
                <td style="font-size:x-small;"><a target="_cf7todb" href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension">CF7-to-DB Extension</a></td>
                <td style="font-size:x-small;"></td>
                <td style="font-size:x-small;"><a target="_cf7todb" href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/">FAQ</a></td>
            </tr></tbody>
        </table>
        <?php

        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');

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
                    <form name="exportcsv" action="">
                        <select size="1" name="encoding">
                            <option id="IQY" value="IQY">Excel Internet Query</option>
                            <option id="UTF8" value="UTF8">Spreadsheet (CSV UTF-8)</option>
                            <option id="UTF16LE" value="UTF16LE">Excel-specific (TSV UTF-16LE)</option>
                        </select>
                        <input name="exportcsv" type="button" value="<?php _e('Export to File', 'contact-form-7-to-database-extension'); ?>"
                                onclick="location.href=
                                '<?php echo $pluginDirUrl ?>exportCSV.php?form_name=<?php echo urlencode($currSelection) ?>&encoding=' +
                                document.forms['exportcsv'].elements['encoding'].options[document.forms['exportcsv'].elements['encoding'].selectedIndex].value;"/>
                    </form>
                </td>
                <td align="right">
                    <?php if ($canDelete) { ?>
                    <form action="" method="post">
                        <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
                        <input name="all" type="hidden" value="y"/>
                        <input name="delete" type="submit" value="<?php _e('Delete All This Form\'s Records', 'contact-form-7-to-database-extension'); ?>" onclick="return confirm('Are you sure you want to delete all the data for this form?')"/>
                    </form>
                    <?php } ?>
                </td>
            </tr>
        </table>


        <?php

        // Query DB for the data for that form
        $tableData = $this->getRowsPivot($currSelection);

        // Show table of form data
        $style = "padding:5px; border-width:1px; border-style:solid; border-color:gray; font-size:x-small;";
        $thStyle = $style." background-color:#E8E8E8;";
        ?>
        <div style="overflow:auto; max-height:500px;">
        <?php if ($canDelete) { ?>
        <form action="" method="post">
            <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
            <input name="delete" type="hidden" value="rows"/>
        <?php } ?>
        <table cellspacing="0" style="margin-top:1em; border-width:0px; border-style:solid; border-color:gray; font-size:x-small;">
            <thead>
            <?php if ($canDelete) { ?>
            <th>
                <input type="image" src="<?php echo $pluginDirUrl ?>delete.gif" alt="<?php _e('Delete Selected')?>" onchange="this.form.submit()"/>
            </th>
            <?php } ?>
            <th style="<?php echo $thStyle ?>">Submitted</th>
            <?php foreach ($tableData->columns as $aCol) {
                echo "<th style=\"$thStyle\">$aCol</th>";
            } ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                ?>
                <tr>
                <?php if ($canDelete) { ?>
                    <td align="center">
                        <input type="checkbox" name="<?php echo $submitTime ?>" value="row" />
                    </td>
                <?php } ?>
                    <td style="<?php echo $style ?>"><div style="max-height:100px; overflow:auto;"><?php echo $this->formatDate($submitTime) ?></div></td>
                <?php
                    $showLineBreaks = $this->getOption('ShowLineBreaksInDataTable');
                    $showLineBreaks = 'false' != $showLineBreaks;
                    foreach ($tableData->columns as $aCol) {
                        $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                        $cell = htmlentities($cell, null, 'UTF-8'); // no HTML injection
                        if ($showLineBreaks) {
                            $cell = str_replace("\r\n", "<br/>", $cell); // preserve DOS line breaks
                            $cell = str_replace("\n", "<br/>", $cell); // preserve UNIX line breaks
                        }
                        if ($tableData->files[$aCol] && "" != $cell) {
                            $fileUrl = sprintf("${pluginDirUrl}getFile.php?s=%s&form=%s&field=%s", $submitTime, urlencode($currSelection), urlencode($aCol));
                            $cell = "<a href=\"$fileUrl\">$cell</a>";
                        }
                        echo "<td style=\"$style\"><div style=\"max-height:100px; overflow:auto;\">$cell</div></td>";
                    }
                ?></tr><?php

            } ?>
            </tbody>
        </table>
        <?php if ($canDelete) { ?>
        </form>
        <?php } ?>
        </div>
        <?php
    }

    /**
     * @param  $formName
     * @return CF7DBTableData
     */
    public function &getRowsPivot($formName) {
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');
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
        return WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    }

    public function formatDate($time) {
        $dateFormat = $this->getOption('SubmitDateTimeFormat');
        return date($dateFormat, $time);
    }
}
