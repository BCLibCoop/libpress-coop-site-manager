<?php

namespace BCLibCoop\SiteManager;

use function pll__;

class CoopHoursWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'hours-widget',
            'Hours of Operation',
            ['classname' => 'CoopHours_hours_widget']
        );
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
            admin_url('admin.php?page=hours-setup') . '">Site Manager</a></p>';
        return 'noform';
    }

    public function widget($args, $instance)
    {
        extract($args);

        $days = get_option('coop-hours-days');
        $notes = get_option('coop-hours-notes');
        $out = [];

        $out[] = $before_widget;

        $out[] = $before_title;
        $out[] = (function_exists('pll__') ? pll__('Hours of Operation', 'coop-hours') : 'Hours of Operation');
        $out[] = $after_title;

        if (!empty($days)) {
            $out[] = '<ul class="operating-hours">';

            foreach ($days as $key => $value) {
                $out[] = '<li class="hours-day ' . $key . '">';

                $out[] = '<span class="hours-dow">' . (function_exists('pll__') ?
                    pll__(ucfirst($key), 'coop-hours') : ucfirst($key)) . '</span>';

                if (filter_var($value['notopen'] ?? false, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) {
                    $out[] = '<span class="hours-notopen">'
                    . (function_exists('pll__') ? pll__('Closed', "coop-hours") : 'Closed')
                    . '</span>';
                } else {
                    foreach ([1, 2] as $period) {
                        $period_array = $period > 1 ? "_$period" : '';

                        if (!empty($value['open' . $period_array])) {
                            if ($period > 1) {
                                $out[] = '<span class="period-separator">&amp;</span>';
                            }

                            $out[] = '<span class="period-' . $period . '">';

                            $out[] = '<span class="hours-open">' . $value['open' . $period_array] . '</span>';

                            if (!empty($value['close' . $period_array])) {
                                $out[] = '<span class="hours-separator">&ndash;</span>';
                                $out[] = '<span class="hours-close">' . $value['close' . $period_array] . '</span>';
                            }

                            $out[] = '</span>';
                        }
                    }
                }

                $out[] = '</li>';
            }

            $out[] = '</ul><!-- .operating-hours -->';
        }

        if (!empty($notes)) {
            $out[] = '<div class="hours-notes">' . $notes . '</div><!-- .hours-notes -->';
        }

        if (empty($days) && empty($notes)) {
            $out[] = '<!-- No results from Hours plugin -->';
        }

        $out[] = $after_widget;

        echo implode("\n", $out);
    }
}
