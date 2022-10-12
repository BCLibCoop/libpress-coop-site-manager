<?php

namespace BCLibCoop\SiteManager;

class CoopHours extends AbstractSiteManagerPage
{
    public static $slug = 'hours-setup';
    public static $page_title = 'Hours of Operation';
    public static $menu_title = 'Hours of Operation';

    protected $position = 1;
    protected $widgets = [
        'hours-widget' => Widget\CoopHoursWidget::class,
        'brief-hours-widget' => Widget\CoopHoursBriefWidget::class,
    ];

    public const DAYS = [
        [
            'short' => 'mon',
            'full' => 'Monday',
        ],
        [
            'short' => 'tue',
            'full' => 'Tuesday',
        ],
        [
            'short' => 'wed',
            'full' => 'Wednesday',
        ],
        [
            'short' => 'thu',
            'full' => 'Thursday',
        ],
        [
            'short' => 'fri',
            'full' => 'Friday',
        ],
        [
            'short' => 'sat',
            'full' => 'Saturday',
        ],
        [
            'short' => 'sun',
            'full' => 'Sunday',
        ],
    ];

    public function init()
    {
        parent::init();

        // Register strings for translation via polylang
        if (function_exists('pll_register_string')) {
            pll_register_string('Widget Title', 'Hours of Operation', 'coop-hours');
            pll_register_string('Abreviated Monday', 'Mon', 'coop-hours');
            pll_register_string('Abreviated Tuesday', 'Tue', 'coop-hours');
            pll_register_string('Abreviated Wednesday', 'Wed', 'coop-hours');
            pll_register_string('Abreviated Thursday', 'Thu', 'coop-hours');
            pll_register_string('Abreviated Friday', 'Fri', 'coop-hours');
            pll_register_string('Abreviated Saturday', 'Sat', 'coop-hours');
            pll_register_string('Abreviated Sunday', 'Sun', 'coop-hours');
            pll_register_string('Closed', 'Closed', 'coop-hours');
            pll_register_string('Hours', 'Hours:', 'coop-hours');
        }
    }

    public function saveChangeCallback()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            !isset($_POST['_wpnonce'])
            || !wp_verify_nonce($_POST['_wpnonce'], static::$slug . '_submit')
        ) {
            wp_die('Sorry, there was an error handling your form submission.');
        }

        $days = [];

        foreach (self::DAYS as $day_arr) {
            $day = $day_arr['short'];

            $days[$day] = [
                'open' => sanitize_text_field($_POST[$day . '_open']),
                'close' => sanitize_text_field($_POST[$day . '_close']),
                'open_2' => sanitize_text_field($_POST[$day . '_open_2']),
                'close_2' => sanitize_text_field($_POST[$day . '_close_2']),
                'notopen' => filter_var(
                    $_POST[$day . '_notopen'] ?? false,
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                )
            ];
        }

        $notes = sanitize_textarea_field($_POST['notes']);

        update_option('coop-hours-days', $days);
        update_option('coop-hours-notes', $notes);

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }

    /**
     * Sanitize and clean data, deals with old unsanizitzed DB data and ensures
     * that 'notopen' is a bool
     */
    public static function getDaysData()
    {
        $raw_days = array_filter(get_option('coop-hours-days', []));
        $days = [];

        if (!empty($raw_days)) {
            foreach (self::DAYS as $day_arr) {
                $day = $day_arr['short'];

                if (!empty($raw_days[$day])) {
                    $days[$day] = [
                        'open' => sanitize_text_field($raw_days[$day]['open'] ?? ''),
                        'close' => sanitize_text_field($raw_days[$day]['close'] ?? ''),
                        'open_2' => sanitize_text_field($raw_days[$day]['open_2'] ?? ''),
                        'close_2' => sanitize_text_field($raw_days[$day]['close_2'] ?? ''),
                        'notopen' => filter_var(
                            $raw_days[$day]['notopen'] ?? false,
                            FILTER_VALIDATE_BOOL,
                            FILTER_NULL_ON_FAILURE
                        )
                    ];
                }
            }
        }

        return $days;
    }
}
