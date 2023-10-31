<?php

namespace BCLibCoop\SiteManager;

use StoutLogic\AcfBuilder\FieldsBuilder;

class InfoBanner extends AbstractSiteManagerPage
{
    public static $slug = 'coop-info-banner';
    public static $page_title = 'Info Banner';
    public static $menu_title = 'Info Banner';

    protected $position = 5;

    protected $allowed_tags = [
        'a'         => [
            'href' => true,
            'title' => true,
            'target' => true,
            'rel' => true,
        ],
        'b'         => [],
        'em'        => [],
        'i'         => [],
        's'         => [],
        'strike'    => [],
        'strong'    => [],
        'span'      => [
            'style' => true,
        ],
    ];

    /**
     * Override parent init, as we're using ACF here instead of a more "manual"
     * admin menu page
     */
    public function init()
    {
        add_action('acf/init', [$this, 'registerOptionsPage']);
        add_action('acf/include_fields', [$this, 'registerAcfFields']);

        // Add filter for multilingual fields if required
        foreach ($this->languages as $curlang) {
            $name = 'html' . (empty($curlang->locale) ? '' : '_' . $curlang->locale);
            $key = strtolower('field_' . static::$slug . '_' . $name);

            add_filter("acf/update_value/key={$key}", [$this, 'sanitizeHtml']);
        }

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueStylesScripts']);
    }

    /**
     * Should the info banner be shown?
     */
    public static function shouldShowBanner()
    {
        $start = get_field(static::$slug . '_start', 'options', false);
        $end = get_field(static::$slug . '_expires', 'options', false);

        $start_datetime = $start ? date_create_from_format('Y-m-d H:i:s', $start, wp_timezone()) : null;
        $end_datetime = $end ? date_create_from_format('Y-m-d H:i:s', $end, wp_timezone()) : null;
        $now_datetime = date_create('now', wp_timezone());

        return (
            get_field(static::$slug . '_enabled', 'options')
            && (
                (!$start_datetime && !$end_datetime)
                || ($start_datetime && $end_datetime
                    && $now_datetime >= $start_datetime && $now_datetime <= $end_datetime
                )
                || ($start_datetime && !$end_datetime && $now_datetime >= $start_datetime)
                || (!$start_datetime && $end_datetime && $now_datetime <= $end_datetime)
            )
        );
    }

    /**
     * Returns the content of the info banner
     */
    public static function infoBanner()
    {
        $key = static::$slug . '_html';

        if (function_exists('pll_languages_list')) {
            $key .= '_' . strtolower(get_locale());
        }

        the_field($key, 'options');
    }

    /**
     * Register the options page with ACF so it is a valid location for field
     * groups.
     */
    public function registerOptionsPage()
    {
        acf_add_options_page([
            'parent_slug' => CoopSiteManager::$slug,
            'menu_slug' => static::$slug,
            'page_title' => static::$page_title,
            'menu_title' => static::$menu_title,
            'capability' => $this->capability,
            'position' => $this->position,
            'autoload' => true,
            'update_button' => 'Save Changes',
            'updated_message' => static::$page_title . ' changes saved',
        ]);
    }

    /**
     * Register our ACF fields for this page
     */
    public function registerAcfFields()
    {
        $info_banner = new FieldsBuilder(static::$slug, [
            'title' => static::$page_title,
            'style' => 'seamless',
        ]);

        $info_banner
            ->addTrueFalse('enabled', [
                'name' => static::$slug . '_enabled',
                'label' => 'Enable Info Banner',
                'instructions' => 'Enable or disable the global information banner that '
                    . 'shows below the hours on all pages',
                'ui' => true,
                'ui_on_text' => 'Enable',
                'ui_off_text' => 'Disable',
            ])
            ->addMessage(
                'Time-Limited Banner',
                'Use the date pickers below to have the banner enabled only during a specific timeframe. '
                . '<br>The banner must still be set to <strong>Enable</strong> above.'
            )
            ->addDateTimePicker('start', [
                'name' => static::$slug . '_start',
                'label' => 'Start Date/Time',
                'instructions' => 'Earliest date at which the notice will show',
                'wrapper' => [
                    'width' => 50,
                ],
            ])
            ->addDateTimePicker('expires', [
                'name' => static::$slug . '_expires',
                'label' => 'End Date/Time',
                'instructions' => 'Date after which the notice will no longer be displayed',
                'wrapper' => [
                    'width' => 50,
                ],
            ]);

        foreach ($this->languages as $curlang) {
            $name = 'html' . (empty($curlang->locale) ? '' : '_' . strtolower($curlang->locale));

            $info_banner->addWysiwyg($name, [
                'name' => static::$slug . '_' . $name,
                'label' => $curlang->name . ' Info Banner Content',
                'instructions' => 'The content of the informational banner. '
                    . 'Only simple HTML styling is allowed, and no linebreaks.',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ]);
        }

        $info_banner->setLocation('options_page', '==', static::$slug);

        acf_add_local_field_group($info_banner->build());
    }

    /**
     * We've already set up the WYSIWYG field to not insert <p> tags and prefer
     * <br> tags, so we're going to just look for those and replace with a space
     * so as not to run words together if someone has inserted a linebreak
     */
    public function sanitizeHtml($value)
    {
        $value = preg_replace('#<\s*br\s*/?\s*>#i', ' ', $value);

        return wp_kses($value, $this->allowed_tags);
    }

    /**
     * noop as we're using ACF
     */
    public function saveChangeCallback()
    {
    }
}
