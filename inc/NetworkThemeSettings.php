<?php

namespace BCLibCoop\SiteManager;

use BCLibCoop\CoopHighlights\CoopHighlights;

class NetworkThemeSettings
{
    private static $settings = [
        'Custom CSS' => [self::class, 'customCSS'],
        'Style Settings' => [
            'header_text' => 'Title and Tagline',
            'header_text_alignment' => 'Header Text Justification',
            'logo_alignment' => 'Logo Alignment',
            'logo_size' => [
                'label' => 'Logo Size',
                'return' => 'intval',
                'suffix' => '%',
            ],
            'custom_logo' => 'Logo Image',
            'header_image' => 'Header Image',
            'background_image' => 'Background Image',
            'show_sidebar' => 'Show Sidebar',
            'footer_menu_order' => [
                'label' => 'Footer Menu',
                'return' => [self::class, 'checkMenuLocation'],
            ],
            'topbar_location' => 'Top Bar Location',
            'menu_justification' => 'Menu Justification',
            'frontpage_content' => [
                'label' => 'Frontpage Content',
                'type' => 'custom',
                'post_type' => 'page',
                'return' => [self::class, 'frontpageContent'],
            ],
            'highlights' => [
                'label' => 'Highlights',
                'type' => 'custom',
                'post_type' => 'highlight',
                'return' => [self::class, 'getHighlights'],
            ],
            'blog_thumbnail' => 'Featured Image',
            'legacy_width' => 'Legacy Content Width',
        ],
        'Calendar Settings' => [
            'libpress_tec_default_cats' => [
                'label' => 'Default Calendar Categories',
                'type' => 'option',
            ],
            'libpress_tec_default_cats_comm' => [
                'label' => 'Community Submitted Categories',
                'type' => 'option',
            ],
            'libpress_tec_main_exclude' => [
                'label' => 'Excluded Categories',
                'type' => 'option',
            ],
            'options_libpress_tec_content' => [
                'label' => 'Before/After Content',
                'type' => 'option',
                'return' => 'count',
                'suffix' => ' Block(s) Defined',
            ],
        ],
        'Search Settings' => [
            'search_style' => 'Search Box Style',
            'search_type' => 'Search Type',
            'search_url' => 'Search URL',
            'search_param' => 'Search Term Parameter',
            'search_extra_params' => 'Extra Search Parameters',
            'search_external' => 'External Search Site',
            'search_external_label' => 'External Search Label',
            'eg_multibranch' => [
                'label' => 'EG Branches',
                'return' => 'count',
            ],
        ],
        'Widget Configuration' => [self::class, 'widgetAreas'],
    ];

    public function __construct()
    {
        add_action('network_admin_menu', [$this, 'networkAdminMenu']);
    }

    /**
     * Add submenu page for managing the Sitka libraries, their library code,
     * catalogue links, etc.
     */
    public function networkAdminMenu()
    {
        add_submenu_page(
            'sites.php',
            'LibPress Theme Settings',
            'LibPress Theme Settings',
            'manage_network',
            'libpress-themes',
            [$this, 'networkLibraryPage']
        );
    }

    /**
     * Network Admin configuration page for setting each library's Sitka
     * Shortcode, Sitka Locale, and Catalogue Domain.
     */
    public function networkLibraryPage()
    {
        if (!is_super_admin()) {
            // User is not a network admin
            wp_die('Sorry, you do not have permission to access this page');
        }
        ?>

        <style>
            .value-false,
            .value-0 {
                color: red;
            }

            .value-true {
                color: green;
            }
        </style>

        <div class="wrap">
            <h1 class="wp-heading-inline">LibPress Theme Settings</h1>
            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th class="column-posts">WP Site ID</th>
                        <th>Domain Name</th>
                        <?php foreach (array_keys(self::$settings) as $section_header) : ?>
                            <th><?= $section_header ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php $this->networkLibraryRows(); ?>
                </tbody>

            </table>
        </div><!-- .wrap -->
        <?php
    }

    /**
     * Output the row for each blog
     */
    private function networkLibraryRows()
    {
        // Get all active blogs
        $blogs = get_sites([
            'archived' => 0,
            'deleted' => 0,
        ]);

        // Loop through each blog lookup options and output form
        foreach ($blogs as $blog) {
            switch_to_blog($blog->blog_id);

            $settings_html = array_fill_keys(array_keys(self::$settings), '');

            foreach (self::$settings as $settings_section_name => $settings_section) {
                if (is_callable($settings_section)) {
                    call_user_func_array($settings_section, [&$settings_html]);
                    continue;
                }

                foreach ($settings_section as $setting => $setting_option) {
                    $label = $setting_option['label'] ?? $setting_option ?? 'Unknown';
                    $type = $setting_option['type'] ?? 'theme_mod';
                    $return = $setting_option['return'] ?? null;
                    $suffix = $setting_option['suffix'] ?? '';
                    $post_type = $setting_option['post_type'] ?? 'attachment';

                    $setting_val = null;
                    $setting_vals = [];

                    // Support dot-separated paths
                    $setting_keys = explode('.', $setting);

                    if ($type === 'option') {
                        $setting_val = get_option($setting_keys[0], null);
                    } elseif ($type === 'theme_mod') {
                        $setting_val = get_theme_mod($setting_keys[0], null);
                    }

                    unset($setting_keys[0]);

                    foreach ($setting_keys as $setting_key) {
                        if (!isset($setting_val[$setting_key])) {
                            $setting_val = null;
                            break;
                        }

                        $setting_val = $setting_val[$setting_key];
                    }

                    // Run the 'return' function if we have one, and the value isn't null
                    if (($setting_val !== null || $type === 'custom') && is_callable($return)) {
                        $setting_val = call_user_func($return, $setting_val) ?: null;
                    }

                    if ($setting_val !== null) {
                        // Cast to array to run for all values
                        foreach ((array) $setting_val as $single_setting_val) {
                            // Reset per-loop variables
                            $value_class = '';
                            $safe_html = false;

                            // Show boolean-like as true/false, unless the result is from
                            // a return function that should be numeric
                            if (
                                ! in_array($return, ['strlen', 'count', 'intval'])
                                && filter_var(
                                    $single_setting_val,
                                    FILTER_VALIDATE_BOOLEAN,
                                    FILTER_NULL_ON_FAILURE
                                ) !== null
                            ) {
                                $single_setting_val = var_export((bool) $single_setting_val, true);
                                $value_class = "value-{$single_setting_val}";
                            }

                            // Process URLs
                            if (strpos($single_setting_val, 'http') === 0) {
                                if ($attachment_id = attachment_url_to_postid($single_setting_val)) {
                                    $single_setting_val = $attachment_id;
                                }

                                // Trim down any remaining URLs a bit
                                $single_setting_val = str_replace(home_url(), '', $single_setting_val);
                            }

                            // Provide Edit links if possible (could provide false-positives)
                            if (
                                is_numeric($single_setting_val)
                                && get_post_type((int) $single_setting_val) === $post_type
                                && $edit_link = get_edit_post_link((int) $single_setting_val, 'raw')
                            ) {
                                // We're making sure this is sanitized HTML
                                $safe_html = true;

                                $single_setting_val = sprintf(
                                    '<a href="%s">%d%s</a>',
                                    esc_url($edit_link),
                                    (int) $single_setting_val,
                                    esc_html($suffix)
                                );
                            }

                            $setting_vals[] = sprintf(
                                '<span class="%s">%s%s</span>',
                                esc_attr($value_class),
                                $safe_html ? $single_setting_val : esc_html($single_setting_val),
                                esc_html($suffix)
                            );
                        }

                        $settings_html[$settings_section_name] .= sprintf(
                            '<div><strong>%s:</strong> %s</div>',
                            esc_html($label),
                            implode(', ', $setting_vals),
                        );
                    }
                }
            }

            // Row actions
            $row_actions = '<div class="row-actions"><span>';
            $row_actions .= join(' | </span><span>', [
                '<a href="' . esc_url(home_url('/')) . '" rel="bookmark">Visit</a>',
                '<a href="' . esc_url(network_admin_url('site-info.php?id=' . $blog->blog_id)) . '">Edit</a>',
                '<a href="' . esc_url(admin_url()) . '" class="edit">Dashboard</a>',
                '<a href="'
                    . esc_url(add_query_arg('autofocus[section]', 'custom_css', admin_url('customize.php')))
                    . '">Edit CSS</a>'
            ]);
            $row_actions .= '</span></div>';

            // Output row
            $row = [
                '<tr>' .
                    '<td class="column-posts">%d</td>' .
                    '<td class="has-row-actions"><strong><a href="%s">%s</a></strong>%s</td>' .
                    str_repeat('<td>%s</td>', count($settings_html)) .
                '</tr>',
                $blog->blog_id,
                esc_url(home_url('/')),
                esc_html($blog->domain),
                $row_actions,
            ];

            echo call_user_func_array('sprintf', array_merge($row, $settings_html));

            // Switch back to previous blog (main network blog)
            restore_current_blog();
        }
    }

    /**
     * Additional check for the footer menu being disabled because there is no
     * menu in that position
     */
    private static function checkMenuLocation($value)
    {
        $menus = get_nav_menu_locations();

        return empty($menus['secondary']) ? "{$value} - No Menu Set" : $value;
    }

    /**
     * Check if there is content on the front-page post that's intentional
     */
    private static function frontpageContent()
    {
        $front_page = (int) get_option('page_on_front');
        $front_post = !empty($front_page) ? get_post($front_page) : null;

        return (
            $front_post
            && get_post_modified_time('U', false, $front_post) > 1646000000
            && !empty(trim(get_the_content(null, false, $front_post)))
        ) ? $front_post->ID : false;
    }

    private static function getHighlights()
    {
        if (!class_exists(CoopHighlights::class)) {
            return null;
        }

        return array_map(function($highlight) {
            return $highlight ? $highlight->ID : false;
        }, CoopHighlights::highlightsPosts(true));
    }

    /**
     * Get custom CSS and updated date
     */
    private static function customCSS(&$settings_html)
    {
        $css = wp_get_custom_css();
        $css_post = wp_get_custom_css_post(get_stylesheet());

        $settings_html['Custom CSS'] .= sprintf(
            '<textarea rows="5" style="width: 100%%;" %s>%s</textarea>'
            . '<span>%d lines</span>&nbsp;<span>(Last updated %s)',
            empty($css) ? 'readonly disabled' : 'readonly',
            esc_textarea($css),
            substr_count($css, PHP_EOL),
            get_the_modified_date('Y-m-d', $css_post)
        );
    }

    /**
     * Get count of widgets in the widget areas
     */
    private static function widgetAreas(&$settings_html)
    {
        if ($sidebars = wp_get_sidebars_widgets()) {
            foreach ($sidebars as $sidebar_name => $widgets) {
                if (
                    $sidebar_name === 'wp_inactive_widgets'
                    || substr($sidebar_name, 0, 16) === 'orphaned_widgets'
                    // || count($widgets) < 1
                ) {
                    continue;
                }

                $sidebar_name = wp_get_sidebar($sidebar_name)['name'];

                $settings_html['Widget Configuration'] .= sprintf(
                    '<div><strong>%1$s Widgets:</strong> <span class="value-%2$d">%2$d</span></div>',
                    esc_html($sidebar_name),
                    count($widgets),
                );
            }
        }
    }
}
