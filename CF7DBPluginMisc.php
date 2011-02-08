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

class CF7DBPluginMisc {

    /**
     * Utility function wrapping call to PHP stripslashes()
     * @param  $text string
     * @return string
     */
    public function stripSlashes($text) {
        return get_magic_quotes_gpc() ? stripslashes($text) : $text;
    }

    /**
     * @return string URL to the Plugin directory. Includes ending "/"
     */
    public function getPluginDirUrl() {
        //return WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__));
        return $this->getPluginFileUrl('/');
    }


    /**
     * @param string $pathRelativeToThisPluginRoot points to a file with relative path from
     * this plugin's root dir. I.e. file "des.js" in the root of this plugin has
     * url = $this->getPluginUrl('des.js');
     * If it was in a subfolder "js" then you would use
     *    $this->getPluginUrl('js/des.js');
     * @return string full url to input file
     */
    public function getPluginFileUrl($pathRelativeToThisPluginRoot = '') {
        return plugins_url($pathRelativeToThisPluginRoot, __FILE__);
    }


}
