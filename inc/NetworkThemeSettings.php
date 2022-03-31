<?php

namespace BCLibCoop;

class NetworkThemeSettings
{
    public function __construct()
    {
        add_action('network_admin_menu', [$this, 'networkAdminMenu']);
    }

    /**
     * Add submenu page for managing the Sitka libraries, their library code, catalogue links, etc.
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

    /*
    * Network Admin configuration page for setting each library's Sitka Shortcode, Sitka Locale, and Catalogue Domain.
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
                        <th>WP Site ID</th>
                        <th>Domain Name</th>
                        <th>Custom CSS</th>
                        <th>Search Settings</th>
                    </tr>
                </thead>

                <?php
                // Loop through each blog lookup options and output form
                foreach ($blogs as $blog) {
                    switch_to_blog($blog->blog_id);

                    // Custom CSS
                    $css = wp_get_custom_css();
                    $atts = 'readonly';

                    if (empty($css)) {
                        $atts .= ' disabled';
                    }

                    // Search Settings
                    $search_settings = [
                        'search_style' => 'Search Box Style',
                        'search_type' => 'Search Type',
                        'search_url' => 'Search URL',
                        'search_param' => 'Search Term Parameter',
                        'search_extra_params' => 'Extra Search Parameters',
                        'search_external' => 'External Search Site',
                    ];

                    $search_settings_html = '';

                    foreach ($search_settings as $search_setting => $search_setting_label) {
                        $search_setting_val = get_theme_mod($search_setting);
                        if (!empty($search_setting_val)) {
                            $search_settings_html .= sprintf(
                                '<div><strong>%s:</strong> %s</div>',
                                $search_setting_label,
                                $search_setting_val
                            );
                        }
                    }

                    // Output form
                    echo sprintf(
                        '<tr>' .
                            '<td>%d</td><td>%s</td>' .
                            '<td><textarea rows="5" style="width: 100%%;" %s>%s</textarea></td>' .
                            '<td>%s</td>' .
                        '</tr>',
                        $blog->blog_id,
                        $blog->domain,
                        $atts,
                        $css,
                        $search_settings_html
                    );

                    // Switch back to previous blog (main network blog)
                    restore_current_blog();
                } ?>

            </table>
        </div><!-- .wrap -->
        <?php
    }
}
