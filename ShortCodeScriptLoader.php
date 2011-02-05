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

require_once('ShortCodeLoader.php');

/**
 * Adapted from this excellent article:
 * http://scribu.net/wordpress/optimal-script-loading.html
 *
 * The idea is you have a shortcode that needs a script loaded, but you only
 * want to load it if the shortcode is actually called.
 */
abstract class ShortCodeScriptLoader extends ShortCodeLoader {

    var $doAddScript;

    public function register($shortcodeName) {
        $this->registerShortcodeToFunction($shortcodeName, 'handle_shortcode_wrapper');

        // It will be too late to enqueue the script in the header,
        // so have to add it to the footer
        add_action('wp_footer', array($this, 'add_script_wrapper'));
    }

    public function handle_shortcode_wrapper($atts) {
        // Flag that we need to add the script
        $this->doAddScript = true;
        return $this->handle_shortcode($atts);
    }

    // Defined in super-class:
    //public abstract function handle_shortcode($atts);

    public function add_script_wrapper() {
        // Only add the script if the shortcode was actually called
        if ($this->doAddScript) {
            $this->add_script();
        }
    }

    /**
     * @abstract override this function with calls to insert scripts needed by your shortcode in the footer
     * Example:
     *   wp_register_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
     *   wp_print_scripts('my-script');
     * @return void
     */
    public abstract function add_script();

}
