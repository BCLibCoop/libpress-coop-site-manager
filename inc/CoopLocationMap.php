<?php

namespace BCLibCoop\SiteManager;

class CoopLocationMap extends AbstractSiteManagerPage
{
    public static $slug = 'coop-location-map';
    public static $page_title = 'Location Map Setup';
    public static $menu_title = 'Location Map Setup';

    protected $position = 2;

    public $address;

    public function adminSettingsPageContent()
    {
        // fetch the address data from the contact information page (from the coop-site-manager plugin)
        $this->address = get_option('coop-ci-info');

        $out = [];
        // auto lookup current address
        if (empty($this->address)) {
            $out[] = '<p>Please enter the library\'s address in the <a href="'
                     . admin_url('admin.php?page=' . ContactInfo::$slug) . '">Contact Information page</a> '
                     . 'and then return here to set up the location map.';
        } else {
            $data = get_option('_' . static::$slug . '_geodata', [
                'zoom' => 14,
                'width' => 300,
                'height' => 300,
            ]);

            // Just get numbers. Width, height, and zoom should all be ints
            array_walk($data, function (&$data_item) {
                $data_item = filter_var($data_item, FILTER_SANITIZE_NUMBER_INT);
            });

            $full_address = sprintf(
                '%s %s %s %s',
                $this->address['address'],
                $this->address['city'],
                $this->address['prov'],
                $this->address['pcode']
            );
            $address_encoded = urlencode($full_address);

            $gmaps_url = add_query_arg(
                [
                    'key' => GMAPSAPIKEY,
                    'q' => $address_encoded,
                    'zoom' => $data['zoom'],
                ],
                'https://www.google.com/maps/embed/v1/place'
            );

            $out[] = '<p>To change the library\'s address please edit the <a href="'
                     . admin_url('admin.php?page=' . ContactInfo::$slug) . '">Contact Information page</a>.</p>';
            $out[] = '<p>Library Address:</p>';
            $out[] = '<p id="library-address">' . $full_address . '</p>';

            $out[] = '<table class="form-table">';
            $out[] = '<tbody>';

            $out[] = '<tr>';
            $out[] = '<th scope="row">';
            $out[] = '<label for="zoom">Magnification</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="zoom" name="zoom" class="narrow-text" value="' . $data['zoom'] . '">';
            $out[] = '<p class="description">Recommended between 12 and 16. Default: 14</p>';
            $out[] = '</td>';
            $out[] = '</tr>';


            $out[] = '<tr>';
            $out[] = '<th scope="row">';
            $out[] = '<label for="map-width">Map Width</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="map-width" name="map-width" class="narrow-text" value="'
                     . $data['width'] . '">';
            $out[] = '<p class="description">Map width in pixels (don\'t include \'px\'). Default: 300</p>';
            $out[] = '</td>';
            $out[] = '</tr>';


            $out[] = '<tr>';
            $out[] = '<th scope="row">';
            $out[] = '<label for="map-height">Map Height</label>';
            $out[] = '</th>';
            $out[] = '<td>';
            $out[] = '<input type="text" id="map-height" name="map-height" class="narrow-text" value="'
                     . $data['height'] . '">';
            $out[] = '<p class="description">Map height in pixels (don\'t include \'px\'). Default: 300</p>';
            $out[] = '</td>';
            $out[] = '</tr>';

            $out[] = '</tbody>';
            $out[] = '</table>';

            $out[] = '<iframe';
            $out[] = '  id="coop-location-map-preview"';
            $out[] = '  width="' . $data['width'] . '"';
            $out[] = '  height="' . $data['height'] . '"';
            $out[] = '  style="border:0"';
            $out[] = '  src="' . $gmaps_url . '"';
            $out[] = ' allowfullscreen >';
            $out[] = '</iframe>';
        }

        return $out;
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
