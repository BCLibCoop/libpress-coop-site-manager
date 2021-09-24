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
 * Version:           1.1.3
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

use function pll__;
use function pll_register_string;

class CoopSiteManager
{
    private static $instance;

    public $slug = 'coop-site-manager';

    public function __construct()
    {
        if (isset(self::$instance)) {
            return;
        }

        self::$instance = $this;

        add_action('init', [&$this, 'init']);
    }

    public function init()
    {
        wp_register_sidebar_widget(
            $this->slug . '-widget',
            'Contact Information',
            [&$this, 'contactInfoWidget'],
            ['classname' => 'CoopSiteManager_coop_site_manager_ci_widget']
        );

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

    public function contactInfoWidget($args)
    {
        extract($args);

        $out = [];
        $out[] = $before_widget;

        $info = get_option('coop-ci-info', []);

        if (!empty($info)) {
            $out[] = $before_title . $info['heading'] . $after_title;
            $out[] = '<div class="coop-contact-info">';
            if (!empty($info['email'])) {
                $out[] = '<a href="mailto:' . $info['email'] . '">'
                         . (function_exists('pll__') ? pll__('Email Us', 'coop-site-manager') : 'Email Us')
                         . '</a><br/>';
            }
            if (!empty($info['phone'])) {
                $out[] = '<strong>' . (function_exists('pll__') ? pll__('Phone', 'coop-site-manager') : 'Phone')
                         . '</strong> ' . $info['phone'] . '<br/>';
            }
            if (!empty($info['fax'])) {
                $out[] = '<strong>' . (function_exists('pll__') ? pll__('Fax', 'coop-site-manager') : 'Fax')
                        . '</strong> ' . $info['fax'] . '<br/>';
            }
            if (!empty($info['address'])) {
                $out[] = $info['address'] . '<br/>';
                if (!empty($info['address2'])) {
                    $out[] = $info['address2'] . '<br/>';
                }
                $out[] = $info['city'] . ' ' . $info['prov'] . ' ' . $info['pcode'] . '<br/>';
            }
            $out[] = '</div><!-- .coop-contact-info -->';
        } else {
            $out[] = '<!-- no results from ContactInfo plugin -->';
        }

        $out[] = $after_widget;

        echo implode("\n", $out);
    }
}

// No direct access
defined('ABSPATH') || die(-1);

require_once 'inc/json-ld.php';

new CoopSiteManager();
new LibPressSchema();
