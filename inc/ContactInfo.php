<?php

namespace BCLibCoop\SiteManager;

use function pll_register_string;

class ContactInfo extends AbstractSiteManagerPage
{
    public static $slug = 'site-manager'; // Back compat stuff
    public static $page_title = 'Contact Information';
    public static $menu_title = 'Contact Information';

    protected $widgets = [
        'coop-site-manager-widget' => Widget\ContactInfoWidget::class,
    ];

    public static $fields = [
        'heading' => 'Heading',
        'email' => 'Email',
        'phone' => 'Phone',
        'fax' => 'Fax',
        'address' => 'Address 1',
        'address2' => 'Address 2',
        'city' => 'City/Town',
        'prov' => 'Province',
        'pcode' => 'Postal Code',
    ];

    public function init()
    {
        parent::init();

        // Register strings for multilingual support
        if (function_exists('pll_register_string')) {
            pll_register_string('Email Us', 'Email Us', 'coop-site-manager');
            pll_register_string('Phone', 'Phone', 'coop-site-manager');
            pll_register_string('Fax', 'Fax', 'coop-site-manager');
        }
    }

    public function saveChangeCallback()
    {
        parent::saveChangeCallback();

        $info = [];

        foreach (static::$fields as $field_key => $field_name) {
            $info[$field_key] = !empty($_POST[$field_key]) ? sanitize_text_field(stripslashes($_POST[$field_key])) : '';
        }

        update_option('coop-ci-info', $info);

        wp_redirect(admin_url('admin.php?page=' . CoopSiteManager::$slug));
        exit;
    }
}
