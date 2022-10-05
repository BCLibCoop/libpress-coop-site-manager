<?php

namespace BCLibCoop\SiteManager;

use function pll_register_string;

class CoopSiteManager
{
    private static $instance;

    public static $slug = 'site-manager';

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
        if (is_admin()) {
            // Add admin menu item with a lower priority so it is avaliable for plugins adding child menus
            add_action('admin_menu', [&$this, 'addMenu'], 5);
        }
    }

    public function addMenu()
    {
        add_menu_page(
            'Site Manager',
            'Site Manager',
            'manage_local_site',
            self::$slug,
            '',
            '',
            29
        );
    }
}
