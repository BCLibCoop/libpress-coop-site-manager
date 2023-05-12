<?php

namespace BCLibCoop\SiteManager;

class NetworkThemeSettings
{
    private static $settings = [
        'search' => [
            'search_style' => 'Search Box Style',
            'search_type' => 'Search Type',
            'search_url' => 'Search URL',
            'search_param' => 'Search Term Parameter',
            'search_extra_params' => 'Extra Search Parameters',
            'search_external' => 'External Search Site',
        ],
        'style' => [
            'header_text' => 'Title and Tagline',
            'logo_alignment' => 'Logo Alignment',
            'logo_size' => 'Logo Size',
            'custom_logo' => 'Logo Image',
            'header_image' => 'Header Image',
            'background_image' => 'Background Image',
            'show_sidebar' => 'Show Sidebar',
        ],
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

        // Get all active public blogs
        $blogs = get_sites([
            'public' => 1,
            'archived' => 0,
            'deleted' => 0,
        ]); ?>

        <div class="wrap">
            <h1 class="wp-heading-inline">LibPress Theme Settings</h1>
            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th class="column-posts">WP Site ID</th>
                        <th>Domain Name</th>
                        <th>Custom CSS</th>
                        <th>Search Settings</th>
                        <th>Style Settings</th>
                    </tr>
                </thead>

                <?php
                // Loop through each blog lookup options and output form
                foreach ($blogs as $blog) :
                    switch_to_blog($blog->blog_id);

                    // Custom CSS
                    $css = wp_get_custom_css();

                    $settings_html = [
                        'css' => sprintf(
                            '<textarea rows="5" style="width: 100%%;" %s>%s</textarea><span>%d lines</span>',
                            empty($css) ? 'readonly disabled' : 'readonly',
                            $css,
                            substr_count($css, PHP_EOL)
                        ),
                    ];

                    foreach (self::$settings as $settings_section_name => $settings_section) {
                        if (empty($settings_html[$settings_section_name])) {
                            $settings_html[$settings_section_name] = '';
                        }

                        foreach ($settings_section as $setting => $setting_label) {
                            $setting_val = get_theme_mod($setting, null);

                            if ($setting_val !== null) {
                                // Show boolean-like as true/false
                                if (
                                    filter_var(
                                        $setting_val,
                                        FILTER_VALIDATE_BOOLEAN,
                                        FILTER_NULL_ON_FAILURE
                                    ) !== null
                                ) {
                                    $setting_val = var_export((bool) $setting_val, true);
                                }

                                if ($attachment_id = attachment_url_to_postid($setting_val)) {
                                    $setting_val = $attachment_id;
                                }

                                // Trim URLs down a bit
                                $setting_val = str_replace(home_url(), '', $setting_val);

                                if (
                                    is_numeric($setting_val)
                                    && $edit_link = get_edit_post_link((int) $setting_val, 'raw')
                                ) {
                                    $setting_val = sprintf(
                                        '<a href="%s">%s</a>',
                                        $edit_link,
                                        $setting_val
                                    );
                                }

                                $settings_html[$settings_section_name] .= sprintf(
                                    '<div><strong>%s:</strong> %s</div>',
                                    $setting_label,
                                    $setting_val
                                );
                            }
                        }
                    }

                    // Row actions
                    $actions = [
                        '<a href="' . esc_url(home_url('/')) . '" rel="bookmark">Visit</a>',
                        '<a href="' . esc_url(network_admin_url('site-info.php?id=' . $blog->blog_id)) . '">Edit</a>',
                        '<a href="' . esc_url(admin_url()) . '" class="edit">Dashboard</a>',
                        '<a href="'
                            . esc_url(add_query_arg('autofocus[section]', 'custom_css', admin_url('customize.php')))
                            . '">Edit CSS</a>'
                    ];

                    $row_actions = '<div class="row-actions"><span>';
                    $row_actions .= join(' | </span><span>', $actions);
                    $row_actions .= '</span></div>';

                    // Output form
                    echo sprintf(
                        '<tr>' .
                            '<td class="column-posts">%d</td>' .
                            '<td class="has-row-actions"><strong><a href="%s">%s</a></strong>%s</td>' .
                            '<td>%s</td>' .
                            '<td>%s</td>' .
                            '<td>%s</td>' .
                        '</tr>',
                        $blog->blog_id,
                        esc_url(home_url('/')),
                        $blog->domain,
                        $row_actions,
                        $settings_html['css'],
                        $settings_html['search'],
                        $settings_html['style'],
                    );

                    // Switch back to previous blog (main network blog)
                    restore_current_blog();
                endforeach; ?>

            </table>
        </div><!-- .wrap -->
        <?php
    }
}
