<?php

namespace BCLibCoop\SiteManager;

class CoopMediaLink extends AbstractSiteManagerPage
{
    public static $slug = 'coop-media-links';
    public static $page_title = 'Media Link';
    public static $menu_title = 'Media Link';

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

        add_shortcode('coop-media-link', [&$this, 'coopMediaLinkShortcode']);
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

    /**
     * Store option to map URL to unqiue address
     * per co-op client library
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
            $out[] = '<label for="' . $prefix . '-uri">' . $curlang->name . ' Media Link URI:</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="' . $prefix . '-uri" name="' . $prefix . '-uri"  value="'
                     . $link_uri . '" class="regular-text">';
            $out[] = '</td>';
            $out[] = '</tr>';

            $out[] = '<tr valign="top">';
            $out[] = '<th scope="row">';
            $out[] = '<label for="' . $prefix . '-label-text">' . $curlang->name . ' Media Link Label:</label>';
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

    /**
     * Front-side shortcode callback
     **/
    public function coopMediaLinkShortcode()
    {
        // Check if polylang is available and if so get correct info for configured language
        if (function_exists('pll_languages_list')) {
            $link_text = stripslashes(get_option(static::$slug . get_locale() . '-label-text'));
            $link_uri = get_option(static::$slug . get_locale() . '-uri');
        } else {
            $link_text = stripslashes(get_option(static::$slug . '-label-text'));
            $link_uri = get_option(static::$slug . '-uri');
        }

        return '<a class="coop-media-link overdrive-link" href="' . $link_uri . '">' . $link_text . '</a>';
    }
}
