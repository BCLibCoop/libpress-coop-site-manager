<?php

$days = get_option('coop-hours-days', []);
$notes = stripslashes(get_option('coop-hours-notes', ''));
$show_all = get_option('coop-hours-show-all', false);
$full_names = get_option('coop-hours-notes-full-names', false);
?>

<table class="form-table hours-table">

    <colgroup>
        <col>
        <col span="4" class="hours">
        <col class="notopen">
    </colgroup>

    <tr>
        <td></td>
        <th scope="col">Open</th>
        <th scope="col">Close</th>
        <th scope="col">Re-open</th>
        <th scope="col">Re-close</th>
        <th scope="col">Not Open</th>
    </tr>

    <?php foreach ($this::DAYS as $day) : ?>
        <tr>
            <th scope="row"><?= $day['full'] ?>:</th>

            <?php foreach (['open', 'close', 'open_2', 'close_2'] as $input) : ?>
                <?php
                $id = $day['short'] . '_' . $input;
                $notopen = filter_var(
                    $days[$day['short']]['notopen'] ?? false,
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                );
                ?>

                <td>
                    <?= sprintf(
                        '<input type="text" size="8" id="%1$s" name="%1$s" value="%2$s">',
                        $id,
                        $days[$day['short']][$input] ?? ''
                    ); ?>
                </td>
            <?php endforeach; ?>

            <td>
                <?= sprintf(
                    '<input type="checkbox" id="%1$s" name="%1$s" value="true" %2$s>',
                    $day['short'] . '_notopen',
                    checked($notopen, true, false)
                ); ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <th>Notes</th>
        <td colspan="4">
            <textarea id="notes" name="notes"><?= $notes ?></textarea>
        </td>
        <td></td>
    </tr>
</table>

<h2 class="title">Brief Hours Widget</h2>

<table class="form-table">
    <tr>
        <th scope="row">Show Full Names</th>
        <td>
            <label for="full_names">
                <input type="checkbox" id="full_names" name="coop-hours-full-names" value="1" <?php checked($full_names) ?>>
                Show full names of days rather than abbreviate versions
            </label>
        </td>
    </tr>

    <tr>
        <th scope="row">Show Days Separately</th>
        <td>
            <label for="show_all">
                <input type="checkbox" id="show_all" name="coop-hours-show-all" value="1" <?php checked($show_all) ?>>
                Don't group consecutive days that have the same hours together
            </label>
        </td>
    </tr>
</table>
