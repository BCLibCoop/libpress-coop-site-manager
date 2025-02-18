<?php

namespace BCLibCoop\SiteManager;

use StoutLogic\AcfBuilder\FieldsBuilder;
use Tribe__Events__Main;

class EventCalendarBeforeAfter extends AbstractSiteManagerPage
{
    public static $slug = 'coop-ec-before-after';
    public static $page_title = 'Event Calendar Header/Footer Content';
    public static $menu_title = 'Event Calendar Content';
    public static $option_name = 'libpress_tec_content';

    protected $capability = 'manage_options'; // Admin only for now
    protected $position = 5;
    protected $admin_script_deps = ['jquery', 'acf-input'];

    protected $locations = [
        'single_event' => [
            'label' => 'Single Event',
            'conditionals' => [
                'operator' => 'AND',
                'tribe_is_event',
                'is_single'
            ],
        ],
        'category' => [
            'label' => 'Category Page(s)',
            'conditionals' => [[self::class, 'isCalendarCat']],
        ],
        'community_submission' => [
            'label' => 'Community Submission Page',
            'conditionals' => [[self::class, 'isCommunityPage']],
        ],
        'global' => [
            'label' => 'Global Calendar',
            'conditionals' => [[self::class, 'isMainCalendar']],
        ],
    ];

    protected $content = [];

    /**
     * Override parent init, as we're using ACF here instead of a more "manual"
     * admin menu page
     */
    public function init()
    {
        add_action('acf/init', [$this, 'registerOptionsPage']);
        add_action('acf/include_fields', [$this, 'registerAcfFields']);
        add_filter('acf/fields/flexible_content/layout_title/name=' . static::$option_name, [$this, 'acfFlexTitle']);

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueStylesScripts']);

        // Using option filter instead of output filter so we don't have to
        // worry about getting within the right output <div>
        // add_filter('tribe_events_before_html', [$this, 'insertContent'], 10);
        // add_filter('tribe_events_after_html', [$this, 'insertContent'], 10);

        add_filter('tribe_get_option', [$this, 'insertContent'], 100, 2);

        // Populate content array
        if (function_exists('get_field')) {
            $this->content = get_field(static::$option_name, 'options');
        }
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
        $options_page = new FieldsBuilder('options', [
            'title' => static::$page_title,
            'style' => 'seamless',
        ]);

        $options_page->setLocation('options_page', '==', static::$slug);

        $content_fields = new FieldsBuilder('content', [
            'label' => 'Calendar Page Content',
        ]);

        $content_fields
            ->addText('description', [
                'label' => 'Content Description',
                'instructions' => 'Only used to organize your content on this page, not shown to users',
                'required' => 1,
                'wrapper' => ['width' => 50],
            ])
            ->addRepeater('locations', [
                'label' => 'Display Locations',
                'instructions' => 'Select the calendar page types where this content should appear',
                'required' => 1,
                'layout' => 'table',
                'min' => 1,
                'button_label' => 'Add Display Location',
                'wrapper' => ['width' => 50],
            ])
                ->addSelect('location', [
                    'required' => 1,
                    'choices' => array_combine(
                        array_keys($this->locations),
                        array_column($this->locations, 'label')
                    )
                ])
                ->addTaxonomy('categories', [
                    'taxonomy' => Tribe__Events__Main::TAXONOMY,
                    'required' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'value',
                    'field_type' => 'multi_select',
                    'allow_null' => 0,
                    'multiple' => 0,
                ])
                    ->conditional('location', '==', 'category')
            ->endRepeater();
        ;

        // Tag/WYSIWYG for each language
        foreach ($this->languages as $curlang) {
            $suffix = empty($curlang->locale) ? '' : '_' . strtolower($curlang->locale);
            $label_suffix = empty($curlang->name) ? '' : ' (' . $curlang->name . ')';

            $content_fields
                ->addTab("before{$suffix}", [
                    'label' => "Before{$label_suffix}",
                ])
                ->addWysiwyg("before{$suffix}", [
                    'label' => '',
                    'instructions' => 'This content is displayed at the top of the selected pages, before the title',
                    'toolbar' => 'basic',
                    'delay' => 1,
                ])
                ->addTab("after{$suffix}", [
                    'label' => "After{$label_suffix}",
                ])
                ->addWysiwyg("after{$suffix}", [
                    'label' => '',
                    'instructions' => 'The content is displayed at the bottom of the selected pages, after all other '
                        . 'calendar content but before the footer menu',
                    'toolbar' => 'basic',
                    'delay' => 1,
                ])
            ;
        }

        $flexible = new FieldsBuilder(static::$option_name . '_flexible');
        $flexible
            ->addFlexibleContent(static::$option_name, [
                'label' => 'Calendar Page Content',
                'instructions' => 'Create one or more groups of content to display before/after automatically '
                    . 'generated calendar pages',
            ])
                ->addLayout($content_fields, [
                    'layout' => 'block',
                ])
        ;

        $options_page
            ->addFields($flexible)
        ;

        acf_add_local_field_group($options_page->build());
    }

    /**
     * noop as we're using ACF
     */
    public function saveChangeCallback()
    {
    }

    /**
     * Include layout description in header
     */
    public function acfFlexTitle($title)
    {
        if ($description = get_sub_field('description')) {
            $title .= ' - <b>' . esc_html($description) . '</b>';
        }

        return $title;
    }

    /**
     * Check an array of callables, with optional conditional.
     *
     * Borrowed in part from TEC
     */
    protected function shouldInsert($conditionals, $arg)
    {
        $tests = [];

        $conditional_operator = $conditionals['operator'] ?? 'OR';

        foreach ($conditionals as $key => $conditional) {
            // Skip the operator key
            if ($key === 'operator') {
                continue;
            }

            $tests[] = (bool) call_user_func($conditional, $arg);
        }

        if ($conditional_operator === 'OR') {
            return in_array(true, $tests);
        }

        return !in_array(false, $tests);
    }

    /**
     * Callback to check if this is a community page
     *
     * We're runing in the context of WP_Router's execute() method, which is hooked
     * to parse_request, which is all happening before the global $wp_query is set
     * up, and thus tribe_is_community_edit_event_page() won't work, so we're using
     * this alternate function which hopefully won't go away...
     */
    protected function isCommunityPage()
    {
        return tribe(\TEC\Events_Community\Custom_Tables\V1\Assets::class)->is_edit_route();
    }

    /**
     * Conditional check for if we are looking at the main calendar
     */
    protected function isMainCalendar()
    {
        return (
            tribe_is_day()
            || tribe_is_week()
            || tribe_is_month()
            || tribe_is_photo()
            || tribe_is_map()
            || tribe_is_list_view()
        )
        && ! is_tax()
        && ! is_single();
    }

    /**
     * Conditional check to see if we are on any of the specified categories
     */
    protected function isCalendarCat($categories)
    {
        $category_checks = [];

        foreach ($categories as $category) {
            $category_checks[] = is_tax(Tribe__Events__Main::TAXONOMY, $category);
        }

        return in_array(true, $category_checks);
    }

    /**
     * Hooked to insert our content into the default before/after event HTML
     */
    public function insertContent($value, $optionName)
    {
        if (
            !is_admin()
            && in_array($optionName, ['tribeEventsBeforeHTML', 'tribeEventsAfterHTML'])
            && !empty($this->content)
        ) {
            $position = $optionName === 'tribeEventsAfterHTML' ? 'after' : 'before';
            $lang_suffix = function_exists('pll_languages_list') ? '_' . strtolower(get_locale()) : '';

            foreach ($this->content as $check_content) {
                foreach ($check_content['locations'] as $location) {
                    $location_def = $this->locations[$location['location']] ?? [];

                    if ($this->shouldInsert($location_def['conditionals'], $location['categories'])) {
                        $value .= $check_content[$position . $lang_suffix] ?? '';
                    }
                }
            }
        }

        return $value;
    }
}
