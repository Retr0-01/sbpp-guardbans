<?php
//----------------------------------------
// TF2 Jailbreak Redux guardbans (teambans) administration through SourceBans.
// Originally made for the Wonderland.TF community.
//
// Made by Giannis "Retr0" Kepas
//----------------------------------------

use SteamID\SteamID;

global $theme;

if (!defined("IN_SB")) {
    echo "You should not be here. Only follow links!";
    die();
}

new AdminTabs([], $userbank, $theme);

if ($_GET['key'] != $_SESSION['banlist_postkey']) {
    echo '<script>ShowBox("Error", "Possible hacking attempt (URL Key mismatch)!", "red", "index.php?p=admin&c=teambans");</script>';
    PageDie();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<script>ShowBox("Error", "No ban id specified. Please only follow links!", "red", "index.php?p=admin&c=teambans");</script>';
    PageDie();
}

$res = $GLOBALS['db']->GetRow("SELECT * FROM " . TEAMBANS_DB_NAME . " WHERE timestamp = {$_GET['timestamp']}");

if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_EDIT_ALL_BANS) && (!$userbank->HasAccess(ADMIN_EDIT_OWN_BANS) && $res[8] != $userbank->GetAid()) && (!$userbank->HasAccess(ADMIN_EDIT_GROUP_BANS) && $res->fields['gid'] != $userbank->GetProperty('gid'))) {
    echo '<script>ShowBox("Error", "You don\'t have access to this!", "red", "index.php?p=admin&c=teambans");</script>';
    PageDie();
}

isset($_GET["page"]) ? $pagelink = "&page=" . $_GET["page"] : $pagelink = "";

$errorScript = "";

if (isset($_POST['name'])) {
    $_POST['steam'] = SteamID::toSteam2(trim($_POST['steam']));
    $_POST['name'] = htmlspecialchars_decode($_POST['name'], ENT_QUOTES);

    // Form Validation
    $error = 0;
    // If they didn't type a steamid
    if (empty($_POST['steam'])) {
        $error++;
        $errorScript .= "$('steam.msg').innerHTML = 'You must type a Steam ID or Community ID';";
        $errorScript .= "$('steam.msg').setStyle('display', 'block');";
    } elseif (!SteamID::isValidID($_POST['steam'])) {
        $error++;
        $errorScript .= "$('steam.msg').innerHTML = 'Please enter a valid Steam ID or Community ID';";
        $errorScript .= "$('steam.msg').setStyle('display', 'block');";
    }

    // Didn't type a custom reason
    if ($_POST['listReason'] == "other" && empty($_POST['txtReason'])) {
        $error++;
        $errorScript .= "$('reason.msg').innerHTML = 'You must type a reason';";
        $errorScript .= "$('reason.msg').setStyle('display', 'block');";
    }

    if ($error == 0) {
        $chk = $GLOBALS['db']->GetRow("SELECT count(id) AS count FROM " . TEAMBANS_DB_NAME . " WHERE offender_id = ? AND (length = 0 ) AND id != ?", array(
            $_POST['steam'],
            (int) $_GET['id']
        ));

        if ((int) $chk[0] > 0) {
            $error++;
            $errorScript .= "$('steam.msg').innerHTML = 'This SteamID is already banned';";
            $errorScript .= "$('steam.msg').setStyle('display', 'block');";
        } else {
            // Check if player is immune
            $admchk = $userbank->GetAllAdmins();
            foreach ($admchk as $admin) {
                if ($admin['authid'] == $_POST['steam'] && $userbank->GetProperty('srv_immunity') < $admin['srv_immunity']) {
                    $error++;
                    $errorScript .= "$('steam.msg').innerHTML = 'Admin " . $admin['user'] . " is immune';";
                    $errorScript .= "$('steam.msg').setStyle('display', 'block');";
                    break;
                }
            }
        }
    }
   
    $reason = $_POST['listReason'] == "other" ? $_POST['txtReason'] : $_POST['listReason'];
    // I hate this.
    $_POST['steam'] = preg_replace('/\[U:1[^.]/', '', substr(SteamID::toSteam3($_POST['steam']), 0, -1));

    if (!$_POST['banlength']) {
        $_POST['banlength'] = 0;
    } else {
        $_POST['banlength'] = (int) $_POST['banlength'] * 60;
    }

    // Show the new values in the form
    $res['offender_name'] = $_POST['name'];
    $res['offender_id'] = $_POST['steam'];
    $res['length'] = $_POST['banlength'];
    $reason = htmlspecialchars_decode($reason, ENT_QUOTES);
    $res['reason'] = $reason;

    // Only process if there are still no errors
    if ($error == 0) {
        $lengthrev = $GLOBALS['db']->Execute("SELECT length, id FROM " . TEAMBANS_DB_NAME . " WHERE id = '" . (int) $_GET['id'] . "'");

        // Do not reset the timeleft if the banlength wasn't changed.
        if ($_POST['banlength'] != $lengthrev->fields['length']) {
            Log::add("m", "Teamban Length Edited", "Teamban length for (" . $lengthrev->fields['id'] . ") has been updated, before: " . $lengthrev->fields['length'] . ", now: " . $_POST['banlength']);
        
            $edit = $GLOBALS['db']->Execute(
                "UPDATE " . TEAMBANS_DB_NAME . " SET
                offender_name = ?,
                offender_id = ?,
                length = ?,
                timeleft = ?,
                reason = ?
                WHERE id = ?",
                array(
                    $_POST['name'],
                    $_POST['steam'],
                    $_POST['banlength'],
                    $_POST['banlength'],
                    $reason,
                    (int) $_GET['id']
                )
            );
        } else {
            $edit = $GLOBALS['db']->Execute(
                "UPDATE " . TEAMBANS_DB_NAME . " SET
                offender_name = ?,
                offender_id = ?,
                reason = ?
                WHERE id = ?",
                array(
                    $_POST['name'],
                    $_POST['steam'],
                    $reason,
                    (int) $_GET['id']
                )
            );
        }
        echo '<script>ShowBox("Ban updated", "The ban has been updated successfully", "green", "index.php?p=teambans' . $pagelink . '");</script>';
    }
}

if (!$res) {
    echo '<script>ShowBox("Error", "There was an error getting details. Maybe the teamban has been deleted?", "red", "index.php?p=banlist' . $pagelink . '");</script>';
}

$theme->assign('ban_name', $res['offender_name']);
$theme->assign('ban_reason', $res['reason']);
$theme->assign('ban_authid',  SteamID::toSteam3('[U:1:'. $res['offender_id'] . ']'));
$theme->assign('customreason', (Config::getBool('bans.customreasons')) ? unserialize(Config::get('bans.customreasons')) : false);

$theme->left_delimiter  = "-{";
$theme->right_delimiter = "}-";
$theme->display('page_admin_edit_teamban.tpl');
$theme->left_delimiter  = "{";
$theme->right_delimiter = "}";
?>

<script type="text/javascript">window.addEvent('domready', function(){
<?=$errorScript?>
});
function changeReason(szListValue)
{
    $('dreason').style.display = (szListValue == "other" ? "block" : "none");
}
selectLengthReasonOnly('<?=(int) $res['length']?>', '<?=addslashes($res['reason'])?>');
</script>
