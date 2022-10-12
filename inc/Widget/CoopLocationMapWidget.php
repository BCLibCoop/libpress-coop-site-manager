<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopLocationMap;

class CoopLocationMapWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopLocationMap::class;
        $this->slug = $this->adminPage::$slug . '-widget';
        $this->name = 'Location Map';
        $this->options = ['classname' => 'CoopLocationMap_coop_location_map_widget'];

        parent::__construct();
    }
}
