<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopFooter;

class FooterTextWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopFooter::class;
        $this->slug = 'footer-text-widget';
        $this->name = 'Footer Text';

        parent::__construct();
    }

    public function widget($args, $instance)
    {
        // Check if polylang is available and if so get correct info for configured language
        $option = implode('-', array_filter([
            $this->adminPage::$slug,
            function_exists('pll_languages_list') ? get_locale() : '',
            'footer-text'
        ]));
        $footer_text = stripslashes(get_option($option));

        // Text substitutions
        $footer_text = preg_replace_callback('/\{\{(.*)\}\}/', function ($matches) {
            switch (trim($matches[1])) {
                case 'year':
                    return date('Y');
                default:
                    return '';
            }
        }, $footer_text);

        require $this->viewPath;
    }
}
