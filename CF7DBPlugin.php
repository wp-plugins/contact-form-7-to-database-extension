<?php

require_once('CF7DBPluginLifeCycle.php');
require_once('CF7DBTableData.php');

/**
 * Implementation for CF7DBPluginLifeCycle.
 * Plugin writer: put your code here. Also enter code in settings.php
 */

class CF7DBPlugin extends CF7DBPluginLifeCycle {

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
            `form_name` VARCHAR(127),
            `field_name` VARCHAR(127),
            `field_value` LONGTEXT )");
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

        // Add Config page into the Plugins menu
        //add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Add Config page as a top-level menu item on the Admin page
        add_action('admin_menu', array(&$this, 'createAdminMenu'));

        // Hook into Contact Form 7 when a form post is made to save the data to the DB
        add_action('wpcf7_before_send_mail', array(&$this, 'saveFormData'));
    }


    public function saveFormData($cf7) {
        global $wpdb;
        $time = time();
        $ip = ($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $tableName = $this->prefixTableName('SUBMITS');
        foreach ($cf7->posted_data as $name => $value) {
            $value = is_array($value) ? implode($value, ", ") : $value;
            $wpdb->query("INSERT INTO `$tableName`
            (`submit_time`, `form_name`, `field_name`, `field_value`) VALUES
            ('$time', '$cf7->title', '$name', '$value')");
        }

        // Capture the IP Address of the submitter
        $wpdb->query("INSERT INTO `$tableName`
        (`submit_time`, `form_name`, `field_name`, `field_value`) VALUES
        ('$time', '$cf7->title', 'Submitted From', '$ip')");
    }

    public function createAdminMenu() {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
                      $pluginName,
                      'administrator',
                      get_class($this),
                      array(&$this, 'whatsInTheDBPage'));

    }

    public function whatsInTheDBPage() {
        //print_r($_POST);
        ?><h2>Form Submissions</h2><?php
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');

        // Identify which forms have data in the database
        $rows = $wpdb->get_results("select distinct `form_name` from `$tableName` order by `form_name`");
        if ($rows == null || count($rows) == 0) {
            ?>No form submissions in the database<?php
                return;
        }
        $htmlFormName = $this->prefix('form');
        $currSelection = $rows[0]->form_name;
        if (isset($_POST['form_name'])) {
            $currSelection = $_POST['form_name'];
            // Check for delete operation
            if (isset($_POST['delete'])) {
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
        ?>
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
        <?php

        // Query DB for the data for that form
        $tableData = $this->getRowsPivot($currSelection);

        // Show table of form data
        $style = "style='padding:5px; border-width:1px; border-style:solid; border-color:gray;'";
        $pluginDirUrl = $this->getPluginDirUrl();
        ?>
        <table cellspacing="0" style="margin-top:1em; border-width:0px; border-style:solid; border-color:gray;">
            <thead>
            <th></th>
            <th <?php echo $style ?>>Submitted</th>
            <?php foreach ($tableData->columns as $aCol) {
                echo "<th $style>$aCol</th>";
            } ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                ?>
                <tr>
                    <td>
                        <form action="" method="post">
                            <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
                            <input name="submit_time" type="hidden" value="<?php echo $submitTime ?>"/>
                            <input name="delete" type="hidden" value="row"/>
                            <input type="image" src="<?php echo $pluginDirUrl ?>delete.gif" alt="Delete Row" onchange="this.form.submit()"/>
                        </form>
                    </td>
                    <td <?php echo $style ?>><?php echo date('Y-m-d', $submitTime) ?></td>
                <?php
                    foreach ($tableData->columns as $aCol) {
                    $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                    echo "<td $style>$cell</td>";
                }
                ?></tr><?php

            } ?>
            </tbody>
        </table>

        <table cellspacing="20" style="margin-top:2em;">
            <tr>
                <td>
                    <form action="">
                        <input name="exportcsv" type="button" value="Export to CSV (Excel) File"
                                onclick="document.getElementById('export').src = '<?php echo $pluginDirUrl ?>exportCSV.php?form_name=<?php echo urlencode($currSelection) ?>'"/>
                    </form>
                </td>
                <td>
                    <form action="" method="post">
                        <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
                        <input name="delete" type="submit" value="Delete All This Form's Records" onclick="return confirm('Are you sure you want to delete all the data for this form?')"/>
                    </form>
                </td>
            </tr>
        </table>

        <iframe
                id='export'
                name='export'
                frameborder='0'
                style='display:block'
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
}
