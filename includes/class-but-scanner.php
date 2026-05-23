<?php

if (!defined('ABSPATH')) {
    exit;
}

class BUT_Scanner {

    public static function scan_all_posts() {
        global $wpdb;

        $table = BUT_Database::table_name();

        $wpdb->query("TRUNCATE TABLE {$table}");

        $posts = get_posts([
            'post_type' => get_post_types(['public' => true]),
            'post_status' => ['publish', 'draft', 'future', 'private'],
            'numberposts' => -1,
        ]);

        foreach ($posts as $post) {
            self::scan_post($post);
        }
    }

    public static function scan_post($post) {
        global $wpdb;

        $table = BUT_Database::table_name();

        if (!has_blocks($post->post_content)) {
            return;
        }

        $blocks = parse_blocks($post->post_content);
        $block_names = [];

        self::extract_blocks($blocks, $block_names);

        $block_names = array_unique($block_names);

        foreach ($block_names as $block_name) {
            $wpdb->insert(
                $table,
                [
                    'block_name' => sanitize_text_field($block_name),
                    'post_id' => $post->ID,
                    'post_title' => sanitize_text_field($post->post_title),
                    'post_type' => sanitize_text_field($post->post_type),
                    'post_status' => sanitize_text_field($post->post_status),
                    'updated_at' => current_time('mysql'),
                ],
                ['%s', '%d', '%s', '%s', '%s', '%s']
            );
        }
    }

    private static function extract_blocks($blocks, &$block_names) {
        foreach ($blocks as $block) {
            if (!empty($block['blockName'])) {
                $block_names[] = $block['blockName'];
            }

            if (!empty($block['innerBlocks'])) {
                self::extract_blocks($block['innerBlocks'], $block_names);
            }
        }
    }
}
?>