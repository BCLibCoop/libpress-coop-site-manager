<?php

use BCLibCoop\SiteManager\CoopMyAccount;

extract($args);

// Check if polylang is available and if so get correct info for configured language
if (function_exists('pll_languages_list')) {
    $link_text = stripslashes(get_option(CoopMyAccount::$slug . get_locale() . '-label-text'));
    $link_uri = get_option(CoopMyAccount::$slug . get_locale() . '-uri');
} else {
    $link_text = stripslashes(get_option(CoopMyAccount::$slug . '-label-text'));
    $link_uri = get_option(CoopMyAccount::$slug . '-uri');
}
?>

<?= $before_widget ?>

<?= $before_title ?>
<a href="<?= $link_uri ?>"><?= $link_text ?></a>
<?= $after_title ?>

<?= $after_widget ?>
