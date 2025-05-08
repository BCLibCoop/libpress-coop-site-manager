<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopMyAccount;

class CoopMyAccountWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopMyAccount::class;
        $this->slug = $this->adminPage::$slug . '-widget';
        $this->name = 'My Account';
        $this->options = [
            'classname' => 'CoopMyAccount_coop_my_account_widget',
            'description' => 'Shows a link with the text and target entered in the Site Manager',
        ];

        parent::__construct();
    }
}
