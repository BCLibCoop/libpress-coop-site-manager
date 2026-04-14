<?php

namespace BCLibCoop\SiteManager;

class CoopFooter extends AbstractSiteManagerPage
{
    public static $slug = 'coop-footer';
    public static $page_title = 'Footer Text';
    public static $menu_title = 'Footer Text';

    protected $widgets = [
        'footer-text-widget' => Widget\FooterTextWidget::class,
        'terms-link-widget' => Widget\TermsWidget::class, // No connection to SM page, just convenient
        'privacy-link-widget' => Widget\PrivacyPolicyWidget::class, // No connection to SM page, just convenient
    ];

    public function init()
    {
        parent::init();

        // Set default options if not already set
        foreach ($this->languages as $curlang) {
            $option = implode('-', array_filter([static::$slug, $curlang->locale, 'footer-text']));
            add_option($option, '&copy; {{year}} British Columbia Libraries Cooperative, #320, 185-911 Yates Street, Victoria BC V8V 4Y9');
        }
    }

    public function saveChangeCallback()
    {
        parent::saveChangeCallback();

        foreach ($this->languages as $curlang) {
            $option = implode('-', array_filter([static::$slug, $curlang->locale, 'footer-text']));
            $text = sanitize_text_field(stripslashes($_POST[$option]));

            update_option($option, $text);
        }

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }
}
