<?php
/*
   Plugin Name: Contact Form 7 to DB Extension
   Plugin URI: http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/
   Version: 1.4
   Author: Michael Simpson
   Description: You also need to install and activate Contact Form 7 for this to work. It is an extension that writes the form data to the database | <a href="admin.php?page=CF7DBPluginSubmissions">Data</a>  | <a href="admin.php?page=CF7DBPluginSettings">Settings</a> | <a href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/">FAQ</a>
   Text Domain: contact-form-7-to-database-extension
   License: GPL2
  */

function init_CF7DBPlugin() {

    require_once('CF7DBPlugin.php');
    $aPlugin = new CF7DBPlugin();

    // Install the plugin
    // NOTE: this file gets run each time you *activate* the plugin.
    // So in WP when you "install" the plugin, all that does it dump its files in the plugin-templates directory
    // but it does not call any of its code.
    // So here, the plugin tracks whether or not it has run its install operation, and we ensure it is run only once
    // on the first activation
    if (!$aPlugin->isInstalled()) {
        $aPlugin->install();
    }
    else {
        // Perform any version-upgrade activities prior to activation (e.g. database changes)
        $aPlugin->upgrade();
    }

    // Add callbacks to hooks
    $aPlugin->addActionsAndFilters();

    // Register the Plugin Activation Hook
    register_activation_hook(__FILE__, array(&$aPlugin, 'activate'));


    // Register the Plugin Deactivation Hook
    register_deactivation_hook(__FILE__, array(&$aPlugin, 'deactivate'));
}

init_CF7DBPlugin();