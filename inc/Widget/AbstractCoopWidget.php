<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\AbstractSiteManagerPage;
use WP_Widget;

abstract class AbstractCoopWidget extends WP_Widget
{
    public $slug;
    public $name;
    public $adminPage;
    public $viewPath;
    public $options = [];

    /**
     * Potential Languages
     *
     * @var object[]
     */
    protected $languages;

    public function __construct()
    {
        if (empty($this->viewPath)) {
            $this->viewPath = plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . "views/widget/{$this->slug}.php";
        }

        // Dummy langauges option for when Polylang is not in use
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

        parent::__construct(
            $this->slug,
            $this->name,
            $this->options
        );
    }

    public function form($instance)
    {
        if (is_subclass_of($this->adminPage, AbstractSiteManagerPage::class)) {
            echo '<p class="no-options-widget">This widget is configured via the <a href="' .
                admin_url('admin.php?page=' . $this->adminPage::$slug) . '">Site Manager</a></p>';

            return 'noform';
        } else {
            parent::form($instance);
        }
    }

    public function widget($args, $instance)
    {
        if (file_exists($this->viewPath)) {
            require $this->viewPath;
        } else {
            echo '<!-- Widget View Not Found: ' . $this->slug . ' -->';
        }
    }
}
