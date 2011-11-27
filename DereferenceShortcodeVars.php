<?php
/*
    "Contact Form to Database Extension" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database Extension.

    Contact Form to Database Extension is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database Extension is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
*/

require_once('CF7DBValueConverter.php');

class DereferenceShortcodeVars implements CF7DBValueConverter {

    public function convert($varString) {
        $retValue = $varString; // Default return

        $current_user = wp_get_current_user(); // WP_User

//        echo '<pre>';
//        print_r($current_user);
//        echo '</pre>';

//        echo('ID ' . $current_user->ID . '<br/>');
//        echo('id ' . $current_user->id . '<br/>');
//        echo('user_email ' . $current_user->user_email . '<br/>');
//        echo('first_name ' . $current_user->first_name . '<br/>');
//        echo('last_name ' . $current_user->last_name . '<br/>');
//        echo('user_login ' . $current_user->user_login . '<br/>');
//        echo('user_nicename ' . $current_user->user_nicename . '<br/>');
//        echo('user_firstname ' . $current_user->user_firstname . '<br/>');
//        echo('user_lastname ' . $current_user->user_lastname . '<br/>');

        switch ($varString) {

            case '$ID' :
                $retValue = $current_user->ID;
                break;

            case '$id':
                $retValue = $current_user->id;
                break;

            case '$first_name':
                $retValue = $current_user->first_name;
                break;

            case '$last_name':
                $retValue = $current_user->last_name;
                break;

            case '$user_login':
                $retValue = $current_user->user_login;
                break;

            case '$user_nicename':
                $retValue = $current_user->user_nicename;
                break;

            case '$user_email':
                $retValue = $current_user->user_email;
                break;

            case '$user_firstname':
                $retValue = $current_user->user_firstname;
                break;

            case '$user_lastname':
                $retValue = $current_user->user_lastname;
                break;

            default:
                $match = $this->evalMatch('_POST', $varString);
                global $_POST;
                if ($match != '' && isset($_POST[$match])) {
                    $retValue = $_POST[$match];
                    //print_r($retValue); // debug
                    break;
                }

                $match = $this->evalMatch('_GET', $varString);
                global $_GET;
                if ($match != '' && isset($_GET[$match])) {
                    $retValue = $_GET[$match];
                    //print_r($retValue); // debug
                    break;
                }

                $match = $this->evalMatch('_COOKIE', $varString);
                global $_COOKIE;
                if ($match != '' && $_COOKIE[$match]) {
                    $retValue = $_COOKIE[$match];
                    //print_r($retValue); // debug
                    break;
                }

                break;
        }

        //echo "input: '$varString' output: '$retValue' <br/>"; // debug
        return $retValue;
    }

    /**
     * See if variable name in the form of $varName['...'] appears in the $varString
     * (quotes optional or can be double-quotes)
     * Intended to detect $varString is of the form $_POST['param-name'] given $varName = '_POST'
     * @param  $varName name of the variable (without the "$")
     * @param  $varString string to search
     * @return string inside the brackets and quotes or '' if there is no match or
     */
    public function evalMatch($varName, $varString) {
        $template = '/^\$%s\(\'?"?(.*?)"?\'?\)$/';
        $matches = array();
        if (preg_match(sprintf($template, $varName), $varString, $matches)) {
            //print_r($matches); // debug
            if (count($matches) > 1) {
                return $matches[1];
            }
        }
        return '';
    }
}
