<?php

namespace BCLibCoop\SiteManager\Widget;

use BCLibCoop\SiteManager\CoopLocationMap;

class CoopLocationOSMWidget extends AbstractCoopWidget
{
    public function __construct()
    {
        $this->adminPage = CoopLocationMap::class;
        $this->slug = 'coop-location-osm-widget';
        $this->name = 'Location Map';
        $this->options = [
            'classname' => 'CoopLocationMap_coop_location_map_widget',
            'description' => 'Shows a map of the library location entered in the Site Manager',
        ];

        $this->registerAssets();

        parent::__construct();
    }

    public function registerAssets()
    {
        wp_register_style(
            'maplibre-gl',
            plugins_url('css/maplibre-gl.css', SITEMANAGER_PLUGIN_FILE),
            [],
            filemtime(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . 'css/maplibre-gl.css')
        );

        wp_register_script_module(
            '@coop-site-manager/maplibregl',
            plugins_url('js/maplibre-gl/maplibre-gl.mjs', SITEMANAGER_PLUGIN_FILE),
            [],
            filemtime(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . 'js/maplibre-gl/maplibre-gl.mjs')
        );

        wp_register_script_module(
            '@coop-site-manager/map',
            plugins_url('js/maps.js', SITEMANAGER_PLUGIN_FILE),
            ['@coop-site-manager/maplibregl'],
            filemtime(plugin_dir_path(SITEMANAGER_PLUGIN_FILE) . 'js/maps.js')
        );

        // Provide values to script
        add_filter(
            'script_module_data_@coop-site-manager/map',
            function (array $data) {
                return [...$data, ...CoopLocationMap::getMapData()];
            }
        );
    }

    public function widget($args, $instance)
    {
        wp_enqueue_style('maplibre-gl');
        wp_enqueue_script_module('@coop-site-manager/map');

        parent::widget($args, $instance);
    }
}
