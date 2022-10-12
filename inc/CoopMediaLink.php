<?php

namespace BCLibCoop\SiteManager;

class CoopMediaLink extends AbstractSiteManagerPage
{
    public static $slug = 'coop-media-links';
    public static $page_title = 'Media Link';
    public static $menu_title = 'Media Link';
    public static $shortcode = 'coop-media-link';

    protected $position = 3;

    public function init()
    {
        parent::init();

        // Set default options if not already set
        foreach ($this->languages as $curlang) {
            /**
             * When languages are present, this does generate an option in the format:
             * coop-media-linksen_CA-label-text, which is maybe not the nicest, but is
             * kept for backwards compatibility
             */
            add_option(static::$slug . $curlang->locale . '-label-text', 'Download Digital Media');
            add_option(static::$slug . $curlang->locale . '-uri', '/research/download-digital-media');
        }
    }

    public function saveChangeCallback()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            ! isset($_POST['_wpnonce'])
            || ! wp_verify_nonce($_POST['_wpnonce'], static::$slug . '_submit')
        ) {
            wp_die('Sorry, there was an error handling your form submission.');
        }

        foreach ($this->languages as $curlang) {
            $link_text = sanitize_text_field($_POST[static::$slug . $curlang->locale . '-label-text']);
            $link_uri = sanitize_text_field($_POST[static::$slug . $curlang->locale . '-uri']);

            update_option(static::$slug . $curlang->locale . '-label-text', $link_text);
            update_option(static::$slug . $curlang->locale . '-uri', $link_uri);
        }

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }
}
