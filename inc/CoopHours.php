<?php

namespace BCLibCoop\SiteManager;

class CoopHours extends AbstractSiteManagerPage
{
    public static $slug = 'hours-setup';
    public static $page_title = 'Hours of Operation';
    public static $menu_title = 'Hours of Operation';

    protected $position = 1;

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

    public function __construct()
    {
        parent::__construct();

        add_filter('option_sidebars_widgets', [$this, 'legacySidebarConfig']);
        add_filter('option_widget_hours-widget', [$this, 'legacyWidgetInstance']);
        add_filter('option_widget_brief-hours-widget', [$this, 'legacyWidgetInstance']);
    }

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

    /**
     * Widget previously registered as a single widget, add an instance ID
     * so they continue to function correctly
     */
    public function legacySidebarConfig($sidebars)
    {
        foreach ($sidebars as &$sidebar_widgets) {
            if (is_array($sidebar_widgets)) {
                foreach ($sidebar_widgets as &$widget) {
                    if (
                        in_array($widget, ['hours-widget', 'brief-hours-widget'])
                        && ! preg_match('/-\d$/', $widget)
                    ) {
                        $widget = $widget . '-1';
                        break;
                    }
                }
            }
        }

        return $sidebars;
    }

    public function widgetsInit()
    {
        parent::widgetsInit();

        register_widget(static::class . 'BriefWidget');
    }

    public function adminSettingsPageContent()
    {
        $days = get_option('coop-hours-days', []);
        $notes = get_option('coop-hours-notes', '');

        $out = [];

        $out[] = '<table class="form-table hours-table">';

        $out[] = '<colgroup>';
        $out[] = '<col>';
        $out[] = '<col span="4" class="hours">';
        $out[] = '<col class="notopen">';
        $out[] = '</colgroup>';

        $out[] = '<tr>';
        $out[] = '<td></td>';
        $out[] = '<th scope="col">Open</th>';
        $out[] = '<th scope="col">Close</th>';
        $out[] = '<th scope="col">Re-open</th>';
        $out[] = '<th scope="col">Re-close</th>';
        $out[] = '<th scope="col">Not Open</th>';
        $out[] = '</tr>';

        foreach (self::DAYS as $day) {
            $out[] = '<tr>';
            $out[] = '<th scope="row">' . $day['full'] . ':</th>';

            foreach (['open', 'close', 'open_2', 'close_2'] as $input) {
                $id = $day['short'] . '_' . $input;
                $notopen = filter_var(
                    $days[$day['short']]['notopen'] ?? false,
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                );

                $out[] = '<td>';
                $out[] = sprintf(
                    '<input type="text" size="8" id="%1$s" name="%1$s" value="%2$s">',
                    $id,
                    $days[$day['short']][$input] ?? ''
                );
                $out[] = '</td>';
            }

            $out[] = '<td>';
            $out[] = sprintf(
                '<input type="checkbox" id="%1$s" name="%1$s" value="true" %2$s>',
                $day['short'] . '_notopen',
                checked($notopen, true, false)
            );
            $out[] = '</td>';
            $out[] = '</tr>';
        }

        $out[] = '<tr>';
        $out[] = '<th>Notes</th>';
        $out[] = '<td colspan="4"><textarea id="notes" name="notes">' . $notes . '</textarea></td>';
        $out[] = '<td></td>';
        $out[] = '</tr>';

        $out[] = '</table>';

        return $out;
    }

    public function saveChangeCallback()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            ! isset($_POST['_wpnonce'])
            || ! wp_verify_nonce($_POST['_wpnonce'], static::$slug . '_submit')
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
}
