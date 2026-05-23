<?php

if (!defined('ABSPATH')) {
    exit;
}

class BUT_Export {

    public function __construct() {
        add_action('admin_init', [$this, 'export_csv']);
    }

    public function export_csv() {

        if (!isset($_GET['but_export'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        $table = BUT_Database::table_name();

        $results = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="block-usage.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Block Name',
            'Post ID',
            'Post Title',
            'Post Type',
            'Status',
            'Updated'
        ]);

        foreach ($results as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
?>