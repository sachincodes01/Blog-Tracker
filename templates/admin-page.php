<div class="wrap but-wrap">

    <h1>Block Usage Tracker</h1>

    <div class="but-actions">

        <button id="but-rescan" class="button button-primary">
            Rescan Blocks
        </button>

        <a href="?page=block-usage-tracker&but_export=1" class="button">
            Export CSV
        </a>
    </div>

    <form method="GET" id="but-actions">
        <input type="hidden" name="page" value="block-usage-tracker">

        <input
            type="text"
            name="s"
            placeholder="Search block name..."
            value="<?php echo esc_attr($search); ?>"
        >

        <select name="post_type">

            <option value="">All Post Types</option>

            <?php foreach (get_post_types(['public' => true], 'objects') as $pt) : ?>

                <option
                    value="<?php echo esc_attr($pt->name); ?>"
                    <?php selected($post_type, $pt->name); ?>
                >
                    <?php echo esc_html($pt->label); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button class="button">Filter</button>
    </form>

    <table class="widefat fixed striped">

        <thead>
            <tr>
                <th>Block</th>
                <th>Post</th>
                <th>Type</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

            <?php if (!empty($results)) : ?>

                <?php foreach ($results as $row) : ?>

                    <tr>

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
                            <?php echo esc_html($row->post_type); ?>
                        </td>

                        <td>
                            <?php echo esc_html($row->post_status); ?>
                        </td>

                        <td>
                            <?php echo esc_html($row->updated_at); ?>
                        </td>

                        <td>
                            <a
                                class="button button-small"
                                href="<?php echo admin_url(
                                    'admin.php?page=block-usage-tracker-edit&block=' . urlencode($row->block_name)
                                ); ?>"
                            >
                                Edit Block
                            </a>
                        </td>

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
