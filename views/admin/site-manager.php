<?php

$info = get_option('coop-ci-info', array_fill_keys(array_keys(static::$fields), ''));
?>

<p>Contact info used on the front page of the site</p>

<table class="form-table">
    <?php foreach (static::$fields as $field_key => $field_name) : ?>
        <tr valign="top">
            <th scope="row">
                <label for="coop-ci-<?= $field_key ?>"><?= $field_name ?>:</label>
            </th>
            <td>
                <input type="text" id="coop-ci-<?= $field_key ?>" name="<?= $field_key ?>"
                    class="coop-ci regular-text" value="<?= $info[$field_key] ?>">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
