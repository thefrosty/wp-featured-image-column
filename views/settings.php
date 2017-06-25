<div class="wrap">

    <h2><?php _e( 'Featured Image Column', 'featured-image-column' ); ?>
        <small><?php esc_html_e( 'by', 'featured-image-column' );
            echo ' <a href="http://austin.passy.co">Austin Passy</a>'; ?></small>
    </h2>

    <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">

        <?php settings_fields( 'featured_image_column_post_types' ); ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php esc_html_e( 'Allowed Post Types', 'featured-image-column' ); ?>
                </th>
                <td>
                    <div class="checkbox-wrap">
                        <ul>
                            <?php foreach ( $this->get_post_types() as $post_type ) {
                                $checked = isset( $post_types[ $post_type ] ) ?
                                    $post_types[ $post_type ] : '0';
                                printf( '<li><label for="%1$s[%2$s]" title="%2$s">',
                                    'featured_image_column', $post_type );
                                printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%2$s"%3$s> %2$s</label></li>',
                                    'featured_image_column', $post_type, checked( $checked, $post_type, false ) );
                            } ?>
                        </ul>
                    </div>
                    <span class="description">
                        <?php esc_html_e( 'By default all post types that support "thumbnails" are selected.', 'featured-image-column' ); ?>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>

    </form>

</div>