<?php

/**
 * Coop Site Manager
 *
 * Creates the "Site Manager" admin menu page, as well as a Contact Info page
 * and a contact info widget
 *
 * PHP Version 7
 *
 * @package           Coop Site Manager
 * @author            Erik Stainsby <eric.stainsby@roaringsky.ca>
 * @author            Ben Holt <ben.holt@bc.libraries.coop>
 * @author            Sam Edwards <sam.edwards@bc.libraries.coop>
 * @copyright         2013-2021 BC Libraries Cooperative
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Coop Site Manager
 * Description:       This is the common location for the other Coop Plugins to reside.
 * Version:           2.0.1
 * Network:           true
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            BC Libraries Cooperative
 * Author URI:        https://bc.libraries.coop
 * Text Domain:       coop-site-manager
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace BCLibCoop;

use function pll_register_string;

class CoopSiteManager
{
    private static $instance;

    public static $slug = 'coop-site-manager';

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
        require_once 'inc/ContactInfoWidget.php';

        register_widget(__NAMESPACE__ . '\ContactInfoWidget');
    }

    public function adminEnqueueStylesScripts($hook)
    {
        if ($hook !== 'toplevel_page_site-manager') {
            return;
        }

        wp_enqueue_script('coop-site-manager-admin-js', plugins_url('/js/coop-site-manager.js', __FILE__), ['jquery']);
        wp_enqueue_style('coop-site-manager-admin', plugins_url('/css/coop-site-manager.css', __FILE__), false);
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

        $info = get_option('coop-ci-info', [
          'heading' => '',
          'email' => '',
          'phone' => '',
          'fax' => '',
          'address' => '',
          'address2' => '',
          'city' => '',
          'prov' => '',
          'pcode' => '',
        ]);

        $out = [];

        $out[] = '<div class="wrap">';

        $out[] = '<div id="icon-options-general" class="icon32">';
        $out[] = '<br>';
        $out[] = '</div>';

        $out[] = '<h2>Contact Information parameters</h2>';

        $out[] = '<p>Contact info used on the front page of the site</p>';

        $out[] = '<table class="form-table">';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Heading:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-heading" name="coop-ci-heading" class="coop-ci input-wide" value="'
                 . $info['heading'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Email:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-email" name="coop-ci-email" class="coop-ci input-wide" value="'
                 . $info['email'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Phone:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-phone" name="coop-ci-phone" class="coop-ci input-wide" value="'
                 . $info['phone'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Fax:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-fax" name="coop-ci-fax" class="coop-ci input-wide" value="'
                 . $info['fax'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Address 1:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-address" name="coop-ci-address" class="coop-ci input-wide" value="'
                 . $info['address'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Address 2:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-address-2" name="coop-ci-address-2" class="coop-ci input-wide" value="'
                 . $info['address2'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">City/Town:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-city" name="coop-ci-form" class="coop-ci input-wide" value="'
                 . $info['city'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Province:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-prov" name="coop-ci-prov" class="coop-ci input-wide" value="'
                 . $info['prov'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '<tr valign="top">';
        $out[] = '<th scope="row">';
        $out[] = '<label for="tag">Postal Code:</label>';
        $out[] = '</th>';
        $out[] = '<td>';
        $out[] = '<input type="text" id="coop-ci-pcode" name="coop-ci-pcode" class="coop-ci input-wide" value="'
                 . $info['pcode'] . '">';
        $out[] = '</td>';
        $out[] = '</tr>';

        $out[] = '</table>';

        $out[] = '<p class="submit">';
        $out[] = '<input type="submit" value="Save Changes" class="button button-primary" id="coop-ci-submit" '
                 . 'name="submit">';
        $out[] = '</p>';

        echo implode("\n", $out);
    }

    public function adminContactInfoPageSave()
    {
        $info = [
            'heading'  => sanitize_text_field($_POST['heading']),
            'email'   => sanitize_text_field($_POST['email']),
            'phone'   => sanitize_text_field($_POST['phone']),
            'fax'     => sanitize_text_field($_POST['fax']),
            // 'enable_form'    => $_POST['enable_form'],
            'address'   => sanitize_text_field($_POST['address']),
            'address2'   => sanitize_text_field($_POST['address2']),
            'city'     => sanitize_text_field($_POST['city']),
            'prov'     => sanitize_text_field($_POST['prov']),
            'pcode'   => sanitize_text_field($_POST['pcode'])
        ];

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

// No direct access
defined('ABSPATH') || die(-1);

require_once 'inc/json-ld.php';

new CoopSiteManager();
new LibPressSchema();
