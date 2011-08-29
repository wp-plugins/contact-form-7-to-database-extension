<?php
/*
    "Community Yard Sale Plugin for WordPress" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Community Yard Sale Plugin for WordPress.

    Community Yard Sale Plugin for WordPress is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Community Yard Sale Plugin for WordPress is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Community Yard Sale Plugin for WordPress.
    If not, see <http://www.gnu.org/licenses/>.
*/

function YS_init($file) {

    require_once('YSPlugin.php');
    $aPlugin = new YSPlugin();

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

    if (!$file) {
        $file = __FILE__;
    }
    // Register the Plugin Activation Hook
    register_activation_hook($file, array(&$aPlugin, 'activate'));


    // Register the Plugin Deactivation Hook
    register_deactivation_hook($file, array(&$aPlugin, 'deactivate'));
}
