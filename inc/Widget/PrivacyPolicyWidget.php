<?php

namespace BCLibCoop\SiteManager\Widget;

use WP_Query;

class PrivacyPolicyWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->slug = 'privacy-link-widget';
        $this->name = 'Privacy Policy Link';

        parent::__construct();
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget displays a link to a post or page with the tag '
            . '<code>privacy-policy</code> if one is found.</p>';

        return 'noform';
    }

    public function widget($args, $instance)
    {
        $privacy = new WP_Query([
            'tag' => 'privacy-policy',
            'post_type' => ['page', 'post'],
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($privacy->have_posts()) {
            $privacy->the_post();
            require $this->viewPath;
            wp_reset_postdata();
        }
    }
}
