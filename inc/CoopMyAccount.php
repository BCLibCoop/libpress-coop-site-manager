<?php

namespace BCLibCoop\SiteManager;

class CoopMyAccount extends AbstractSiteManagerPage
{
    public static $slug = 'coop-my-account';
    public static $page_title = 'My Account Link';
    public static $menu_title = 'My Account Link';

    protected $position = 4;

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
            $link_text = stripslashes($_POST[static::$slug . $curlang->locale . '-label-text']);
            $link_text = sanitize_text_field($link_text);
            $link_uri = stripslashes($_POST[static::$slug . $curlang->locale . '-uri']);
            $link_uri = sanitize_text_field($link_uri);

            update_option(static::$slug . $curlang->locale . '-label-text', $link_text);
            update_option(static::$slug . $curlang->locale . '-uri', $link_uri);
        }

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }
}
