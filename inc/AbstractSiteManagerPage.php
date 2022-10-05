<?php

namespace BCLibCoop\SiteManager;

use function pll_languages_list;

abstract class AbstractSiteManagerPage
{
    public static $slug;
    public static $page_title;
    public static $menu_title;

    protected $languages;
    protected $position = null;

    public function __construct()
    {
        // Dummy langauges option for when Polylang is not in use
        $this->languages = [
            (object) [
                'locale' => '',
                'name' => '',
            ],
        ];

        add_action('init', [$this, 'init']);

        if (class_exists(static::class . 'Widget', true)) {
            add_action('widgets_init', [$this, 'widgetsInit']);

            add_filter('option_sidebars_widgets', [$this, 'legacySidebarConfig']);
            add_filter('option_widget_' . static::$slug . '-widget', [$this, 'legacyWidgetInstance']);
        }
    }

    public function init()
    {
        // Check if polylang is available and if so use its list of languages
        if (function_exists('pll_languages_list')) {
            $this->languages = pll_languages_list('fields');
        }

        if (is_admin()) {
            add_action('admin_menu', [$this, 'addMenu']);
            add_action('admin_post_' . static::$slug . '_submit', [$this, 'saveChangeCallback']);

            add_action('admin_enqueue_scripts', [$this, 'adminEnqueueStylesScripts']);
        }
    }

    public function adminEnqueueStylesScripts()
    {
        $screen = get_current_screen();

        if ($screen->id === CoopSiteManager::$slug . '_page_' . static::$slug) {
            $js_file = 'js/' . static::$slug . '-admin.js';
            $css_file = 'css/' . static::$slug . '-admin.css';

            if (file_exists(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . $js_file)) {
                wp_enqueue_script(
                    static::$slug . '-admin-js',
                    plugins_url($js_file, SITEMANAGER_PLUGIN_FILE),
                    ['jquery'],
                    filemtime(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . $js_file)
                );
            }

            if (file_exists(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . $css_file)) {
                wp_enqueue_style(
                    static::$slug,
                    plugins_url($css_file, SITEMANAGER_PLUGIN_FILE),
                    [],
                    filemtime(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . $css_file)
                );
            }
        }
    }

    public function widgetsInit()
    {
        register_widget(static::class . 'Widget');
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
                        in_array($widget, [static::$slug . '-widget'])
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

    public function addMenu()
    {
        add_submenu_page(
            CoopSiteManager::$slug,
            static::$page_title,
            static::$menu_title,
            'manage_local_site',
            static::$slug,
            [$this, 'adminSettingsPage'],
            $this->position
        );
    }

    public function adminSettingsPage()
    {
        if (!current_user_can('manage_local_site')) {
            wp_die('You do not have required permissions to view this page');
        }

        $out = [];

        $out[] = '<div class="wrap">';

        $out[] = '<h1 class="wp-heading-inline">' . static::$page_title . '</h1>';
        $out[] = '<hr class="wp-header-end">';

        $out[] = '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';

        $out = array_merge($out, $this->adminSettingsPageContent());

        $out[] = '<p class="submit">';
        $out[] = '<input type="hidden" name="action" value="' . static::$slug . '_submit">';
        $out[] = wp_nonce_field(static::$slug . '_submit');
        $out[] = '<input type="submit" value="Save Changes" class="button button-primary" id="'
                 . static::$slug . '-submit" name="submit">';
        $out[] = '</p>';
        $out[] = '</form>';
        $out[] = '</div>';

        echo implode("\n", $out);
    }

    abstract public function saveChangeCallback();

    abstract public function adminSettingsPageContent();
}
