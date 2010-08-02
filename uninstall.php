<?php

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

require_once('CF7DBPlugin.php');
$aPlugin = new CF7DBPlugin();
$aPlugin->uninstall();

