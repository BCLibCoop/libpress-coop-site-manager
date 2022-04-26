<?php

namespace BCLibCoop\SiteManager;

use function pll__;

class ContactInfoWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            CoopSiteManager::$slug . '-widget',
            'Contact Information',
            ['classname' => 'CoopSiteManager_coop_site_manager_ci_widget']
        );
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
        admin_url('admin.php?page=site-manager') . '">Site Manager</a></p>';
        return 'noform';
    }

    public function widget($args, $instance)
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
