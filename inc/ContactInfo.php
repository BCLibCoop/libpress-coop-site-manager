<?php

namespace BCLibCoop\SiteManager;

use function pll_register_string;

class ContactInfo extends AbstractSiteManagerPage
{
    public static $slug = 'site-manager'; // Back compat stuff
    public static $page_title = 'Contact Information';
    public static $menu_title = 'Contact Information';

    private static $fields = [
        'heading' => 'Heading',
        'email' => 'Email',
        'phone' => 'Phone',
        'fax' => 'Fax',
        'address' => 'Address 1',
        'address2' => 'Address 2',
        'city' => 'City/Town',
        'prov' => 'Province',
        'pcode' => 'Postal Code',
    ];

    public function __construct()
    {
        parent::__construct();

        // Legacy naming
        add_filter('option_widget_hours-widget', [&$this, 'legacyWidgetInstance']);
        add_filter('option_widget_coop-site-manager-widget', [&$this, 'legacyWidgetInstance']);
    }

    public function init()
    {
        parent::init();

        // Register strings for multilingual support
        if (function_exists('pll_register_string')) {
            pll_register_string('Email Us', 'Email Us', 'coop-site-manager');
            pll_register_string('Phone', 'Phone', 'coop-site-manager');
            pll_register_string('Fax', 'Fax', 'coop-site-manager');
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

        $info = [];

        foreach (static::$fields as $field_key => $field_name) {
            $info[$field_key] = !empty($_POST[$field_key]) ? sanitize_text_field($_POST[$field_key]) : '';
        }

        update_option('coop-ci-info', $info);

        wp_redirect(admin_url('admin.php?page=' . CoopSiteManager::$slug));
        exit;
    }

    public function adminSettingsPageContent()
    {
        $info = get_option('coop-ci-info', array_fill_keys(array_keys(static::$fields), ''));

        $out = [];

        $out[] = '<p>Contact info used on the front page of the site</p>';

        $out[] = '<table class="form-table">';

        foreach (static::$fields as $field_key => $field_name) {
            $out[] = '<tr valign="top">';
            $out[] = '<th scope="row">';
            $out[] = '<label for="coop-ci-' . $field_key . '">' . $field_name . ':</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="coop-ci-' . $field_key . '" name="' . $field_key . '" '
                . 'class="coop-ci regular-text" value="' . $info[$field_key] . '">';
            $out[] = '</td>';
            $out[] = '</tr>';
        }

        $out[] = '</table>';

        return $out;
    }
}
