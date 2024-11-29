<?php

declare(strict_types=1);

namespace TheFrosty;

/**
 * Class FeatureImageColumn
 * @package TheFrosty
 */
class FeatureImageColumn
{

    protected const ID = 'featured-image';

    /**
     * FeatureImageColumn constructor.
     * @param string $file
     */
    public function __construct(private string $file)
    {
    }

    /**
     * Ensures that the rest of the code only runs on edit.php pages
     * @since 0.3
     */
    public function addHooks(): void
    {
        \add_action('admin_menu', [$this, 'adminMenu']);
        \add_action('admin_init', [$this, 'adminInit']);
        \add_action('admin_enqueue_scripts', [$this, 'style']);
    }

    /**
     * Activation hook, to add our default settings.
     */
    public function activationHook(): void
    {
        $post_types = [];
        foreach ($this->getPostTypes() as $post_type) {
            if (!\post_type_supports($post_type, 'thumbnail')) {
                continue;
            }
            $post_types[$post_type] = $post_type;
        }
        \update_option('featured_image_column', $post_types);
    }

    /**
     * Register our settings page.
     */
    public function adminMenu(): void
    {
        \add_options_page(
            'Featured Image Column Settings',
            'Featured Image Col',
            'manage_options',
            'featured-image-column',
            function (): void {
                $this->settingsPage();
            }
        );
    }

    /**
     * Since the load-edit.php hook is too early for checking the post type, hook the rest
     * of the code to the wp action to allow the query to be run first.
     */
    public function adminInit(): void
    {
        global $pagenow;

        $this->registerSettings();

        // Only continue if we're on the 'edit.php' page(s)
        if (empty($pagenow) || $pagenow !== 'edit.php' && \defined('DOING_AJAX') && !\DOING_AJAX) {
            return;
        }

        // Make sure we've got some post_types saved via our settings.
        $post_types = $this->getSettings();
        if (empty($post_types)) {
            return;
        }

        // Add out custom column and column data
        foreach ($post_types as $post_type) {
            if (!\post_type_supports($post_type, 'thumbnail')) {
                continue;
            }
            \add_filter("manage_{$post_type}_posts_columns", [$this, 'columns']);
            \add_action("manage_{$post_type}_posts_custom_column", [$this, 'columnData'], 10, 2);
        }
    }

    /**
     * Enqueue our stylesheet on the edit.php page.
     */
    public function style(): void
    {
        global $pagenow;
        $version = \get_plugin_data($this->file, false, false)['Version'] ?? '20170625';
        \wp_register_style('featured-image-column', \plugin_dir_url($this->file) . 'css/column.css', [], $version);

        if ($pagenow === 'edit.php') {
            \wp_enqueue_style('featured-image-column');
        }
    }

    /**
     * Filter the image in before the 'title'
     * @param array $columns
     * @return array
     */
    public function columns(array $columns): array
    {
        $new_columns = [];
        foreach ($columns as $key => $title) {
            // Put the Thumbnail column before the Title column
            if ($key === 'title') {
                $new_columns[self::ID] = \esc_html__('Image', 'featured-image-column');
            }

            $new_columns[$key] = $title;
        }

        return $new_columns;
    }

    /**
     * Output the image
     *
     * @param string $column_name
     * @param int $post_id
     */
    public function columnData(string $column_name, int $post_id): void
    {
        if (self::ID !== $column_name) {
            return;
        }

        $featured_image = $this->getTheImage($post_id);

        if (!empty($featured_image)) {
            echo $featured_image;

            return;
        }

        echo "&nbsp;"; // This helps prevent issues with empty cells
    }

    /**
     * Output our settings.
     * @since 0.2.2
     */
    protected function settingsPage(): void
    {
        $post_types = $this->getSettings();
        if (empty($post_types)) {
            $post_types = [];
            foreach ($this->getPostTypes() as $key => $post_type) {
                if (\post_type_supports($post_type, 'thumbnail')) {
                    $post_types[$post_type] = $post_type;
                }
            }
            \update_option('featured_image_column', $post_types);
        }

        include \dirname($this->file) . '/views/settings.php';
    }

    /**
     * Register the plugins settings.
     */
    protected function registerSettings(): void
    {
        \register_setting(
            'featured_image_column_post_types',
            'featured_image_column',
            static function (mixed $input): array {
                if (!\is_array($input)) {
                    $input = (array) $input;
                }

                return \array_map('sanitize_key', $input);
            }
        );
    }

    /**
     * Function to get the image.
     * @param int|null $post_id
     * @return string
     */
    protected function getTheImage(?int $post_id): string
    {
        if (\has_post_thumbnail($post_id)) {
            return \get_the_post_thumbnail($post_id);
        }

        $default = \plugins_url('images/default.png', $this->file);
        $image = \apply_filters('featured_image_column_default_image', $default);

        return \sprintf('<img alt="%1$s" src="%2$s">', \esc_attr(\get_the_title($post_id)), \esc_url($image));
    }

    /**
     * Helper function to return all public post ty`pes
     * @return array
     */
    protected function getPostTypes(): array
    {
        return \get_post_types(['public' => true]);
    }

    /**
     * Get our settings from the DB with the defaults set to an array.
     * @return array
     */
    protected function getSettings(): array
    {
        return \get_option('featured_image_column', []);
    }
}
