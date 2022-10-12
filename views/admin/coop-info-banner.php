<table class="form-table">
    <?php foreach ($this->languages as $curlang) : ?>
        <?php
        $link_text = stripslashes(get_option(static::$slug . $curlang->locale . '-label-text'));
        $link_uri = get_option(static::$slug . $curlang->locale . '-uri');
        $prefix = static::$slug . $curlang->locale;
        ?>

        <tr>
            <th><?php // TODO ?></th>
            <td>Enabled checkbox, limited WYWISYG, expiration date (default to 1 week?)</td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $prefix ?>-uri"><?= $curlang->name ?> Account Login URI:</label>
            </th>
            <td>
                <input type="text" id="<?= $prefix ?>-uri" name="<?= $prefix ?>-uri"
                    value="<?= $link_uri ?>" class="regular-text">
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $prefix ?>-label-text"><?= $curlang->name ?> Account Login Label:</label>
            </th>
            <td>
                <input type="text" id="<?= $prefix ?>-label-text" name="<?= $prefix ?>-label-text"
                    value="<?= $link_text ?>" class="regular-text">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
