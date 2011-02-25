<?php
/*
   Plugin Name: Contact Form to DB Extension
   Plugin URI: http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/
   Version: 1.7
   Author: Michael Simpson
   Description: Captures form submissions from Contact Form 7 and Fast Secure Contact Form plugins and writes the form data to the database | <a href="admin.php?page=CF7DBPluginSubmissions">Data</a>  | <a href="admin.php?page=CF7DBPluginSettings">Settings</a> | <a href="http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/faq/">FAQ</a>
   Text Domain: contact-form-7-to-database-extension
   License: GPL3
  */

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function CF7DBPlugin_PhpVersionCheck() {
    $minimalRequiredPhpVersion = '5.0';
    if (version_compare(phpversion(), $minimalRequiredPhpVersion) < 0) {
        trigger_error(
            '<p>Error: Contact Form 7 to DB Plugin requires a newer version of PHP to be running.</p>' .
                    '<ul>' .
                    '<li>' . __('Minimal version of PHP required: ', 'contact-form-7-to-database-extension') . '<strong>' . $minimalRequiredPhpVersion . '</strong></li>' .
                    '<li>' . __('Your server\'s PHP version: ', 'contact-form-7-to-database-extension') . '<strong>' . phpversion() . '</strong></li>' .
                    '</ul>' .

                    '<p>' . __('When using the Apache web server, typically you can configure it to use PHP5 by doing the following:', 'contact-form-7-to-database-extension') .
                    '<ul>' .
                    '<li>' . __('Locate and edit this file, located at the top directory of your WordPress installation: ', 'contact-form-7-to-database-extension') .
                    '<strong><code>.htaccess</code></strong></li>' .
                    '<li>' . __('Add these two lines to the file:', 'contact-form-7-to-database-extension') .
                    '<br/><code><pre>
AddType x-mapp-php5 .php
AddHandler x-mapp-php5 .php
</pre></code></ul>'
            , E_USER_ERROR); // E_USER_ERROR seems to be handled OK in WP. It gives a notice in the Plugins Page
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function CF7DBPlugin_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('contact-form-7-to-database-extension', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
CF7DBPlugin_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (CF7DBPlugin_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('CF7DBPlugin_init.php');
    CF7DBPlugin_init(__FILE__);
}
