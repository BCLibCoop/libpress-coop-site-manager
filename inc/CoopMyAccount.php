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
            $link_text = sanitize_text_field($_POST[static::$slug . $curlang->locale . '-label-text']);
            $link_uri = sanitize_text_field($_POST[static::$slug . $curlang->locale . '-uri']);

            update_option(static::$slug . $curlang->locale . '-label-text', $link_text);
            update_option(static::$slug . $curlang->locale . '-uri', $link_uri);
        }

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }

    /**
     * Store option to map URL to unqiue address
     * per coop client library
     **/
    public function adminSettingsPageContent()
    {
        $out = [];

        $out[] = '<table class="form-table">';

        foreach ($this->languages as $curlang) {
            $link_text = stripslashes(get_option(static::$slug . $curlang->locale . '-label-text'));
            $link_uri = get_option(static::$slug . $curlang->locale . '-uri');
            $prefix = static::$slug . $curlang->locale;

            $out[] = '<tr valign="top">';
            $out[] = '<th scope="row">';
            $out[] = '<label for="' . $prefix . '-uri">' . $curlang->name . ' Account Login URI:</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="' . $prefix . '-uri" name="' . $prefix . '-uri"  value="'
                     . $link_uri . '" class="regular-text">';
            $out[] = '</td>';
            $out[] = '</tr>';

            $out[] = '<tr valign="top">';
            $out[] = '<th scope="row">';
            $out[] = '<label for="' . $prefix . '-label-text">' . $curlang->name . ' Account Login Label:</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="' . $prefix . '-label-text" name="' . $prefix . '-label-text"  value="'
                     . $link_text . '" class="regular-text">';
            $out[] = '</td>';
            $out[] = '</tr>';
        }

        $out[] = '</table>';

        return $out;
    }
}
