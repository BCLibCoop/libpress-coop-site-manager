<?php

/**
 * Functions/helpers to be present in the global namespace
 */

function coop_should_show_banner()
{
    return BCLibCoop\SiteManager\InfoBanner::shouldShowBanner();
}

function coop_info_banner()
{
    return BCLibCoop\SiteManager\InfoBanner::infoBanner();
}
