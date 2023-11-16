<?php

$post_types ??= $this->get_post_types();

?>
<div class="wrap">

    <h2><?php
        printf(
            __('Featured Image Column <small>by %s</small>', 'featured-image-column'),
            ' <a href="https://austin.passy.co">Austin Passy</a>'
        ); ?>
    </h2>

    <form method="post" action="<?php
    echo esc_url(admin_url('options.php')); ?>">

        <?php
        settings_fields('featured_image_column_post_types'); ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php
                    esc_html_e('Allowed Post Types', 'featured-image-column'); ?>
                </th>
                <td>
                    <div class="checkbox-wrap">
                        <ul>
                            <?php
                            foreach ($post_types as $post_type) {
                                $checked = $post_types[$post_type] ?? '0';
                                printf('<li><label for="featured_image_column[%1$s]" title="12$s">', $post_type);
                                printf(
                                    '<input type="checkbox" class="checkbox" id="featured_image_column[%1$s]" name="featured_image_column[%1$s]" value="%1$s"%2$s> %1$s</label></li>',
                                    $post_type,
                                    checked($checked, $post_type, false)
                                );
                            } ?>
                        </ul>
                    </div>
                    <span class="description">
                        <?php
                        esc_html_e(
                            'By default all post types that support "thumbnails" are selected.',
                            'featured-image-column'
                        ); ?>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        submit_button(); ?>
    </form>
</div>