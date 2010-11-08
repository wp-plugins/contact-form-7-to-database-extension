<?php
 
class CF7DBUtil {

    public static function &getParam($paramName) {
        if (isset($_GET[$paramName])) {
            return $_GET[$paramName];
        }
        else if (isset($_POST[$paramName])) {
            return $_POST[$paramName];
        }
        return null;
    }

}