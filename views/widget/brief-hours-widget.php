<?php

use BCLibCoop\SiteManager\CoopHours;

extract($args);

$days = CoopHours::getDaysData();

$show_all = filter_var(
    get_option('coop-hours-show-all', false),
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
);

$full_names = filter_var(
    get_option('coop-hours-notes-full-names', false),
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
);
?>

<?= $before_widget ?>

<?php if (!empty($days)) : ?>
    <ul class="operating-hours">

        <?php foreach (CoopHours::DAYS as $index => $day) : ?>
            <li class="hours-day <?= $day['full'] ?>">
                <?php if ($full_names) : ?>
                    <span class="hours-dow">
                        <?php _e(ucfirst($day['full']), 'pll_string'); ?>
                    </span>
                <?php else : ?>
                    <abbr class="hours-dow" title="<?= esc_attr($day['full']) ?>">
                        <?php _e(ucfirst($day['short']), 'pll_string'); ?>
                    </abbr>
                <?php endif; ?>

                <?php
                if (
                    $show_all
                    || $index === array_key_last(CoopHours::DAYS)
                    || !($days[$day['short']]['notopen'] && $days[CoopHours::DAYS[$index + 1]['short']]['notopen'])
                    && !empty(array_diff_assoc($days[$day['short']], $days[CoopHours::DAYS[$index + 1]['short']]))
                ) : ?>
                    <?php if ($days[$day['short']]['notopen']) : ?>
                        <span class="hours-notopen"><?php _e('Closed', 'pll_string'); ?></span>
                    <?php else : ?>
                        <?php foreach ([1, 2] as $period) : ?>
                            <?php $period_array = $period > 1 ? "_$period" : ''; ?>

                            <?php if (!empty($days[$day['short']]['open' . $period_array])) : ?>
                                <?php if ($period > 1) : ?>
                                    <span class="period-separator">&amp;</span>
                                <?php endif; ?>

                                <span class="period-<?= $period ?>">
                                    <span class="hours-open"><?= $days[$day['short']]['open' . $period_array] ?></span>

                                    <?php if (!empty($days[$day['short']]['close' . $period_array])) : ?>
                                        <span class="hours-separator">&ndash;</span>
                                        <span class="hours-close">
                                            <?= $days[$day['short']]['close' . $period_array] ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>

            </li>
        <?php endforeach; ?>

    </ul><!-- .operating-hours -->
<?php else : ?>
    <!-- No results from Hours plugin -->
<?php endif; ?>

<?= $after_widget; ?>
