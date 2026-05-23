<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'but_block_usage';

$wpdb->query("DROP TABLE IF EXISTS {$table}");
?>