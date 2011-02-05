<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2010 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
 
abstract class ShortCodeLoader {

    /**
     * @param  $shortcodeName mixed either string name of the shortcode
     * (as it would appear in a post, e.g. [shortcodeName])
     * or an array of such names in case you want to have more than one name
     * for the same shortcode
     * @return void
     */
    public function register($shortcodeName) {
        $this->registerShortcodeToFunction($shortcodeName, 'handle_shortcode');
    }

    /**
     * @param  $shortcodeName mixed either string name of the shortcode
     * (as it would appear in a post, e.g. [shortcodeName])
     * or an array of such names in case you want to have more than one name
     * for the same shortcode
     * @param  $functionName string name of public function in this class to call as the
     * shortcode handler
     * @return void
     */
    protected function registerShortcodeToFunction($shortcodeName, $functionName) {
        if (is_array($shortcodeName)) {
            foreach ($shortcodeName as $aName) {
                add_shortcode($aName, array($this, $functionName));
            }
        }
        else {
            add_shortcode($shortcodeName, array($this, $functionName));
        }
    }

    /**
     * @abstract Override this function and add actual shortcode handling here
     * @param  $atts shortcode inputs
     * @return string shortcode content
     */
    public abstract function handle_shortcode($atts);

}
