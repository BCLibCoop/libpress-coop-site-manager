<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopHours;

class CoopHoursBriefWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopHours::class;
        $this->slug = 'brief-hours-widget';
        $this->name = 'Brief Hours';
        $this->options = ['classname' => 'CoopHours_brief_hours_widget'];

        parent::__construct();
    }
}
