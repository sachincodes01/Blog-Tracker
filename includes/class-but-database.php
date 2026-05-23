<?php

if (!defined('ABSPATH')) {
    exit;
}

class BUT_Database {

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'but_block_usage';
    }

    public static function activate() {
        global $wpdb;

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            block_name VARCHAR(255) NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            post_title TEXT NOT NULL,
            post_type VARCHAR(50) NOT NULL,
            post_status VARCHAR(20) NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY block_name (block_name),
            KEY post_id (post_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
?>