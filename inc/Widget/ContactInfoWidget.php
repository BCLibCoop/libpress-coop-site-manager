<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\ContactInfo;

class ContactInfoWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = ContactInfo::class;
        $this->slug = 'coop-site-manager-widget';
        $this->name = 'Contact Information';
        $this->options = [
            'classname' => 'CoopSiteManager_coop_site_manager_ci_widget',
            'description' => 'Displays content information as ented in the Site Manager',
        ];

        parent::__construct();
    }
}
