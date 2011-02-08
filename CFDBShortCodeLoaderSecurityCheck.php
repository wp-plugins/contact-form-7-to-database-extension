<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require_once('ShortCodeLoader.php');

abstract class CFDBShortCodeLoaderSecurityCheck extends ShortCodeLoader {

    public function handleShortcode($atts) {
        if ($atts['form']) {
            $plugin = new CF7DBPlugin();
            if ($plugin->canUserDoRoleOption('CanSeeSubmitData') ||
                    $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode')) {
                return $this-> handleShortcodePostSecurityCheck($atts);
            }
            else {
                echo __('Insufficient privileges to display data from form: ', 'contact-form-7-to-database-extension') . $atts['form'];
            }
        }
    }

    public abstract function handleShortcodePostSecurityCheck($atts);
}
