<?php

defined('ABSPATH') || die(1);

// Check if polylang is available and if so get correct info for configured language
if (function_exists('pll_languages_list')) {
    $link_text = stripslashes(get_option(static::$slug . get_locale() . '-label-text'));
    $link_uri = get_option(static::$slug . get_locale() . '-uri');
} else {
    $link_text = stripslashes(get_option(static::$slug . '-label-text'));
    $link_uri = get_option(static::$slug . '-uri');
}
?>
<a class="coop-media-link overdrive-link" href="<?= esc_url($link_uri) ?>"><?= $link_text ?></a>
