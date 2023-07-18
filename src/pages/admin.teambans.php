<?php
//----------------------------------------
// TF2 Jailbreak Redux guardbans (teambans) administration through SourceBans.
// Originally made for the Wonderland.TF community.
//
// Made by Giannis "Retr0" Kepas
//----------------------------------------

global $userbank, $theme;

if (!defined("IN_SB")) {
    echo "You should not be here. Only follow links!";
    die();
}

new AdminTabs([
    ['name' => 'Add a Teamban', 'permission' => ADMIN_OWNER|ADMIN_ADD_BAN]
], $userbank, $theme);

echo '<div id="admin-page-content">';
// Add Teamban
echo '<div class="tabcontent" id="Add a Teamban">';
$theme->assign('permission_addban', $userbank->HasAccess(ADMIN_OWNER | ADMIN_ADD_BAN));
$theme->assign('customreason', (Config::getBool('bans.customreasons')) ? unserialize(Config::get('bans.customreasons')) : false);
$theme->display('page_admin_teambans_add.tpl');
echo '</div>';
?>

<script type="text/javascript">
function changeReason(szListValue)
{
    $('dreason').style.display = (szListValue == "other" ? "block" : "none");
}

function ProcessTeamban()
{
    var err = 0;
    var reason = $('listReason')[$('listReason').selectedIndex].value;

    if (reason == "other") {
        reason = $('txtReason').value;
    }

    if (!$('nickname').value) {
        $('nick.msg').setHTML('You must enter the nickname of the person you are banning');
        $('nick.msg').setStyle('display', 'block');
        err++;
    } else {
        $('nick.msg').setHTML('');
        $('nick.msg').setStyle('display', 'none');
    }

    if (!$('steam').value.test(/(?:STEAM_[01]:[01]:\d+)|(?:\[U:1:\d+\])|(?:\d{17})/)) {
        $('steam.msg').setHTML('You must enter a valid STEAM ID or Community ID');
        $('steam.msg').setStyle('display', 'block');
        err++;
    } else {
        $('steam.msg').setHTML('');
        $('steam.msg').setStyle('display', 'none');
    }

    if (!reason) {
        $('reason.msg').setHTML('You must select or enter a reason for this ban.');
        $('reason.msg').setStyle('display', 'block');
        err++;
    } else {
        $('reason.msg').setHTML('');
        $('reason.msg').setStyle('display', 'none');
    }

    if (err) {
        return 0;
    }

    xajax_AddTeamban($('nickname').value,
                 $('steam').value,
                 $('banlength').value,
                 reason);
}
</script>
</div>
