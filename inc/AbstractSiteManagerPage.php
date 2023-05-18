<?php

namespace BCLibCoop\SiteManager;

use function pll_languages_list;

abstract class AbstractSiteManagerPage
{
    public static $slug;
    public static $page_title;
    public static $menu_title;
    public static $shortcode;

    /**
     * Potential Languages
     *
     * @var object[]
     */
    protected $languages;

    protected $position = null;
    protected $widgets = [];
    protected $settings_api = false;
    protected $capability = 'manage_local_site';

    public function __construct()
    {
        // Dummy languages option for when Polylang is not in use
        $this->languages = [
            (object) [
                'locale' => '',
                'name' => '',
            ],
        ];

        // Check if polylang is available and if so use its list of languages
        if (function_exists('pll_languages_list')) {
            $this->languages = pll_languages_list('fields');
        }

        if (empty($this::$shortcode)) {
            $this::$shortcode = $this::$slug;
        }

        add_action('init', [$this, 'init'], 4);

        /**
         * Try and find a widget class matching this class, but also allow for
         * the class to already be specified by the extending class
         */
        $matching_widget = __NAMESPACE__ . '\\Widget\\' . (new \ReflectionClass($this))->getShortName() . 'Widget';

        if (class_exists($matching_widget) && !in_array($matching_widget, $this->widgets)) {
            $this->widgets[static::$slug . '-widget'] = $matching_widget;
        }

        if (!empty($this->widgets)) {
            add_action('widgets_init', [$this, 'widgetsInit']);

            add_filter('option_sidebars_widgets', [$this, 'legacySidebarConfig']);

            foreach ($this->widgets as $widget_slug => $widget_class) {
                add_filter('option_widget_' . $widget_slug, [$this, 'legacyWidgetInstance']);
            }
        }

        if (file_exists(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . "views/shortcode/{$this::$slug}.php")) {
            add_shortcode($this::$shortcode, [$this, 'doShortcode']);
        }
    }

    public function init()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addMenu']);

            add_action('admin_enqueue_scripts', [$this, 'adminEnqueueStylesScripts']);

            if ($this->settings_api) {
                add_filter('option_page_capability_' . static::$slug, [$this, 'settingsApiCapability']);
            } else {
                add_action('admin_post_' . static::$slug . '_submit', [$this, 'saveChangeCallback']);
            }
        }
    }

    public function settingsApiCapability()
    {
        return $this->capability;
    }

    public function adminEnqueueStylesScripts()
    {
        $screen = get_current_screen();

        if ($screen->id === CoopSiteManager::$slug . '_page_' . static::$slug) {
            $this->adminStylesScripts();
        }
    }

    public function adminStylesScripts()
    {
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

    public function widgetsInit()
    {
        foreach ($this->widgets as $widget_slug => $widget_class) {
            register_widget($widget_class);
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
                        in_array($widget, array_keys($this->widgets))
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
            $this->capability,
            static::$slug,
            [$this, 'adminSettingsPage'],
            $this->position
        );

        if ($this->settings_api) {
            add_settings_section(
                static::$slug . '-settings',
                null,
                '__return_false',
                static::$slug
            );
        }
    }

    public function adminSettingsPage()
    {
        if (!current_user_can($this->capability)) {
            wp_die('You do not have required permissions to view this page');
        }

        $out = [];

        $out[] = '<div class="wrap">';

        $out[] = '<h1 class="wp-heading-inline">' . static::$page_title . '</h1>';
        $out[] = '<hr class="wp-header-end">';

        $out[] = '<form action="'
            . esc_url(admin_url($this->settings_api ? 'options.php' : 'admin-post.php')) . '" method="post">';

        if ($this->settings_api) {
            ob_start();
            do_settings_sections(static::$slug);
            $out[] = ob_get_clean();
        } else {
            $content = $this->adminSettingsPageContent();

            if (is_array($content)) {
                $out = array_merge($out, $content);
            } else {
                $out[] = $content;
            }
        }

        if ($this->settings_api) {
            ob_start();
            settings_fields(static::$slug);
            $out[] = ob_get_clean();
        } else {
            $out[] = '<input type="hidden" name="action" value="' . static::$slug . '_submit">';
            $out[] = wp_nonce_field(static::$slug . '_submit');
        }
        $out[] = get_submit_button('', 'primary', 'submit', true, ['id' => static::$slug . '-submit']);
        $out[] = '</form>';
        $out[] = '</div>';

        echo implode("\n", $out);
    }

    /**
     * Return the output of the shortcode template
     */
    public function doShortcode()
    {
        ob_start();
        require plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . "views/shortcode/{$this::$slug}.php";
        return ob_get_clean();
    }

    /**
     * Return the output of the shortcode template
     */
    public function adminSettingsPageContent()
    {
        ob_start();
        require plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . "views/admin/{$this::$slug}.php";
        return ob_get_clean();
    }

    abstract public function saveChangeCallback();
}
