<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopHours;

class CoopHoursWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopHours::class;
        $this->slug = 'hours-widget';
        $this->name = 'Hours of Operation';
        $this->options = ['classname' => 'CoopHours_hours_widget'];

        parent::__construct();
    }
}
