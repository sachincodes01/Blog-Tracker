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
            'post_type'      => 'page',
            'post_status'    => ['publish', 'draft', 'private'],
            'posts_per_page' => -1,
        ]);

        foreach ($posts as $post) {
            self::scan_post($post);
        }
    }

    public static function scan_post($post) {

        if (!$post) {
            return;
        }

        global $wpdb;

        $table = BUT_Database::table_name();

        $blocks = parse_blocks($post->post_content);

        if (empty($blocks)) {
            return;
        }

        /*
         * Scan Lazy Blocks
         */
        $block_counts = [];

        self::extract_lazy_blocks(
            $blocks,
            $block_counts
        );

        foreach ($block_counts as $block_name => $count) {

            $wpdb->insert(
                $table,
                [
                    'block_name'  => $block_name,
                    'block_count' => $count,
                    'post_id'     => $post->ID,
                    'post_title'  => $post->post_title,
                    'post_type'   => 'page',
                    'post_status' => $post->post_status,
                    'updated_at'  => current_time('mysql'),
                ]
            );
        }

        /*
         * Scan Media inside Lazy Blocks
         */
        $media_counts = [];

        self::extract_media(
            $blocks,
            $media_counts
        );

        foreach ($media_counts as $attachment_id => $count) {

            $file = get_attached_file($attachment_id);

            if (!$file) {
                continue;
            }

            $wpdb->insert(
                $table,
                [
                    'block_name'  => basename($file),
                    'block_count' => $count,
                    'post_id'     => $post->ID,
                    'post_title'  => $post->post_title,
                    'post_type'   => 'attachment',
                    'post_status' => $post->post_status,
                    'updated_at'  => current_time('mysql'),
                ]
            );
        }
    }

    private static function extract_lazy_blocks($blocks, &$counts = []) {

        foreach ($blocks as $block) {

            if (!empty($block['blockName'])) {

                if (
                    strpos(
                        $block['blockName'],
                        'lazyblock/'
                    ) === 0
                ) {

                    $name = $block['blockName'];

                    if (!isset($counts[$name])) {
                        $counts[$name] = 0;
                    }

                    $counts[$name]++;
                }
            }

            if (!empty($block['innerBlocks'])) {

                self::extract_lazy_blocks(
                    $block['innerBlocks'],
                    $counts
                );
            }
        }
    }

    private static function extract_media($blocks, &$media_counts = []) {

        foreach ($blocks as $block) {

            if (!empty($block['attrs'])) {

                foreach ($block['attrs'] as $value) {

                    /*
                     * LazyBlocks image field stores encoded JSON
                     */
                    if (is_string($value)) {

                        $decoded = json_decode(
                            urldecode($value),
                            true
                        );

                        if (
                            is_array($decoded) &&
                            !empty($decoded['id'])
                        ) {

                            $attachment_id = intval(
                                $decoded['id']
                            );

                            if (
                                get_post_type(
                                    $attachment_id
                                ) === 'attachment'
                            ) {

                                if (!isset($media_counts[$attachment_id])) {
                                    $media_counts[$attachment_id] = 0;
                                }

                                $media_counts[$attachment_id]++;
                            }
                        }
                    }
                }
            }

            if (!empty($block['innerBlocks'])) {

                self::extract_media(
                    $block['innerBlocks'],
                    $media_counts
                );
            }
        }
    }
}