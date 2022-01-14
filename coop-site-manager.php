<?php

/**
 * Coop Site Manager
 *
 * Creates the "Site Manager" admin menu page, as well as a Contact Info page
 * and a contact info widget
 *
 * PHP Version 7
 *
 * @package           Coop Site Manager
 * @author            Erik Stainsby <eric.stainsby@roaringsky.ca>
 * @author            Ben Holt <ben.holt@bc.libraries.coop>
 * @author            Sam Edwards <sam.edwards@bc.libraries.coop>
 * @copyright         2013-2022 BC Libraries Cooperative
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Coop Site Manager
 * Description:       This is the common location for the other Coop Plugins to reside.
 * Version:           2.1.0
 * Network:           true
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            BC Libraries Cooperative
 * Author URI:        https://bc.libraries.coop
 * Text Domain:       coop-site-manager
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace BCLibCoop;

// No direct access
defined('ABSPATH') || die(-1);

add_action('plugins_loaded', function () {
    require_once 'inc/CoopSiteManager.php';
    require_once 'inc/LibPressSchema.php';
    require_once 'inc/NetworkSitkaLibraries.php';
    require_once 'inc/NetworkThemeSettings.php';

    new CoopSiteManager();
    new LibPressSchema();
    new NetworkSitkaLibraries();
    new NetworkThemeSettings();
});
