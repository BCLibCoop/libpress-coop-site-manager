<?php

namespace BcLibCoop\SiteManager;

use function pll_register_string;

class CoopSiteManager
{
    private static $instance;

    public static $slug = 'coop-site-manager';

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
        if (isset(self::$instance)) {
            return;
        }

        self::$instance = $this;

        add_action('init', [&$this, 'init']);
        add_action('widgets_init', [&$this, 'widgetsInit']);

        add_filter('option_sidebars_widgets', [&$this, 'legacySidebarConfig']);
        add_filter('option_widget_hours-widget', [&$this, 'legacyWidgetInstance']);
        add_filter('option_widget_coop-site-manager-widget', [&$this, 'legacyWidgetInstance']);
    }

    public function init()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [&$this, 'adminEnqueueStylesScripts']);
            // Add admin menu item with a lower priority so it is avaliable for plugins adding child menus
            add_action('admin_menu', [&$this, 'addSiteManagerMenu'], 5);
            add_action('wp_ajax_coop-save-ci-change', [&$this, 'adminContactInfoPageSave']);
        }

        // Register strings for multilingual support
        if (function_exists('pll_register_string')) {
            pll_register_string('Email Us', 'Email Us', 'coop-site-manager');
            pll_register_string('Phone', 'Phone', 'coop-site-manager');
            pll_register_string('Fax', 'Fax', 'coop-site-manager');
        }
    }

    /**
     * Widget previously registered as a single widget, add an instance ID
     * so they continue to function correctly
     */
    public function legacySidebarConfig($sidebars)
    {
        foreach ($sidebars as &$sidebar_widgets) {
            if (is_array($sidebar_widgets)) {
                foreach ($sidebar_widgets as &$widget) {
                    if (
                        in_array($widget, ['coop-site-manager-widget'])
                        && ! preg_match('/-\d$/', $widget)
                    ) {
                        $widget = $widget . '-1';
                        break;
                    }
                }
            }
        }

        return $sidebars;
    }

    /**
     * Widget previously registered as a single widget, add a setting for
     * the first instance if one doesn't exist
     */
    public function legacyWidgetInstance($widget_settings)
    {
        if (!isset($widget_settings[1])) {
            $widget_settings[1] = [];
        }

        return $widget_settings;
    }

    public function widgetsInit()
    {
        register_widget(__NAMESPACE__ . '\ContactInfoWidget');
    }

    public function adminEnqueueStylesScripts($hook)
    {
        if ($hook !== 'toplevel_page_site-manager') {
            return;
        }

        wp_enqueue_script(
            'coop-site-manager-admin-js',
            plugins_url('/js/coop-site-manager.js', dirname(__FILE__)),
            ['jquery'],
            get_plugin_data(SITEMANAGER_PLUGIN_FILE, false, false)['Version'],
            true
        );
        wp_enqueue_style(
            'coop-site-manager-admin',
            plugins_url('/css/coop-site-manager.css', dirname(__FILE__)),
            get_plugin_data(SITEMANAGER_PLUGIN_FILE, false, false)['Version']
        );
    }

    public function addSiteManagerMenu()
    {
        add_menu_page(
            'Contact Information',
            'Site Manager',
            'manage_local_site',
            'site-manager',
            [&$this, 'adminContactInfoPage'],
            '',
            29
        );

        add_submenu_page(
            'site-manager',
            'Contact Information',
            'Contact Information',
            'manage_local_site',
            'site-manager',
            [&$this, 'adminContactInfoPage']
        );
    }

    public function adminContactInfoPage()
    {
        if (!current_user_can('manage_local_site')) {
            wp_die('You do not have required permissions to view this page');
        }

        $info = get_option('coop-ci-info', array_fill_keys(array_keys(self::$fields), ''));

        $out = [];

        $out[] = '<div class="wrap">';

        $out[] = '<h1 class="wp-heading-inline">Contact Information</h1>';
        $out[] = '<hr class="wp-header-end">';

        $out[] = '<p>Contact info used on the front page of the site</p>';

        $out[] = '<table class="form-table">';

        foreach (self::$fields as $field_key => $field_name) {
            $out[] = '<tr valign="top">';
            $out[] = '<th scope="row">';
            $out[] = '<label for="coop-ci-' . $field_key . '">' . $field_name . ':</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="coop-ci-' . $field_key . '" name="' . $field_key . '" '
                . 'class="coop-ci input-wide" value="' . $info[$field_key] . '">';
            $out[] = '</td>';
            $out[] = '</tr>';
        }

        $out[] = '</table>';

        $out[] = '<p class="submit">';
        $out[] = '<input type="submit" value="Save Changes" class="button button-primary" id="coop-ci-submit" '
                 . 'name="submit">';
        $out[] = '</p>';

        echo implode("\n", $out);
    }

    public function adminContactInfoPageSave()
    {
        $info = [];

        foreach (self::$fields as $field_key => $field_name) {
            $info[$field_key] = !empty($_POST[$field_key]) ? sanitize_text_field($_POST[$field_key]) : '';
        }

        if (update_option('coop-ci-info', $info)) {
            wp_send_json([
                'result' => 'success',
                'feedback' => 'Saved changes',
            ]);
        }

        wp_send_json([
            'result' => 'failed',
            'feedback' => 'Failed to save changes',
        ]);
    }
}
