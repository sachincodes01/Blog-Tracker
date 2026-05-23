<?php

if (!defined('ABSPATH')) {
    exit;
}

class BUT_Admin {

    public function __construct() {

        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);

        add_action('wp_ajax_but_rescan', [$this, 'rescan']);

        add_action('save_post', [$this, 'save_post_scan'], 20, 3);
    }

    public function menu() {

        add_menu_page(
            'Block Usage Tracker',
            'Block Usage',
            'manage_options',
            'block-usage-tracker',
            [$this, 'page'],
            'dashicons-screenoptions',
            80
        );

        add_submenu_page(
            null,
            'Edit Block',
            'Edit Block',
            'manage_options',
            'block-usage-tracker-edit',
            [$this, 'edit_block_page']
        );
    }

    public function assets() {

        wp_enqueue_style(
            'but-admin',
            BUT_URL . 'assets/admin.css',
            [],
            BUT_VERSION
        );
    
        wp_enqueue_script(
            'but-admin',
            BUT_URL . 'assets/admin.js',
            ['jquery'],
            BUT_VERSION,
            true
        );
    
        wp_localize_script('but-admin', 'but_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('but_nonce'),
        ]);
    }

    public function save_post_scan($post_id, $post, $update) {

        if (wp_is_post_revision($post_id)) {
            return;
        }

        global $wpdb;

        $table = BUT_Database::table_name();

        $wpdb->delete($table, ['post_id' => $post_id], ['%d']);

        BUT_Scanner::scan_post($post);
    }

    public function rescan() {

        check_ajax_referer('but_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        BUT_Scanner::scan_all_posts();

        wp_send_json_success('Scan completed');
    }

    public function page() {

        global $wpdb;

        $table = BUT_Database::table_name();

        $search = isset($_GET['s'])
            ? sanitize_text_field($_GET['s'])
            : '';

        $post_type = isset($_GET['post_type'])
            ? sanitize_text_field($_GET['post_type'])
            : '';

        $where = 'WHERE 1=1';

        if (!empty($search)) {
            $where .= $wpdb->prepare(
                ' AND block_name LIKE %s',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        if (!empty($post_type)) {
            $where .= $wpdb->prepare(
                ' AND post_type = %s',
                $post_type
            );
        }

        $results = $wpdb->get_results(
            "SELECT * FROM {$table} {$where} ORDER BY updated_at DESC"
        );

        include BUT_PATH . 'templates/admin-page.php';
    }

    public function edit_block_page() {

        if (!current_user_can('manage_options')) {
            return;
        }

        $block_name = isset($_GET['block'])
            ? sanitize_text_field($_GET['block'])
            : '';

        if (empty($block_name)) {
            echo '<div class="wrap"><h2>No block selected.</h2></div>';
            return;
        }

        global $wpdb;

        $table = BUT_Database::table_name();

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE block_name = %s",
                $block_name
            )
        );

        echo '<div class="wrap">';
        echo '<h1>Edit Block: ' . esc_html($block_name) . '</h1>';

        foreach ($results as $row) {

            $post = get_post($row->post_id);

            echo '<div style="background:#fff;padding:20px;margin-bottom:20px;border:1px solid #ddd;">';

            echo '<h2>' . esc_html($post->post_title) . '</h2>';

            echo '<p><strong>Post Type:</strong> ' . esc_html($post->post_type) . '</p>';

            echo '<p><strong>Status:</strong> ' . esc_html($post->post_status) . '</p>';

            echo '<a class="button button-primary" href="' .
                esc_url(get_edit_post_link($post->ID)) .
                '">Open Editor</a>';

            echo '</div>';
        }

        echo '</div>';
    }
}
?>