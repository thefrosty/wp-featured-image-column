<div class="wrap">

    <h2><?php _e( 'Featured Image Column', \TheFrosty\Featured_Image_Column::DOMAIN ); ?>
        <small><?php printf( __( 'by <a href="%s">Austin Passy</a>', \TheFrosty\Featured_Image_Column::DOMAIN ), 'http://austin.passy.co' ); ?></small>
    </h2>

    <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">

        <?php settings_fields( 'featured_image_column_post_types' ); ?>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php _e( 'Allowed Post Types', \TheFrosty\Featured_Image_Column::DOMAIN ); ?>
                </th>
                <td>
                    <div class="checkbox-wrap">
                        <ul>
                            <?php foreach ( $this->get_post_types() as $key => $label ) {
                                $checked = isset( $post_types[ $label ] ) ?
                                    $post_types[ $label ] : '0';
                                printf( '<li><label for="%1$s[%3$s]" title="%2$s">',
                                    'featured_image_column', $label, $label );
                                printf( '<input type="checkbox" class="checkbox" id="%1$s[%3$s]" name="%1$s[%3$s]" value="%3$s"%4$s> %2$s</label></li>',
                                    'featured_image_column', $label, $label, checked( $checked, $label, false ) );
                            } ?>
                        </ul>
                    </div>
                    <span class="description"><?php _e( 'By default all post types that support "thumbnails" are selected.', \TheFrosty\Featured_Image_Column::DOMAIN ); ?></span>
                </td>
            </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>

    </form>

</div>