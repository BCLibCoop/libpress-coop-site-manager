<?php

namespace BCLibCoop\SiteManager;

class NetworkSitkaLibraries
{
    public function __construct()
    {
        add_action('network_admin_menu', [$this, 'networkAdminMenu']);
        add_action('admin_post_sitka_libraries', [$this, 'networkLibraryPageSave']);
    }

    /**
     * Add submenu page for managing the Sitka libraries, their library code, catalogue links, etc.
     */
    public function networkAdminMenu()
    {
        add_submenu_page(
            'sites.php',
            'Sitka Libraries',
            'Sitka Libraries',
            'manage_network',
            'sitka-libraries',
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
            <h1 class="wp-heading-inline">Sitka Libraries</h1>
            <hr class="wp-header-end">

            <form method="post" action="<?= admin_url('admin-post.php') ?>">

                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th>WP Site ID</th>
                            <th>Domain Name</th>
                            <th>Sitka Shortcode</th>
                            <th>Sitka Locale</th>
                            <th>Catalogue Domain</th>
                        </tr>
                    </thead>

                    <?php
                    // Loop through each blog lookup options and output form
                    foreach ($blogs as $blog) {
                        switch_to_blog($blog->blog_id);

                        $lib_shortcode = get_option('_coop_sitka_lib_shortname', '');

                        // If no value for locg exists, set it to 1 (parent container for Sitka)
                        $lib_locg = get_option('_coop_sitka_lib_locg', 1);

                        // Must be blank by default. Same shortname stem used when it agrees with Sitka catalogue
                        // subdomain. Blogs with custom domains are the only ones targeted here.
                        $lib_cat_link = get_option('_coop_sitka_lib_cat_link', '');

                        // Output form
                        echo sprintf(
                            '<tr>' .
                                '<td>%1$d</td><td>%2$s</td>' .
                                '<td>' .
                                '<input type="text" name="shortcode_%1$d" class="shortcode widefat" value="%3$s">' .
                                '</td><td>' .
                                '<input type="text" name="locg_%1$d" class="shortcode widefat" value="%4$d">' .
                                '</td><td>' .
                                '<input type="text" name="cat_link_%1$d" class=shortcode widefat" value="%5$s">' .
                                '</td></tr>',
                            $blog->blog_id,
                            $blog->domain,
                            esc_attr($lib_shortcode),
                            esc_attr($lib_locg),
                            esc_attr($lib_cat_link)
                        );

                        // Switch back to previous blog (main network blog)
                        restore_current_blog();
                    }
                    ?>

                </table>
                <div class="tablenav bottom">
                    <button class="button button-primary sitka-libraries-save-btn">Save changes</button>
                </div>
                <?php echo wp_nonce_field('admin_post', 'coop_sitka_libraries_nonce') ?>
                <input type="hidden" name="action" value="sitka_libraries">
            </form>
        </div><!-- .wrap -->
        <?php
    }

    /*
    * Callback to handle the network admin form submission
    */
    public function networkLibraryPageSave()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            empty($_POST['coop_sitka_libraries_nonce'])
            || !wp_verify_nonce($_POST['coop_sitka_libraries_nonce'], 'admin_post')
        ) {
            wp_die('Sorry, there was an error handling your form submission.');
        }

        if (!is_super_admin()) {
            // User is not a network admin
            wp_die('Sorry, you do not have permission to access this page');
        }

        // Get all active public blogs
        $blogs = get_sites([
            'public' => 1,
            'archived' => 0,
            'deleted' => 0,
        ]);

        foreach ($blogs as $blog) {
            // Loop through each blog and update
            switch_to_blog($blog->blog_id);

            // Collect and sanitize values for this site
            $shortname = strtoupper(sanitize_text_field(stripslashes($_POST['shortcode_' . $blog->blog_id])));
            $locg = (int) sanitize_text_field(stripslashes($_POST['locg_' . $blog->blog_id]));
            $cat_link = sanitize_text_field(stripslashes($_POST['cat_link_' . $blog->blog_id]));

            // Note: The previous carousel plugin appears to have put NA in as a placeholder for unset shortcodes so
            //       we test for it here
            if (!empty($shortname) && $shortname !== "NA") {
                update_option('_coop_sitka_lib_shortname', $shortname);
            }

            // Sitka Locale (locg)
            if (is_numeric($locg)) {
                update_option('_coop_sitka_lib_locg', $locg);
            }

            // Catalogue Link
            if (!empty($cat_link)) {
                update_option('_coop_sitka_lib_cat_link', $cat_link);
            }

            restore_current_blog();
        }

        // Return to the form page
        wp_redirect(network_admin_url('sites.php?page=sitka-libraries'));
    }
}
