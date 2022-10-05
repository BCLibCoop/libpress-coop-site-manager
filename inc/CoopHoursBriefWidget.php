<?php

namespace BCLibCoop\SiteManager;

use function pll__;

class CoopHoursBriefWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'brief-hours-widget',
            'Brief Hours',
            ['classname' => 'CoopHours_brief_hours_widget']
        );
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
        admin_url('admin.php?page=' . CoopHours::$slug) . '">Site Manager</a></p>';
        return 'noform';
    }

    public function widget($args, $instance)
    {
        extract($args);

        $out = [];
        $days = get_option('coop-hours-days');

        $out[] = $before_widget;

        if (!empty($days)) {
            $out[] = '<ul class="operating-hours">';

            foreach (CoopHours::DAYS as $index => $day) {
                $dayname = $day['full'];
                $dayshort = substr($dayname, 0, 3);

                $out[] = '<li class="hours-day ' . $dayname . '"><span class="hours-dow">'
                         . (function_exists('pll__') ? pll__($dayshort, 'coop-hours') : $dayshort) . '</span>';

                $today = $days[$day['short']];
                $tomorrow = isset(CoopHours::DAYS[$index + 1]) ? $days[CoopHours::DAYS[$index + 1]['short']] : [];

                $today['notopen'] = filter_var(
                    $today['notopen'] ?? false,
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                );
                $tomorrow['notopen'] = filter_var(
                    $tomorrow['notopen'] ?? false,
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                );

                // Are the hours for today the same as tomorrow's hours ?
                if (
                    empty($tomorrow)
                    || !($today['notopen'] && $tomorrow['notopen'])
                    && !empty(array_diff_assoc($today, $tomorrow))
                ) {
                    // Only print hours if they are different than the previous day
                    if ($today['notopen']) {
                        $out[] = '<span class="hours-notopen">'
                                 . (function_exists('pll__') ? pll__('Closed', "coop-hours") : 'Closed')
                                 . '</span>';
                    } else {
                        foreach ([1, 2] as $period) {
                            $period_array = $period > 1 ? "_$period" : '';

                            if (!empty($today['open' . $period_array])) {
                                if ($period > 1) {
                                    $out[] = '<span class="period-separator">&amp;</span>';
                                }

                                $out[] = '<span class="period-' . $period . '">';

                                $out[] = '<span class="hours-open">' . $today['open' . $period_array] . '</span>';

                                if (!empty($today['close' . $period_array])) {
                                    $out[] = '<span class="hours-separator">&ndash;</span>';
                                    $out[] = '<span class="hours-close">' . $today['close' . $period_array] . '</span>';
                                }

                                $out[] = '</span>';
                            }
                        }
                    }
                }

                $out[] = '</li>';
            }

            $out[] = '</ul><!-- .operating-hours -->';
        } else {
            $out[] = '<!-- No results from Hours plugin -->';
        }

        $out[] = $after_widget;

        echo implode("\n", $out);
    }
}
