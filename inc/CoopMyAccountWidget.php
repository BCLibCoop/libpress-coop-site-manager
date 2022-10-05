<?php

namespace BCLibCoop\SiteManager;

class CoopMyAccountWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            CoopMyAccount::$slug . '-widget',
            'My Account',
            ['classname' => 'CoopMyAccount_coop_my_account_widget']
        );
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
        admin_url('admin.php?page=' . CoopMyAccount::$slug) . '">Site Manager</a></p>';

        return 'noform';
    }

    public function widget($args, $instance)
    {
        extract($args);

        // Check if polylang is available and if so get correct info for configured language
        if (function_exists('pll_languages_list')) {
            $link_text = stripslashes(get_option(CoopMyAccount::$slug . get_locale() . '-label-text'));
            $link_uri = get_option(CoopMyAccount::$slug . get_locale() . '-uri');
        } else {
            $link_text = stripslashes(get_option(CoopMyAccount::$slug . '-label-text'));
            $link_uri = get_option(CoopMyAccount::$slug . '-uri');
        }

        $link = '<a href="' . $link_uri . '">' . $link_text . '</a>';

        $out = [];

        $out[] = $before_widget;
        $out[] = $before_title . $link . $after_title;
        $out[] = $after_widget;

        echo implode("\n", $out);
    }
}
