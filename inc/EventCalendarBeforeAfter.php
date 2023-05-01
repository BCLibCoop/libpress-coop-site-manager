<?php

namespace BCLibCoop\SiteManager;

use StoutLogic\AcfBuilder\FieldsBuilder;

class EventCalendarBeforeAfter extends AbstractSiteManagerPage
{
    public static $slug = 'coop-ec-before-after';
    public static $page_title = 'Event Calendar Header/Footer Content';
    public static $menu_title = 'Event Calendar Content';

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

    protected $locations = [
        [
            'name' => 'Single Event',
            'slug' => 'single_event',
            'rule' => [],
        ],
        [
            'name' => 'Community Submission Page',
            'slug' => 'community_submission',
            'rule' => [],
        ],
        [
            'name' => 'Global Calendar',
            'slug' => 'global',
            'rule' => [],
        ],
        [
            'name' => 'Category Page',
            'slug' => 'category',
            'rule' => [],
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

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueStylesScripts']);

        // $before = apply_filters( 'tribe_events_before_html', $before, $view );
        // $before = apply_filters( 'tribe_events_views_v2_view_before_events_html', $before, $view );
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
        $tec_content = new FieldsBuilder(static::$slug, [
            'title' => static::$page_title,
            'style' => 'seamless',
        ]);

        $group = $tec_content
            ->addGroup('before', [
                'label' => 'Before',
                'instructions' => '',
                'required' => 0,
                'layout' => 'block',
                // 'sub_fields' => [],
            ]);

        foreach ($this->locations as $index => $tab) {
            $group
                ->addTab('tab_' . $tab['slug'], [
                    'label' => $tab['name'],
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => [],
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'placement' => 'left',
                    'endpoint' => 0 ? 1 : 0,
                ]);

                $group
                    ->addWysiwyg('html_' . $tab['slug'], [
                        'label' => $tab['name'] . ' Content',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => [],
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                    ]);
        }

        $group
            ->endGroup();

        $tec_content
            ->addMessage('message_field', 'message', [
                'label' => 'Regular TEC Content',
                'message' => 'Content Goes Here',
                'new_lines' => 'wpautop', // 'wpautop', 'br', '' no formatting
                'esc_html' => 0,
            ]);

        $group = $tec_content
            ->addGroup('after', [
                'label' => 'After',
                'instructions' => '',
                'required' => 0,
                'layout' => 'block',
            ]);

            foreach ($this->locations as $index => $tab) {
                $group
                    ->addTab('tab_' . $tab['slug'], [
                        'label' => $tab['name'] . ' Content',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => [],
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                        'placement' => 'left',
                        'endpoint' => $index === 0 ? 1 : 0,
                    ]);

                $group
                    ->addWysiwyg('html_' . $tab['slug'], [
                        'label' => 'Content',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => [],
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                    ]);
            }

            $group
                ->endGroup();

        // foreach ($this->languages as $curlang) {
        //     $name = 'html' . (empty($curlang->locale) ? '' : '_' . strtolower($curlang->locale));

        //     $tec_content->addWysiwyg($name, [
        //         'name' => static::$slug . '_' . $name,
        //         'label' => $curlang->name . ' Info Banner Content',
        //         'instructions' => 'The content of the informational banner. '
        //             . 'Only simple HTML styling is allowed, and no linebreaks.',
        //         'toolbar' => 'basic',
        //         'media_upload' => 0,
        //     ]);
        // }

        $tec_content->setLocation('options_page', '==', static::$slug);

        // $built = $tec_content->build();
        $built = [];

        // error_log(var_export($built, true));

        acf_add_local_field_group($built);
    }

    /**
     * noop as we're using ACF
     */
    public function saveChangeCallback()
    {
    }
}
