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
        parent::saveChangeCallback();

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
