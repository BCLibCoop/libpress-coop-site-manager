<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Coop Site Manager
 *
 * Provides most functionality in the "Site Manager" section
 *
 * PHP Version 7
 *
 * @package           Coop Site Manager
 * @author            Erik Stainsby <eric.stainsby@roaringsky.ca>
 * @author            Ben Holt <ben.holt@bc.libraries.coop>
 * @author            Jonathan Schatz <jonathan.schatz@bc.libraries.coop>
 * @author            Sam Edwards <sam.edwards@bc.libraries.coop>
 * @copyright         2013-2022 BC Libraries Cooperative
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Coop Site Manager
 * Description:       LibPress-specific Site Manager functionality
 * Version:           3.9.1
 * Network:           true
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            BC Libraries Cooperative
 * Author URI:        https://bc.libraries.coop
 * Text Domain:       coop-site-manager
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace BCLibCoop\SiteManager;

// No direct access
defined('ABSPATH') || die(-1);
define('SITEMANAGER_PLUGIN_FILE', __FILE__);

/**
 * Require Composer autoloader if installed on it's own
 */
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

/**
 * Hooker earlier to ensure we can register the Site Manager menu first
 */
add_action('plugins_loaded', function () {
    $classes = [
        CoopSiteManager::class,
        ContactInfo:: class,
        CoopFooter:: class,
        CoopHours::class,
        CoopLocationMap::class,
        CoopMediaLink::class,
        CoopMyAccount::class,
        InfoBanner::class,
        LinkReport::class,
        LibPressSchema::class,
        NetworkSitkaLibraries::class,
        NetworkThemeSettings::class,
    ];

    // Conditionally load TEC Class
    if (class_exists('Tribe__Events__Main')) {
        $classes[] = EventCalendarBeforeAfter::class;
    }

    foreach ($classes as $class) {
        new $class();
    }
}, 5);
