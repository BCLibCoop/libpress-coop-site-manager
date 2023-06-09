<?php

use BCLibCoop\SiteManager\ContactInfo;
use BCLibCoop\SiteManager\CoopLocationMap;

extract(CoopLocationMap::getMapData());
?>

<?php if (empty($full_address)) : ?>
    <p>
        Please enter the library's address in the
        <a href="<?= admin_url('admin.php?page=' . ContactInfo::$slug) ?>">Contact Information page</a>
        and then return here to set up the location map.
    </p>
    <?php return; ?>
<?php endif; ?>

<p>
    To change the library's address please edit the
    <a href="<?= admin_url('admin.php?page=' . ContactInfo::$slug) ?>">Contact Information page</a>.
</p>

<p>Library Address:</p>

<p id="library-address"><?= $full_address ?></p>

<table class="form-table">
    <tbody>

        <tr>
            <th scope="row">
                <label for="zoom">Magnification</label>
            </th>
            <td>
                <input type="text" id="zoom" name="zoom" class="narrow-text" value="<?= esc_attr($data['zoom']) ?>">
                <p class="description">Recommended between 12 and 16. Default: 14</p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="map-width">Map Width</label>
            </th>
            <td>
                <input type="text" id="map-width" name="map-width" class="narrow-text" value="<?= esc_attr($data['width']) ?>">
                <p class="description">Map width in pixels (don't include 'px'). Default: 300</p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="map-height">Map Height</label>
            </th>
            <td>
                <input type="text" id="map-height" name="map-height" class="narrow-text" value="<?= esc_attr($data['height']) ?>">
                <p class="description">Map height in pixels (don't include 'px'). Default: 300</p>
            </td>
        </tr>

    </tbody>
</table>

<iframe id="coop-location-map-preview" width="<?= esc_attr($data['width']) ?>" height="<?= esc_attr($data['height']) ?>"
    style="border:0" src="<?= esc_attr($gmaps_url) ?>" allowfullscreen></iframe>
