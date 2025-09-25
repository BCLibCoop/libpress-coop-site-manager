<?php

namespace BCLibCoop\SiteManager;

class LinkReport extends AbstractSiteManagerPage
{
    public static $slug = 'coop-link-report';
    public static $page_title = 'Site Links Report';
    public static $menu_title = 'Links Report';

    protected $position = 10;
    protected $submit_button = 'Download CSV';

    /**
     * Find URLs in post content and menus, to be used for dead link checking
     * and general link auditing
     */
    public function saveChangeCallback()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            ! isset($_POST['_wpnonce'])
            || ! wp_verify_nonce($_POST['_wpnonce'], static::$slug . '_submit')
        ) {
            wp_die('Sorry, there was an error handling your request.');
        }

        $site_url = parse_url(get_bloginfo('url'));
        $clean_url = preg_replace('/[\.\/]/', '_', $site_url['host'] . rtrim($site_url['path'], '/'));

        $filename = sprintf('libpress-link-report-%s-%s.csv', $clean_url, wp_date('Ymd_His'));

        header('Content-Type: text/csv');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 30 Nov 1987 11:29:00 GMT');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $csv = [];
        $urls = [];

        /**
         * Menu Links
         */

        $menu_locations = get_nav_menu_locations();

        foreach ($menu_locations as $location_name => $menu_id) {
            $name = wp_get_nav_menu_name($location_name);
            $items = wp_get_nav_menu_items($menu_id) ?: [];

            foreach ($items as $item) {
                if ($item->type == "custom") {
                    $urls[$item->url][] = "{$name} - {$item->title}";
                }
            }
        }

        /**
         * Post content
         */

        $posts = get_posts([
            'post_type' => ['post', 'page', 'highlight'],
            'post_status' => 'publish',
            'nopaging' => true,
        ]);

        foreach ($posts as $post) {
            foreach (wp_extract_urls(get_the_content(null, null, $post)) as $post_url) {
                $urls[$post_url][] = get_permalink($post);
            }
        }

        /**
         * Sorting and processing URLs
         */

        // Remove any non-URLs that get caught by wp_extract_urls()
        $urls = array_filter($urls, fn($url) => str_starts_with($url, 'http'), ARRAY_FILTER_USE_KEY);

        foreach ($urls as $url => $in_post) {
            $csv[] = [
                $url,
                join(", ", $in_post)
            ];
        }

        // Sort by URL
        array_multisort(array_column($csv, 0), SORT_ASC, $csv);

        // Add header after sorting
        array_unshift($csv, ['URL', 'Found on Pages']);

        $out = fopen('php://output', 'w');
        foreach ($csv as $line) {
            fputcsv($out, $line);
        }
        fclose($out);

        die();
    }
}
