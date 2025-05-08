<?php

namespace BCLibCoop\SiteManager\Widget;

use WP_Query;

class TermsWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->slug = 'terms-link-widget';
        $this->name = 'Terms of Use Link';
        $this->options = [
            'description' => 'Displays a link to a post or page with the tag "terms-of-use"',
        ];

        parent::__construct();
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget displays a link to a post or page with the tag '
            . '<code>terms-of-use</code> if one is found.</p>';

        return 'noform';
    }

    public function widget($args, $instance)
    {
        if (file_exists($this->viewPath)) {
            $terms = new WP_Query([
                'tag' => 'terms-of-use',
                'post_type' => ['page', 'post'],
                'post_status' => 'publish',
                'posts_per_page' => 1,
            ]);

            if ($terms->have_posts()) {
                $terms->the_post();
                require $this->viewPath;
                wp_reset_postdata();
            }
        } else {
            echo '<!-- Widget View Not Found: ' . $this->slug . ' -->';
        }
    }
}
