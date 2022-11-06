<?php
//----------------------------------------
// Jailbreak guardbans (teambans) administration through SourceBans.
// Made by Retr0#1799 for the Wonderland.TF community :)
//----------------------------------------
global $userbank, $theme;

if (!defined("IN_SB")) {
    echo "You should not be here. Only follow links!";
    die();
}

new AdminTabs([
    ['name' => 'Add a Teamban', 'permission' => ADMIN_OWNER|ADMIN_ADD_BAN]
], $userbank, $theme);
?>