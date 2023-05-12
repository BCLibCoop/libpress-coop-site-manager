<table class="form-table">
    <?php foreach ($this->languages as $curlang) : ?>
        <?php
        $option = implode('-', array_filter([static::$slug, $curlang->locale, 'footer-text']));
        $text = get_option($option);
        ?>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $option ?>"><?= $curlang->name ?> Footer Text:</label>
            </th>
            <td>
                <input type="text" id="<?= $option ?>" name="<?= $option ?>"
                    value="<?= $text ?>" class="large-text">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
