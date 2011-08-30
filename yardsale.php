<?php
/*
   Plugin Name: Community Yard Sale
   Plugin URI: http://wordpress.org/extend/plugins/community-yard-sale/
   Version: 1.0.1
   Author: Michael Simpson
   Description: Short codes for community yard sale entry form and listings. Uses Google Maps and a filterable table to show listings. | <a href="admin.php?page=YSPluginSettings">Settings</a>
   Text Domain: yardsale
   License: GPL3
  */


$YS_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function YS_noticePhpVersionWrong() {
    global $YS_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Community Yard Sale" requires a newer version of PHP to be running.',  'yardsale').
            '<br/>' . __('Minimal version of PHP required: ', 'yardsale') . '<strong>' . $YS_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'yardsale') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function YS_PhpVersionCheck() {
    global $YS_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $YS_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'YS_noticePhpVersionWrong');
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
function YSPlugin_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('yardsale', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
YSPlugin_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (YS_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('YS_init.php');
    YS_init(__FILE__);
}
