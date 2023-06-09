<?php

use BCLibCoop\SiteManager\CoopLocationMap;

extract($args);
extract(CoopLocationMap::getMapData());
?>

<?= $before_widget; ?>

<?php if (!empty($full_address)) : ?>
    <?php if ($data['width'] > 0 && $data['height'] > 0) : ?>
        <iframe width="<?= esc_attr($data['width']) ?>" height="<?= esc_attr($data['height']) ?>" style="border:0" src="<?= esc_attr($gmaps_url) ?>" allowfullscreen>
        </iframe>
    <?php else : ?>
        <!-- Map iframe set to 0x0, not displaying -->
    <?php endif; ?>
<?php else : ?>
    <!-- No Address set on Contact Info page of Site Manager -->
<?php endif; ?>

<?= $after_widget; ?>
