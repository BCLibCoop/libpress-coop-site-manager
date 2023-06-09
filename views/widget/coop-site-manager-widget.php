<?php

extract($args);

$info = wp_unslash(get_option('coop-ci-info', []));
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
            <?= $info['phone'] ?><br />
        <?php endif; ?>

        <?php if (!empty($info['fax'])) : ?>
            <strong><?php _e('Fax', 'pll_string') ?></strong>
            <?= $info['fax'] ?><br />
        <?php endif; ?>

        <?php if (!empty($info['address'])) : ?>
            <?= $info['address'] ?><br />

            <?php if (!empty($info['address2'])) : ?>
                <?= $info['address2'] ?><br />
            <?php endif; ?>

            <?= $info['city'] . ' ' . $info['prov'] . ' ' . $info['pcode'] ?><br />
        <?php endif; ?>

    </div><!-- .coop-contact-info -->
<?php else : ?>
    <!-- no results from ContactInfo plugin -->
<?php endif; ?>

<?= $after_widget; ?>
