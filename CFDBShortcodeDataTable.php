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

require_once('ShortCodeScriptLoader.php');
require_once('CFDBShortcodeTable.php');

class CFDBShortcodeDataTable extends ShortCodeScriptLoader {

    public function handle_shortcode($atts) {
        $atts['useDT'] = true;
        $sc = new CFDBShortcodeTable();
        return $sc->handle_shortcode($atts);
    }

    public function register($shortcodeName) {
        parent::register($shortcodeName);
        // Unfortunately, can't put styles in the footer so we have to always add this style sheet
        wp_enqueue_style('datatables-demo', 'http://www.datatables.net/release-datatables/media/css/demo_table.css');
    }

    public function add_script() {
//        wp_register_style('datatables-demo', 'http://www.datatables.net/release-datatables/media/css/demo_table.css');
//        wp_print_styles('datatables-demo');

        wp_register_script('datatables', 'http://www.datatables.net/release-datatables/media/js/jquery.dataTables.js', array('jquery'), false, true);
        wp_print_scripts('datatables');
    }

}
