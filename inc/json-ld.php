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

namespace BCLibCoop;

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
            if (is_active_widget('coop-site-manager-widget')) {
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
        if (!empty($days) && (is_active_widget('hours-widget') || is_active_widget('brief-hours-widget'))) {
            $schema['openingHoursSpecification'] = [];

            foreach ($days as $day => $hours) {
                if ($hours['notopen'] !== 'true') {
                    foreach (['', '_2'] as $suffix) {
                        if (!empty($hours['open' . $suffix]) && !empty($hours['close' . $suffix])) {
                            // Take a stab at converting these arbitrary text fields to a full time
                            $open = $hours['open' . $suffix];
                            if (stripos($open, 'noon') === false) {
                                $meridiem = 'AM';
                                if (strpos($open, '12') === 0) {
                                    $meridiem = 'PM';
                                }

                                $open = preg_replace('/\D*$/', $meridiem, $open, 1);
                            }

                            $close = $hours['close' . $suffix];
                            if (stripos($close, 'noon') === false) {
                                $meridiem = 'PM';
                                if (strpos($close, '12') === 0) {
                                    $meridiem = 'AM';
                                }

                                $close = preg_replace('/\D*$/', $meridiem, $close, 1);
                            }

                            $schema['openingHoursSpecification'][] = [
                                '@type' => 'OpeningHoursSpecification',
                                'dayOfWeek' => 'https://schema.org/' . date('l', strtotime($day)),
                                'opens' => date('H:i:s', strtotime($open)),
                                'closes' => date('H:i:s', strtotime($close)),
                            ];
                        }
                    }
                }
            }
        }

        return $schema;
    }
}
