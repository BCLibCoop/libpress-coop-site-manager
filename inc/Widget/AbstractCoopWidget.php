<?php

namespace BCLibCoop\SiteManager\Widget;

use WP_Widget;

abstract class AbstractCoopWidget extends WP_Widget
{
    public $slug;
    public $name;
    public $adminPage;
    public $viewPath;
    public $options = [];

    public function __construct()
    {
        if (empty($this->viewPath)) {
            $this->viewPath = plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . "views/widget/{$this->slug}.php";
        }

        parent::__construct(
            $this->slug,
            $this->name,
            $this->options
        );
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
        admin_url('admin.php?page=' . $this->adminPage::$slug) . '">Site Manager</a></p>';

        return 'noform';
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
