<table class="form-table">
    <?php foreach ($this->languages as $curlang) : ?>
        <?php
        $link_text = stripslashes(get_option(static::$slug . $curlang->locale . '-label-text'));
        $link_uri = get_option(static::$slug . $curlang->locale . '-uri');
        $prefix = static::$slug . $curlang->locale;
        ?>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $prefix ?>-uri"><?= $curlang->name ?> Search Box Link URL:</label>
            </th>
            <td>
                <input type="text" id="<?= $prefix ?>-uri" name="<?= $prefix ?>-uri"
                    value="<?= $link_uri ?>" class="regular-text">
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $prefix ?>-label-text"><?= $curlang->name ?> Search Box Link Label:</label>
            </th>
            <td>
                <input type="text" id="<?= $prefix ?>-label-text" name="<?= $prefix ?>-label-text"
                    value="<?= $link_text ?>" class="regular-text">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
