<?php

namespace BCLibCoop\SiteManager;

use BCLibCoop\CoopHighlights\CoopHighlights;

class NetworkThemeSettings
{
    private static $settings = [
        'Custom CSS' => [[self::class, 'customCSS']],
        'Style Settings' => [
            'classic-editor-allow-users' => [
                'label' => 'Block Editor',
                'type' => 'custom',
                'return' => [self::class, 'blockEditor'],
            ],
            'header_text' => 'Title and Tagline',
            'site_icon' => [
                'label' => 'Site Icon',
                'type' => 'option',
                'return' => 'intval',
            ],
            'header_text_alignment' => 'Header Text Justification',
            'custom_logo' => 'Logo Image',
            'logo_alignment' => 'Logo Alignment',
            'logo_size' => [
                'label' => 'Logo Size',
                'return' => 'intval',
                'suffix' => '%',
            ],
            'header_image' => 'Header Image',
            'background_image' => 'Background Image',
            'header_order' => [
                'label' => 'Header Item Order',
                'default' => false,
                'return' => [self::class, 'checkHeaderOrder'],
            ],
            'footer_menu_order' => 'Footer Item Order',
            'footer_menu' => [
                'setting' => 'footer_menu_order',
                'label' => 'Footer Menu',
                'return' => [self::class, 'checkMenuLocation'],
            ],
            'global_container' => 'Use Global Container',
            'legacy_width' => 'Legacy Content Width',
            'show_sidebar' => 'Show Sidebar',
            'menu_justification' => 'Menu Justification',
            'frontpage_content' => [
                'label' => 'Frontpage Content',
                'type' => 'custom',
                'object_type' => 'page',
                'return' => [self::class, 'frontpageContent'],
            ],
            'highlights' => [
                'label' => 'Highlights',
                'type' => 'custom',
                'object_type' => 'highlight',
                'return' => [self::class, 'getHighlights'],
            ],
            'blog_thumbnail' => 'Blog Featured Image',
        ],
        'Calendar Settings' => [
            'libpress_tec_default_cats' => [
                'label' => 'Default Calendar Categories',
                'type' => 'option',
                'object_type' => 'tribe_events',
                'taxonomy' => 'tribe_events_cat',
            ],
            'libpress_tec_default_cats_comm' => [
                'label' => 'Community Submitted Categories',
                'type' => 'option',
                'object_type' => 'tribe_events',
                'taxonomy' => 'tribe_events_cat',
            ],
            'libpress_tec_main_exclude' => [
                'label' => 'Excluded Categories',
                'type' => 'option',
                'object_type' => 'tribe_events',
                'taxonomy' => 'tribe_events_cat',
            ],
            'options_libpress_tec_content' => [
                'label' => 'Before/After Content',
                'type' => 'option',
                'return' => 'count',
                'suffix' => ' Block(s) Defined',
                'class' => '',
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
        'Widget Configuration' => [[self::class, 'widgetAreas']],
    ];

    private static $widgetOrder = [
        'searchbox',
        'header-1',
        'myaccount-1',
        'footer-1',
        'footer-3',
        'footer-4',
        'footer-bottom',
        'sidebar-1',
    ];

    private static $msSettings = [];

    public function __construct()
    {
        add_action('network_admin_menu', [$this, 'networkAdminMenu']);

        self::$msSettings['editor'] = get_network_option(null, 'classic-editor-replace') === 'block' ? 'block' : 'classic';
        self::$msSettings['allow-sites'] = get_network_option(null, 'classic-editor-allow-sites') === 'allow' ? 'allow' : 'deny';
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

            textarea.code {
                width: 100%;
            }

            [title]:not([title=""]),
            [title]:not([title=""]) a {
                cursor: help;
            }
        </style>

        <div class="wrap">
            <h1 class="wp-heading-inline">LibPress Theme Settings</h1>
            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th scope="col" class="column-primary manage-column">Domain Name</th>
                        <th scope="col" class="column-posts num manage-column">Site ID</th>
                        <?php foreach (array_keys(self::$settings) as $section_header) : ?>
                            <th scope="col" class="manage-column"><?= $section_header ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody id="the-list">
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

        foreach ($blogs as $blog) {
            echo '<tr>';

            switch_to_blog($blog->blog_id);

            $row_actions = '<div class="row-actions"><span>';
            $row_actions .= join(' | </span><span>', [
                '<a href="' . esc_url(home_url('/')) . '" rel="bookmark">Visit</a>',
                '<a href="' . esc_url(network_admin_url('site-info.php?id=' . $blog->blog_id)) . '">Edit</a>',
                '<a href="' . esc_url(admin_url()) . '" class="edit">Dashboard</a>',
                '<a href="' . esc_url(admin_url('widgets.php')) . '">Widgets</a>',
                '<a href="' . esc_url(add_query_arg('autofocus[section]', 'custom_css', admin_url('customize.php'))) . '">Edit CSS</a>'
            ]);
            $row_actions .= '</span></div>';
            $row_buttons = '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>';

            // Primary Column
            printf(
                '<td class="has-row-actions column-primary" data-colname="Domain Name"><strong><a href="%s">%s</a></strong>%s%s</td>',
                esc_url(home_url('/')),
                esc_html($blog->domain),
                $row_buttons,
                $row_actions,
            );

            printf('<td class="column-posts" data-colname="Site ID">%d</td>', $blog->blog_id);

            foreach (self::$settings as $settings_section_name => $settings_section) {
                $settings_html = '';

                foreach ($settings_section as $setting => $setting_option) {
                    if (is_callable($setting_option)) {
                        $settings_html .= call_user_func($setting_option);
                        continue;
                    }

                    $setting = $setting_option['setting'] ?? $setting;
                    $label = $setting_option['label'] ?? $setting_option ?? 'Unknown';
                    $default = $setting_option['default'] ?? null;
                    $type = $setting_option['type'] ?? 'theme_mod';
                    $return = $setting_option['return'] ?? null;
                    $suffix = $setting_option['suffix'] ?? '';
                    $class = $setting_option['class'] ?? 'code';
                    $object_type = $setting_option['object_type'] ?? 'attachment';
                    $taxonomy = $setting_option['taxonomy'] ?? null;

                    $setting_val = null;
                    $setting_vals = [];

                    // Support dot-separated paths
                    $setting_keys = explode('.', $setting);

                    if ($type === 'option') {
                        $setting_val = get_option($setting_keys[0], $default);
                    } elseif ($type === 'theme_mod') {
                        $setting_val = get_theme_mod($setting_keys[0], $default);
                    }

                    unset($setting_keys[0]);

                    foreach ($setting_keys as $setting_key) {
                        if (!isset($setting_val[$setting_key])) {
                            $setting_val = null;
                            break;
                        }

                        $setting_val = $setting_val[$setting_key];
                    }

                    // Catch values that should be arrays that are a blank string
                    $setting_val = ($return == 'count' && $setting_val == '') ? null : $setting_val;

                    // Run the 'return' function if we have one, and the value isn't null
                    if (($setting_val !== null || $type === 'custom') && is_callable($return)) {
                        $setting_val = call_user_func($return, $setting_val) ?: null;
                    }

                    if ($setting_val !== null) {
                        // Cast to array to run for all values
                        foreach ((array) $setting_val as $single_setting_val) {
                            // Reset per-loop variables
                            $value_classes = array_filter([$class]);
                            $safe_html = false;
                            $title = '';

                            if (is_array($single_setting_val)) {
                                $title = $single_setting_val[1];
                                $single_setting_val = $single_setting_val[0];
                            }

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
                                $value_classes[] = "value-{$single_setting_val}";
                            }

                            // Process URLs
                            if (strpos($single_setting_val, 'http') === 0) {
                                if ($attachment_id = attachment_url_to_postid($single_setting_val)) {
                                    $title = str_replace(home_url(), '', $single_setting_val);
                                    $single_setting_val = $attachment_id;
                                } else {
                                    // Trim down any remaining URLs a bit
                                    $single_setting_val = str_replace(home_url(), '', $single_setting_val);
                                }
                            }

                            // Provide Edit links if possible (could provide false-positives)
                            if (is_numeric($single_setting_val)) {
                                $edit_link = null;

                                if ($taxonomy) {
                                    // Can't get taxonomies of a multisite, so blindly build an edit link
                                    $edit_link = add_query_arg(
                                        [
                                            'taxonomy' => $taxonomy,
                                            'tag_ID'   => $single_setting_val,
                                            'post_type' => $object_type,
                                        ],
                                        admin_url('term.php')
                                    );
                                } elseif (get_post_type((int) $single_setting_val) === $object_type) {
                                    $title = !empty($title) ? $title : get_the_title($single_setting_val);
                                    $edit_link = get_edit_post_link((int) $single_setting_val, 'raw');
                                }

                                if (!empty($edit_link)) {
                                    // We're making sure this is sanitized HTML
                                    $safe_html = true;

                                    $single_setting_val = sprintf(
                                        '<a href="%s">%d%s</a>',
                                        esc_url($edit_link),
                                        (int) $single_setting_val,
                                        esc_html($suffix)
                                    );
                                }
                            }

                            $setting_vals[] = sprintf(
                                '<span class="%s" title="%s">%s%s</span>',
                                esc_attr(implode(' ', $value_classes)),
                                esc_attr($title),
                                $safe_html ? $single_setting_val : esc_html($single_setting_val),
                                esc_html($suffix)
                            );
                        }

                        if (!empty($setting_vals)) {
                            $settings_html .= sprintf(
                                '<div><strong>%s:</strong> %s</div>',
                                esc_html($label),
                                implode(', ', $setting_vals),
                            );
                        }
                    }
                }

                // Output the column
                printf('<td data-colname="%s">%s</td>', $settings_section_name, $settings_html);
            }

            // Switch back to previous blog (main network blog)
            restore_current_blog();

            // Finish output here to constrain any errors to this row
            echo '</tr>';
        }
    }

    /**
     * Map old header menu order value if not set
     */
    private static function checkHeaderOrder($value)
    {
        if ($value === false) {
            return get_theme_mod('topbar_location', 'above') == 'above' ?
                ['top-bar', 'info-banner', 'site-branding', 'primary-nav', 'slideshow'] :
                ['site-branding', 'top-bar', 'info-banner', 'primary-nav', 'slideshow'];
        }

        return $value;
    }

    /**
     * Additional check for the footer menu being disabled because there is no
     * menu in that position
     */
    private static function checkMenuLocation($value)
    {
        $menus = get_nav_menu_locations();

        // Cast 'disabled' to false to get class highlight
        $value = !in_array('secondary-nav', $value) ? [false, 'Disabled in Customizer'] : $value;

        return empty($menus['secondary']) ? [[false, 'Menu Location Empty'], $value] : [$value];
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

        return array_map(function ($highlight) {
            return $highlight ? $highlight->ID : [false, 'No Highlight in Position'];
        }, CoopHighlights::highlightsPosts(true));
    }

    /**
     * Get custom CSS and updated date
     */
    private static function customCSS()
    {
        $css = wp_get_custom_css();
        $css_post = wp_get_custom_css_post(get_stylesheet());

        return sprintf(
            '<textarea rows="5" class="code" %s>%s</textarea>'
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
    private static function widgetAreas()
    {
        $sidebar_html = '';

        if ($sidebars = wp_get_sidebars_widgets()) {
            /**
             * Sort the widget areas.
             *
             * First by a known order to catch any new/unknown areas
             *
             * Then by our defined custom order
             */
            ksort($sidebars);
            uksort($sidebars, fn($key1, $key2) => (array_search($key1, self::$widgetOrder) <=> array_search($key2, self::$widgetOrder)));

            foreach ($sidebars as $sidebar_name => $widgets) {
                if (
                    $sidebar_name === 'wp_inactive_widgets'
                    || substr($sidebar_name, 0, 16) === 'orphaned_widgets'
                    // || count($widgets) < 1
                ) {
                    continue;
                }

                $sidebar_name = wp_get_sidebar($sidebar_name)['name'];

                $sidebar_html .= sprintf(
                    '<div><strong>%1$s Widgets:</strong> <span class="code value-%2$d">%2$d</span></div>',
                    esc_html($sidebar_name),
                    count($widgets),
                );
            }
        }

        return $sidebar_html;
    }

    /**
     * Check for block editor status
     */
    private static function blockEditor()
    {
        if (!class_exists(\Classic_Editor::class)) {
            return 'Unrestricted';
        }

        if (self::$msSettings['allow-sites'] !== 'allow' && self::$msSettings['editor'] === 'block') {
            return 'Network Forced';
        }

        if (get_option('classic-editor-allow-users') !== 'allow' && get_option('classic-editor-replace') === 'block') {
            return 'Site Forced';
        }

        if (get_option('classic-editor-allow-users') === 'allow') {
            return 'Site Allowed';
        }
    }
}
