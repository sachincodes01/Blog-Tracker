<?php

if (!defined('ABSPATH')) {
    exit;
}

class BUT_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('wp_ajax_but_rescan', [$this, 'rescan']);
        add_action('save_post', [$this, 'save_post_scan'], 20, 3);
    }

    public function menu()
    {
        add_menu_page(
            'Block & Media Usage',
            'Block Usage',
            'manage_options',
            'block-usage-tracker',
            [$this, 'page'],
            'dashicons-screenoptions',
            80
        );
    }

    public function assets()
    {
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

    public function save_post_scan($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_type !== 'page') {
            return;
        }

        global $wpdb;

        $table = BUT_Database::table_name();

        $wpdb->delete(
            $table,
            ['post_id' => $post_id],
            ['%d']
        );

        BUT_Scanner::scan_post($post);
    }

    public function rescan()
    {
        check_ajax_referer('but_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        BUT_Scanner::scan_all_posts();

        wp_send_json_success('Scan completed');
    }

    private function get_attachment_id_by_filename($filename)
    {
        global $wpdb;

        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_wp_attached_file'
                AND meta_value LIKE %s
                LIMIT 1
                ",
                '%' . $wpdb->esc_like($filename) . '%'
            )
        );

        return $attachment_id ? intval($attachment_id) : 0;
    }

    public function page()
    {
        global $wpdb;

        $table = BUT_Database::table_name();

        $tab = isset($_GET['tab'])
            ? sanitize_text_field($_GET['tab'])
            : 'blocks';

        if ($tab === 'media') {
            $where = "WHERE block_name NOT LIKE 'lazyblock/%'";
        } else {
            $where = "WHERE block_name LIKE 'lazyblock/%'";
        }

        $results = $wpdb->get_results(
            "
            SELECT
                block_name,
                COUNT(*) as usage_count,
                MIN(post_id) as post_id,
                MIN(post_title) as post_title,
                MIN(post_status) as post_status,
                MAX(updated_at) as updated_at
            FROM {$table}
            {$where}
            GROUP BY block_name
            ORDER BY updated_at DESC
            "
        );

        ?>

        <div class="wrap">
            <h1>Block & Media Usage</h1>

            <p>
                <a href="?page=block-usage-tracker&tab=blocks"
                   class="button <?php echo $tab === 'blocks' ? 'button-primary' : ''; ?>">
                    Blocks
                </a>

                <a href="?page=block-usage-tracker&tab=media"
                   class="button <?php echo $tab === 'media' ? 'button-primary' : ''; ?>">
                    Media
                </a>

                <button id="but-rescan" class="button button-primary">
                    Rescan Blocks
                </button>
            </p>

            <table class="widefat striped">

                <thead>
                <tr>

                    <?php if ($tab === 'media') : ?>
                        <th width="90">Thumbnail</th>
                    <?php endif; ?>

                    <th>Name</th>
                    <th>Page</th>
                    <th>Status</th>
                    <th>Updated</th>

                    <?php if ($tab === 'blocks') : ?>
                        <th width="140">Actions</th>
                    <?php endif; ?>

                </tr>
                </thead>

                <tbody>

                <?php foreach ($results as $row) : ?>

                    <tr>

                        <?php if ($tab === 'media') : ?>

                            <td>
                                <?php
                                $attachment_id = $this->get_attachment_id_by_filename(
                                    $row->block_name
                                );

                                if ($attachment_id) {
                                    echo wp_get_attachment_image(
                                        $attachment_id,
                                        [60, 60],
                                        false,
                                        [
                                            'style' =>
                                                'width:60px;height:60px;object-fit:cover;border-radius:6px;'
                                        ]
                                    );
                                }
                                ?>
                            </td>

                        <?php endif; ?>

                        <td>
                            <?php echo esc_html($row->block_name . ' (' . $row->usage_count . ')'); ?>
                        </td>

                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($row->post_id)); ?>">
                                <?php echo esc_html($row->post_title); ?>
                            </a>
                        </td>

                        <td><?php echo esc_html($row->post_status); ?></td>

                        <td><?php echo esc_html($row->updated_at); ?></td>

                        <?php if ($tab === 'blocks') : ?>

                            <td>
                                <?php
                                $edit_url = '';

                                $block_slug = str_replace(
                                    'lazyblock/',
                                    '',
                                    $row->block_name
                                );

                                $lazy_block = get_posts([
                                    'post_type' => 'lazyblocks',
                                    'name' => $block_slug,
                                    'post_status' => 'any',
                                    'posts_per_page' => 1,
                                ]);

                                if (!empty($lazy_block)) {
                                    $edit_url = get_edit_post_link($lazy_block[0]->ID);
                                }

                                if ($edit_url) :
                                ?>
                                    <a
                                        href="<?php echo esc_url($edit_url); ?>"
                                        class="button button-small"
                                        target="_blank">
                                        Edit Block
                                    </a>
                                <?php endif; ?>
                            </td>

                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>
        </div>

        <?php
    }
}