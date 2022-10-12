<?php

namespace BCLibCoop\SiteManager;

class CoopLocationMap extends AbstractSiteManagerPage
{
    public static $slug = 'coop-location-map';
    public static $page_title = 'Location Map Setup';
    public static $menu_title = 'Location Map Setup';

    protected $position = 2;

    public static function getMapData()
    {
        $address = get_option('coop-ci-info');

        $data = get_option('_' . static::$slug . '_geodata', [
            'zoom' => 14,
            'width' => 300,
            'height' => 300,
        ]);

        // Just get numbers. Width, height, and zoom should all be ints
        array_walk($data, function (&$data_item) {
            $data_item = filter_var($data_item, FILTER_SANITIZE_NUMBER_INT);
        });

        if (!empty($address)) {
            $full_address = sprintf(
                '%s %s %s %s',
                $address['address'],
                $address['city'],
                $address['prov'],
                $address['pcode']
            );
        } else {
            $full_address = '';
        }
        $address_encoded = urlencode($full_address);

        $gmaps_url = add_query_arg(
            [
                'key' => GMAPSAPIKEY,
                'q' => $address_encoded,
                'zoom' => $data['zoom'],
            ],
            'https://www.google.com/maps/embed/v1/place'
        );

        return [
            'data' => $data,
            'full_address' => $full_address,
            'gmaps_url' => $gmaps_url,
        ];
    }

    public function saveChangeCallback()
    {
        // Check the nonce field, if it doesn't verify report error and stop
        if (
            ! isset($_POST['_wpnonce'])
            || ! wp_verify_nonce($_POST['_wpnonce'], static::$slug . '_submit')
        ) {
            wp_die('Sorry, there was an error handling your form submission.');
        }

        $data = [
            'zoom' => sanitize_text_field($_POST['zoom']),
            'width' => sanitize_text_field($_POST['map-width']),
            'height' => sanitize_text_field($_POST['map-height']),
        ];

        // Just get numbers. Width, height, and zoom should all be ints
        array_walk($data, function (&$data_item) {
            $data_item = filter_var($data_item, FILTER_SANITIZE_NUMBER_INT);
        });

        update_option('_' . static::$slug . '_geodata', $data);

        wp_redirect(admin_url('admin.php?page=' . static::$slug));
        exit;
    }
}
