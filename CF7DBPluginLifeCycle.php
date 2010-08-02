<?php

require_once('CF7DBInstallIndicator.php');

/**
 * All the basic plugin life cycle functionality is implemented herein.
 * A Plugin is expected to subclass this class and override method to inject
 * its own specific behaviors.
 *
 * @author Michael Simpson
 */

class CF7DBPluginLifeCycle extends CF7DBInstallIndicator {

    public function install() {

        // Initialize Plugin Options
        $this->initOptions();

        // Initialize DB Tables used by the plugin
        $this->installDatabaseTables();

        // Other Plugin initialization - for the plugin writer to override as needed
        $this->otherInstall();

        // To avoid running install() more then once
        $this->markAsInstalled();
    }

    public function uninstall() {
        $this->otherUninstall();
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    public function activate() {
    }

    public function deactivate() {
    }

    protected function initOptions() {
        /**
         * @var $pluginDisplayName string a name for the Plugin to be displayed to the user on a web page
         */
        $pluginDisplayName = "Contact Form 7 to DB Extension";

        /**
         * @var $unPrefixedOptionsMetaData array
         * Define your options metadata here as an array, where each element in the array
         * key: an option name for the key (this name will be given a prefix when stored in
         * the database to ensure it does not conflict with other plugin options)
         * value: can be one of two things:
         *   (1) string display name for displaying the name of the option to the user on a web page
         *   (2) array where the first element is a display name (as above) and the rest of
         *       the elements are choices of values that the user can select
         * e.g.
         * array(
         *   "item" => "Item:",             // key => display-name
         *   "rating" => array(             // key => array ( display-name, choice1, choice2, ...)
         *       "Rating:", "Excellent", "Good", "Fair", "Poor")
         */
        $unPrefixedOptionsMetaData = array(
            // todo: fill out options
            //    "item1" => "Item 1",
            //    "item2" => array(
            //        "Item 2", "Excellent", "Good", "Fair", "Poor")
        );
        $this->setPluginDisplayName($pluginDisplayName);
        $this->setOptionMetaData($unPrefixedOptionsMetaData);
    }

    public function addActionsAndFilters() {
    }

    protected function installDatabaseTables() {
    }

    protected function unInstallDatabaseTables() {
    }

    protected function otherInstall() {
    }

    protected function otherUninstall() {
    }

    /**
     * Puts the configuration page in the Plugins menu by default.
     * Override to put it elsewhere or create a set of submenus
     * Override with an empty implementation if you don't want a configuration page
     * @return void
     */
    public function addSettingsSubMenuPage() {
        $this->addSettingsSubMenuPageToPluginsMenu();
        //$this->addSettingsSubMenuPageToSettingsMenu();
    }


    protected function requireExtraPluginFiles() {
        require_once(plugin_dir_path(__FILE__) . '../../../wp-includes/pluggable.php');
        require_once(plugin_dir_path(__FILE__) . '../../../wp-admin/includes/plugin.php');
    }

    protected function addSettingsSubMenuPageToPluginsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_submenu_page('plugins.php',
                         $displayName,
                         $displayName,
                         'manage_options',
                         get_class($this),
                         array(&$this, 'settingsPage'));
    }


    protected function addSettingsSubMenuPageToSettingsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_options_page($displayName,
                         $displayName,
                         'manage_options',
                         get_class($this),
                         array(&$this, 'settingsPage')); 
    }

    /**
     * @param  $name string name of a database table
     * @return string input prefixed with the Wordpress DB table prefix
     * plus the prefix for this plugin to avoid table name collisions
     */
    protected function prefixTableName($name) {
        global $wpdb;
        return $wpdb->prefix . $this->prefix($name);
    }


}
