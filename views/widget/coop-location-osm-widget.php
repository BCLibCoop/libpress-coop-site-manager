<?php

use BCLibCoop\SiteManager\CoopLocationMap;

defined('ABSPATH') || die(1);

extract($args);
extract(CoopLocationMap::getMapData());
?>

<?= $before_widget; ?>

<?php if (!empty($full_address)) : ?>
    <?php if ($data['width'] > 0 && $data['height'] > 0) : ?>
        <div id="map" style="height: <?= esc_attr($data['height']) ?>px; width: <?= esc_attr($data['width']) ?>px;"></div>
    <?php else : ?>
        <!-- Map set to 0x0, not displaying -->
    <?php endif; ?>
<?php else : ?>
    <!-- No Address set on Contact Info page of Site Manager -->
<?php endif; ?>

<?= $after_widget; ?>
