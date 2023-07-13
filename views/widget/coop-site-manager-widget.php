<?php

extract($args);

$info = wp_unslash(get_option('coop-ci-info', []));

$maps_url = 'https://www.google.com/maps/dir/'; // ?api=1
$map_desination = "{$info['address']}, {$info['city']}, {$info['prov']} {$info['pcode']}";
$map_desination = rawurlencode($map_desination);
$maps_url = add_query_arg(
    [
        'api' => 1,
        'destination' => $map_desination,
    ],
    $maps_url
);
?>

<?= $before_widget; ?>

<?php if (!empty($info)) : ?>
    <?= $before_title . $info['heading'] . $after_title; ?>

    <div class="coop-contact-info">

        <?php if (!empty($info['email'])) : ?>
            <a href="mailto:<?= esc_attr($info['email']) ?>"><?php _e('Email Us', 'pll_string') ?></a><br />
        <?php endif; ?>

        <?php if (!empty($info['phone'])) : ?>
            <strong><?php _e('Phone', 'pll_string') ?></strong>
            <a href="tel:<?= preg_replace('/[^0-9]/', '', $info['phone']) ?>"><?= $info['phone'] ?></a><br />
        <?php endif; ?>

        <?php if (!empty($info['fax'])) : ?>
            <strong><?php _e('Fax', 'pll_string') ?></strong>
            <a href="tel:<?= preg_replace('/[^0-9]/', '', $info['fax']) ?>"><?= $info['fax'] ?></a><br />
        <?php endif; ?>

        <?php if (!empty($info['address'])) : ?>
            <a href="<?= $maps_url ?>" target="_blank" rel="noreferrer">
                <?= $info['address'] ?><br />

                <?php if (!empty($info['address2'])) : ?>
                    <?= $info['address2'] ?><br />
                <?php endif; ?>

                <?= $info['city'] . ' ' . $info['prov'] . ' ' . $info['pcode'] ?>
            </a>
            <br />
        <?php endif; ?>

    </div><!-- .coop-contact-info -->
<?php else : ?>
    <!-- no results from ContactInfo plugin -->
<?php endif; ?>

<?= $after_widget; ?>
