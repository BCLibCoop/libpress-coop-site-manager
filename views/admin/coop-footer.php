<p class="description">You may use <code>{{year}}</code> to show the current year.</p>

<table class="form-table">
    <?php foreach ($this->languages as $curlang) : ?>
        <?php
        $option = implode('-', array_filter([static::$slug, $curlang->locale, 'footer-text']));
        $text = stripslashes(get_option($option));
        ?>

        <tr valign="top">
            <th scope="row">
                <label for="<?= $option ?>"><?= $curlang->name ?> Footer Text:</label>
            </th>
            <td>
                <input type="text" id="<?= $option ?>" name="<?= $option ?>"
                    value="<?= esc_attr($text) ?>" class="large-text">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
