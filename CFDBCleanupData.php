<?php
/*
    "Contact Form to Database" Copyright (C) 2011-2015 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database.

    Contact Form to Database is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database.
    If not, see <http://www.gnu.org/licenses/>.
*/

require_once('CF7DBPlugin.php');

class CFDBCleanupData {

    /**
     * @var CF7DBPlugin
     */
    var $plugin;

    /**
     * @param $plugin CF7DBPlugin
     */
    function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Fix entries from different forms with same submit_time
     * @return int number of items fixed in the DB
     */
    public function cleanup() {
        global $wpdb;

        $table = $this->plugin->getSubmitsTableName();
        $sql = sprintf('select * from (select submit_time, count(form_name) as count
        from (
            select distinct submit_time, form_name from %s) t group by submit_time
        ) u  where count > 1', $table);
        $results = $wpdb->get_results($sql, ARRAY_A);
        //print_r($results); // debug
        if (!$results) {
            return 0;
        }

        $stSql = 'select distinct submit_time, form_name from ' . $table . '  where submit_time = %F';
        $inDBSql = 'select count(submit_time) from ' . $table . ' where submit_time = %F';
        $updateSql = 'update '. $table . ' set submit_time = %F where submit_time = %F and form_name = %s';
                $count = 0;
        foreach($results as $row) {
            $stResults = $wpdb->get_results($wpdb->prepare($stSql, $row['submit_time']), ARRAY_A);
            $idx = 0;
            foreach($stResults as $stResult) {
                if ($idx++ == 0) {
                    continue;
                }
                $newST = $stResult['submit_time'];
                while(true) {
                    $newST = $newST + 0.0001; // Get new submit time
                    $inDbAlready = $wpdb->get_var($wpdb->prepare($inDBSql, $newST));
                    if (! $inDbAlready) {
                        $wpdb->query($wpdb->prepare($updateSql, $newST, $stResult['submit_time'], $stResult['form_name']));
                        ++$count;
                        break;
                    }
                }
            }
        }
        return $count;
    }
}
