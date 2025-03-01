<?php

use BCLibCoop\SiteManager\CoopHours;

extract($args);

$days = wp_unslash(CoopHours::getDaysData());
$notes = stripslashes(get_option('coop-hours-notes'));
?>

<?= $before_widget ?>

<?= $before_title ?>

<?php _e('Hours of Operation', 'pll_string') ?>

<?= $after_title ?>

<?php if (!empty($days)) : ?>
    <dl class="operating-hours">

        <?php foreach ($days as $key => $value) : ?>
            <div class="row g-0 hours-day <?= esc_attr($key) ?>">
                <dt class="col">
                    <span class="hours-dow"><?php _e(ucfirst($key), 'pll_string'); ?></span>
                </dt>
                <dd class="col">
                    <?php if ($value['notopen']) : ?>
                        <span class="hours-notopen"><?php _e('Closed', 'pll_string'); ?></span>
                    <?php else : ?>
                        <?php foreach ([1, 2] as $period) : ?>
                            <?php $period_array = $period > 1 ? "_$period" : ''; ?>

                            <?php if (!empty($value['open' . $period_array])) : ?>
                                <?php if ($period > 1) : ?>
                                    <span class="period-separator">&amp;</span>
                                <?php endif; ?>

                                <span class="period-<?= esc_attr($period) ?>">
                                    <span class="hours-open"><?= $value['open' . $period_array] ?></span>

                                    <?php if (!empty($value['close' . $period_array])) : ?>
                                        <span class="hours-separator">&ndash;</span>
                                        <span class="hours-close"><?= $value['close' . $period_array] ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </dd>
            </div>

        <?php endforeach; ?>

    </dl><!-- .operating-hours -->
<?php endif; ?>

<?php if (!empty($notes)) : ?>
    <div class="hours-notes"><?= $notes ?></div><!-- .hours-notes -->
<?php endif; ?>

<?php if (empty($days) && empty($notes)) : ?>
    <!-- No results from Hours plugin -->
<?php endif; ?>

<?= $after_widget ?>
