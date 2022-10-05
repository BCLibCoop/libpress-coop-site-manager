<?php

namespace BCLibCoop\SiteManager;

class CoopLocationMapWidget extends \WP_Widget
{
    public $address;

    public function __construct()
    {
        parent::__construct(
            CoopLocationMap::$slug . '-widget',
            'Location Map',
            ['classname' => 'CoopLocationMap_coop_location_map_widget']
        );

        // fetch the address data from the contact information page (from the coop-site-manager plugin)
        $this->address = get_option('coop-ci-info');
    }

    public function form($instance)
    {
        echo '<p class="no-options-widget">This widget is configured via the <a href="' .
        admin_url('admin.php?page=' . CoopLocationMap::$slug) . '">Site Manager</a></p>';
        return 'noform';
    }

    public function widget($args, $instance)
    {
        extract($args);
        /* widget-declaration:
        id
        name
        before_widget
        after_widget
        before_title
        after_title
        */

        $out = [];

        $out[] = $before_widget;

        if (!empty($this->address)) {
            $data = get_option('_' . CoopLocationMap::$slug . '_geodata', [
                'zoom' => 14,
                'width' => 300,
                'height' => 300,
            ]);

            // Just get numbers. Width, height, and zoom should all be ints
            array_walk($data, function (&$data_item) {
                $data_item = filter_var($data_item, FILTER_SANITIZE_NUMBER_INT);
            });

            if ($data['width'] > 0 && $data['height'] > 0) {
                $address_encoded = urlencode($this->address['address'] . ' ' . $this->address['city'] . ' '
                                   . $this->address['prov'] . ' ' . $this->address['pcode']);
                $gmaps_url = 'https://www.google.com/maps/embed/v1/place?key=' . GMAPSAPIKEY . '&q=' . $address_encoded
                             . '&zoom=' . $data['zoom'];

                $out[] = '<iframe';
                $out[] = "  width=\"" . $data['width'] . "\"";
                $out[] = "  height=\"" . $data['height'] . "\"";
                $out[] = '  style="border:0"';
                $out[] = '  src="' . $gmaps_url . '"';
                $out[] = ' allowfullscreen >';
                $out[] = '</iframe>';
            } else {
                $out[] = '<!-- Map iframe set to 0x0, not displaying -->';
            }
        } else {
            $out[] = '<!-- No Address set on Contact Info page of Site Manager -->';
        }

        $out[] = $after_widget;

        echo implode("\n", $out);
    }
}
