<?php

/**
 * Coop Site Manager - JSON-LD Data
 *
 * Adds JSON-LD markup to the header, pulling from Site Manager contact info
 * and hours information
 *
 * PHP Version 7
 *
 * @package           Coop Site Manager - JSON-LD Data
 * @author            Sam Edwards <sam.edwards@bc.libraries.coop>
 * @copyright         2021 BC Libraries Cooperative
 * @license           GPL-2.0-or-later
 */

namespace BCLibCoop\SiteManager;

class LibPressSchema
{
    public $json;

    public function __construct()
    {
        add_action('wp_head', [&$this, 'schemaOutput']);
    }

    /**
     * JSON Encodes and outputs the schema data in the <head>
     *
     * @return void
     */
    public function schemaOutput()
    {
        $this->json = $this->generateSchema();

        echo '<script type="application/ld+json">' . json_encode($this->json, JSON_UNESCAPED_UNICODE) . "</script>\n";
    }

    /**
     * Collects and formats schema data
     *
     * @return array
     */
    private function generateSchema()
    {
        $info = get_option('coop-ci-info', []);
        $days = get_option('coop-hours-days', []);
        // $hours_note = get_option('coop-hours-notes', '');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Library',
            '@id' => get_bloginfo('url'),
            'name' => get_bloginfo('name'),
            'url' => get_bloginfo('url'),
        ];

        if (!empty($info)) {
            $info = array_filter($info, 'trim');

            if (!empty($info['email'])) {
                $schema['email'] = $info['email'];
            }

            if (!empty($info['phone'])) {
                $schema['telephone'] = $info['phone'];
            }

            if (!empty($info['fax'])) {
                $schema['faxNumber'] = $info['fax'];
            }

            // Only include address info in the contact info widget is active
            // Most federartions have this turned off
            if (is_active_widget(false, false, 'coop-site-manager-widget')) {
                if (!empty($info['city']) && !empty($info['prov'])) {
                    $schema['address'] = [
                        '@type' => 'PostalAddress',
                        'contactType' => 'Mailing address',
                        'addressCountry' => 'Canada',
                        'addressLocality' => $info['city'],
                        'addressRegion' => $info['prov'],
                    ];

                    if (!empty($info['pcode'])) {
                        $schema['address']['postalCode'] = $info['pcode'];
                    }

                    if (!empty($info['pcode'])) {
                        $schema['address']['postalCode'] = $info['pcode'];
                    }

                    if (!empty($info['address'])) {
                        $schema['address']['streetAddress'] = $info['address'];

                        if (!empty($info['address2'])) {
                            $schema['address']['streetAddress'] .= ', ' . $info['address2'];
                        }
                    }
                }
            }
        }

        // If we have hours and one of the widgets is active, include hours in the schema data
        // Again, check for widgets is to not output old/incorrect hours for a federation that
        // doesn't really have hours, or lists multiple library hours manually
        if (
            !empty($days)
            && (is_active_widget(false, false, 'hours-widget') || is_active_widget(false, false, 'brief-hours-widget'))
        ) {
            $schema['openingHoursSpecification'] = [];

            foreach ($days as $day => $hours) {
                if (! filter_var($hours['notopen'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) {
                    foreach (['', '_2'] as $suffix) {
                        // Take a stab at converting these arbitrary text fields to a full time
                        foreach (['open', 'close'] as $hour) {
                            $$hour = $hours[$hour . $suffix];

                            // Don't mess with "noon" or if there is already a meridiem
                            if (stripos($$hour, 'noon') === false || !preg_match('/[ap]m/i', $$hour)) {
                                $meridiem = 'PM';

                                if (preg_match('/(\d+)/', $$hour, $this_hour)) {
                                    $this_hour = (int) $this_hour[1];

                                    // Conservative guess that libraries won't be open past 9pm
                                    if ($this_hour >= 9 && $this_hour <= 11) {
                                        $meridiem = 'AM';
                                    }
                                }

                                $$hour = preg_replace('/\D*$/', $meridiem, $$hour, 1);
                            }

                            // Do strtotime now so we can check if it worked before adding the day
                            $$hour = strtotime($$hour);
                        }

                        if ($open && $close) {
                            $schema['openingHoursSpecification'][] = [
                                '@type' => 'OpeningHoursSpecification',
                                'dayOfWeek' => 'https://schema.org/' . date('l', strtotime($day)),
                                'opens' => date('H:i:s', $open),
                                'closes' => date('H:i:s', $close),
                            ];
                        }
                    }
                }
            }
        }

        return $schema;
    }
}
