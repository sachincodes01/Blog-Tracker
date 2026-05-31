<div class="wrap but-wrap">

    <h1>Block & Media Usage</h1>

    <?php
    $tab = isset($_GET['tab'])
        ? sanitize_text_field($_GET['tab'])
        : 'blocks';
    ?>

    <div class="but-actions">

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

    </div>

    <table class="widefat fixed striped">

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

        <?php if ($tab === 'media') : ?>
<td width="90">
    <?php

    $attachment = get_page_by_title(
        $row->block_name,
        OBJECT,
        'attachment'
    );

    if ($attachment) {

        echo wp_get_attachment_image(
            $attachment->ID,
            [60, 60],
            false,
            [
                'style' => '
                    width:60px;
                    height:60px;
                    object-fit:cover;
                    border-radius:6px;
                '
            ]
        );

    } else {

        echo '<span style="
            display:inline-block;
            width:60px;
            height:60px;
            background:#f0f0f1;
            border-radius:6px;
        "></span>';

    }
    ?>
</td>
<?php endif; ?>

                    <td>
                        <strong>
                            <?php echo esc_html($row->block_name); ?>
                        </strong>
                    </td>

                    <td>
                        <a href="<?php echo esc_url(get_edit_post_link($row->post_id)); ?>">
                            <?php echo esc_html($row->post_title); ?>
                        </a>
                    </td>

                    <td>
                        <?php echo esc_html($row->post_status); ?>
                    </td>

                    <td>
                        <?php echo esc_html($row->updated_at); ?>
                    </td>

                    <?php if ($tab === 'blocks') : ?>

                        <td>

                            <?php
                            $edit_url = '';

                            if (strpos($row->block_name, 'lazyblock/') === 0) {

                                $block_slug = str_replace(
                                    'lazyblock/',
                                    '',
                                    $row->block_name
                                );

                                $lazy_blocks = get_posts([
                                    'post_type'      => 'lazyblocks',
                                    'posts_per_page' => 1,
                                    'post_status'    => 'any',
                                    'name'           => $block_slug,
                                ]);

                                if (!empty($lazy_blocks)) {
                                    $edit_url = get_edit_post_link(
                                        $lazy_blocks[0]->ID
                                    );
                                }

                                if (empty($edit_url)) {

                                    $lazy_blocks = get_posts([
                                        'post_type'      => 'lazyblocks',
                                        'posts_per_page' => 1,
                                        'post_status'    => 'any',
                                        'title'          => $block_slug,
                                    ]);

                                    if (!empty($lazy_blocks)) {
                                        $edit_url = get_edit_post_link(
                                            $lazy_blocks[0]->ID
                                        );
                                    }
                                }
                            }

                            if (!empty($edit_url)) :
                            ?>

                                <a href="<?php echo esc_url($edit_url); ?>"
                                   class="button button-small"
                                   target="_blank">
                                    Edit Block
                                </a>

                            <?php else : ?>

                                <span style="color:#888;">—</span>

                            <?php endif; ?>

                        </td>

                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>

        <?php else : ?>

            <tr>
                <td colspan="6">No data found.</td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>