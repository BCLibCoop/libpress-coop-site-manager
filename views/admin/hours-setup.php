<?php

$days = get_option('coop-hours-days', []);
$notes = get_option('coop-hours-notes', '');
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
