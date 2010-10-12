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
        return "1.2.2";
    }


    public function &getPluginDisplayName() {
        return "Contact Form 7 to DB Extension";
    }

    public function &getOptionMetaData() {
        return array(
            'CanSeeSubmitData' => array('Can See Submission data', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
            'CanChangeSubmitData' => array('Can Delete Submission data', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
        );
    }

    public function upgrade() {
        $version = $this->getVersionSaved();
        if (!$version || $version == "") { // Prior to storing version in options (pre 1.2)
            // DB Schema Upgrade to support i18n using UTF-8
            global $wpdb;
            $tableName = $this->prefixTableName('SUBMITS');
            $wpdb->query("ALTER TABLE $tableName MODIFY form_name VARCHAR(127) CHARACTER SET utf8");
            $wpdb->query("ALTER TABLE $tableName MODIFY field_name VARCHAR(127) CHARACTER SET utf8");
            $wpdb->query("ALTER TABLE $tableName MODIFY field_value longtext CHARACTER SET utf8");

            // Remove obsolete options
            $this->deleteOption('_displayName');
            $this->deleteOption('_metatdata');
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
            `field_value` LONGTEXT CHARACTER SET utf8)");
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
        $parametrizedQuery = "INSERT INTO `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value`) VALUES (%s, %s, %s, %s)";

        $title = $this->stripSlashes($cf7->title);
        foreach ($cf7->posted_data as $name => $value) {
            $value = is_array($value) ? implode($value, ", ") : $value;
            $wpdb->query($wpdb->prepare($parametrizedQuery,
                                        $time,
                                        $title,
                                        $this->stripSlashes($name),
                                        $this->stripSlashes($value)));
        }

        // Capture the IP Address of the submitter
        $wpdb->query($wpdb->prepare($parametrizedQuery,
                                    $time,
                                    $title,
                                    'Submitted From',
                                    $ip));

        // todo: How to handle file uploads? foreach ( (array) $cf7->uploaded_files as $name => $path ) { }
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
                else {
                    $wpdb->query("delete from `$tableName` where `form_name` = '$currSelection'");
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
                                onclick="document.getElementById('export').src =
                                '<?php echo $pluginDirUrl ?>exportCSV.php?form_name=<?php echo urlencode($currSelection) ?>&encoding=' +
                                document.forms['exportcsv'].elements['encoding'].options[document.forms['exportcsv'].elements['encoding'].selectedIndex].value;"/>
                    </form>
                </td>
                <td align="right">
                    <?php if ($canDelete) { ?>
                    <form action="" method="post">
                        <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
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
        $style = "style='padding:5px; border-width:1px; border-style:solid; border-color:gray; font-size:x-small;'";
        ?>
        <div style="overflow:auto; max-height:500px;">
        <table cellspacing="0" style="margin-top:1em; border-width:0px; border-style:solid; border-color:gray; font-size:x-small;">
            <thead>
            <?php if ($canDelete) { ?> <th></th> <?php } ?>
            <th <?php echo $style ?>>Submitted</th>
            <?php foreach ($tableData->columns as $aCol) {
                echo "<th $style>$aCol</th>";
            } ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                ?>
                <tr>
                <?php if ($canDelete) { ?>
                    <td>
                        <form action="" method="post">
                            <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
                            <input name="submit_time" type="hidden" value="<?php echo $submitTime ?>"/>
                            <input name="delete" type="hidden" value="row"/>
                            <input type="image" src="<?php echo $pluginDirUrl ?>delete.gif" alt="Delete Row" onchange="this.form.submit()"/>
                        </form>
                    </td>
                <?php } ?>
                    <td <?php echo $style ?>><div style="max-height:100px; overflow:auto;"><?php echo $this->formatDate($submitTime) ?></div></td>
                <?php
                    foreach ($tableData->columns as $aCol) {
                    $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                    $cell = htmlentities($cell); // no HTML injection
                    $cell = str_replace("\r\n", "<br/>", $cell); // preserve DOS line breaks
                    $cell = str_replace("\n", "<br/>", $cell); // preserve UNIX line breaks
                    echo "<td $style><div style=\"max-height:100px; overflow:auto;\">$cell</div></td>";
                }
                ?></tr><?php

            } ?>
            </tbody>
        </table>
        </div>

        <iframe
                id='export'
                name='export'
                frameborder='0'
                style='display:none'<?php // todo  ?>
                src=''></iframe>
        <?php
    }

    /**
     * @param  $formName
     * @return CF7DBTableData
     */
    public function &getRowsPivot($formName) {
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');
        $rows = $wpdb->get_results("select `submit_time`, `field_name`, `field_value` from `$tableName` where `form_name` = '$formName' order by `submit_time` desc");

        $tableData = new CF7DBTableData();

        foreach ($rows as $aRow) {
            if (!isset($tableData->pivot[$aRow->submit_time])) {
                $tableData->pivot[$aRow->submit_time] = array();
            }
            $tableData->pivot[$aRow->submit_time][$aRow->field_name] = $aRow->field_value;
            $tableData->columns[count($tableData->columns)] = $aRow->field_name;
        }
        $tableData->columns = array_unique($tableData->columns);

        return $tableData;
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    }

    public function formatDate($time) {
        return date('Y-m-d H:i:s P', $time);
    }
}
