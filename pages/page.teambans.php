<?php
//----------------------------------------
// Jailbreak guardbans (teambans) administration through SourceBans.
// Made by Retr0#1799 for the Wonderland.TF community :)
//----------------------------------------
use SteamID\SteamID;

global $theme;
global $userbank;

$BansPerPage = SB_BANS_PER_PAGE;
$page     = 1;
$pagelink = "";

if (!defined("IN_SB")) {
    echo "You should not be here. Only follow links!";
    die();
}

function setPostKey()
{
    if (isset($_SERVER['REMOTE_IP'])) {
        $_SESSION['banlist_postkey'] = md5($_SERVER['REMOTE_IP'] . time() . rand(0, 100000));
    } else {
        $_SESSION['banlist_postkey'] = md5(time() . rand(0, 100000));
    }
}
if (!isset($_SESSION['banlist_postkey']) || strlen($_SESSION['banlist_postkey']) < 4) {
    setPostKey();
}

if (isset($_GET['page']) && $_GET['page'] > 0) {
    $page     = intval($_GET['page']);
    $pagelink = "&page=" . $page;
}

//----------------------------------------
// Handle Teamban Unban Request
//----------------------------------------
if (isset($_GET['a']) && $_GET['a'] == "unban" && isset($_GET['id']))
{
    if ($_GET['key'] != $_SESSION['banlist_postkey']) {
        die("Possible hacking attempt (URL Key mismatch)");
    }
    
    if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_UNBAN)) {
        echo "<script>ShowBox('Error', 'You do not have access to this.', 'red', 'index.php?p=teambans$pagelink');</script>";
        PageDie();
    }

    $row = $GLOBALS['db']->GetRow("SELECT offender_name, offender_id FROM " . TEAMBANS_DB_NAME . " WHERE id = ? AND timeleft > 0", $_GET['id']);

    if (empty($row) || !$row) {
        echo "<script>ShowBox('Player Not Unguardbanned', 'The player's teamban was not removed, either they are already unbanned or this is not a valid teamban.', 'red', 'index.php?p=teambans$pagelink');</script>";
        PageDie();
    }

    $res = $GLOBALS['db']->Execute("UPDATE " . TEAMBANS_DB_NAME . " SET timeleft = 0 WHERE id=(?)", $_GET['id']);

    if ($res) {
        echo "<script>ShowBox('Teamban Removed', 'The guard ban for \'" . addslashes($row['offender_name']) . "\' has been removed from SourceBans.', 'green', 'index.php?p=teambans$pagelink');</script>";
        Log::add("m", "Teamban Removed", addslashes($row['offender_name']) . " has been unteambanned.");
    } else {
        echo "<script>ShowBox('Teamban Remove Failed', 'The guard ban for \'" . addslashes($row['offender_name']) . "\' had an error while being removed.', 'red', 'index.php?p=teambans$pagelink', true);</script>";
    }
}
//----------------------------------------
// Handle Teamban Delete Request
//----------------------------------------
else if (isset($_GET['a']) && $_GET['a'] == "delete")
{
    if ($_GET['key'] != $_SESSION['banlist_postkey']) {
        die("Possible hacking attempt (URL Key mismatch)");
    }
    
    if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_DELETE_BAN)) {
        echo "<script>ShowBox('Error', 'You do not have access to this.', 'red', 'index.php?p=teambans$pagelink');</script>";
        PageDie();
    }

    $row = $GLOBALS['db']->GetRow("SELECT offender_name, offender_id FROM " . TEAMBANS_DB_NAME . " WHERE id = ?", $_GET['id']);

    if (empty($row) || !$row) {
        echo "<script>ShowBox('Player Not Unguardbanned', 'The player was not un-guardbanned, either already unbanned or not a valid ban.', 'red', 'index.php?p=teambans$pagelink');</script>";
        PageDie();
    }

    $res = $GLOBALS['db']->Execute("DELETE FROM " . TEAMBANS_DB_NAME . " WHERE id = ?", $_GET['id']);

    if ($res) {
        echo "<script>ShowBox('Teamban Deleted', 'The guard ban for \'" . addslashes($row['offender_name']) . "\' has been deleted from SourceBans.', 'green', 'index.php?p=teambans$pagelink');</script>";
        Log::add("m", "Teamban Deleted", "Teamban for '" .  addslashes($row['offender_name']) . "' has been deleted.");
    } else {
        echo "<script>ShowBox('Teamban Delete Failed', 'The guard ban for \'" . addslashes($row['offender_name']) . "\' had an error while being deleted.', 'red', 'index.php?p=teambans$pagelink', true);</script>";
    }
}

$BansStart = intval(($page - 1) * $BansPerPage);
$BansEnd   = intval($BansStart + $BansPerPage);

// Hide inactive teambans.
if (isset($_GET["hideinactive"]) && $_GET["hideinactive"] == "true") {
    $_SESSION["hideinactive"] = true;
} 
elseif (isset($_GET["hideinactive"]) && $_GET["hideinactive"] == "false") {
    unset($_SESSION["hideinactive"]);
}

if (isset($_SESSION["hideinactive"])) {
    $hidetext      = "Show";
    $hideinactive  = " AND timeleft != 0";
    $hideinactiven = " WHERE timeleft != 0";
} else {
    $hidetext      = "Hide";
    $hideinactive  = "";
    $hideinactiven = "";
}

if (!isset($_GET['search'])) {
    $res = $GLOBALS['db']->Execute("SELECT * FROM " . TEAMBANS_DB_NAME . $hideinactiven . ' ORDER BY timestamp DESC ' . " LIMIT " . $BansPerPage);

    $res_count  = $GLOBALS['db']->Execute("SELECT count(id) FROM " . TEAMBANS_DB_NAME . $hideinactiven);
    $searchlink = "";
} else {
    // 
    // Guard bans search system.
    //
    // The normal banlist has two search 'modes', searchText and adv(anced)Search. searchText is executed through the top right search box
    // and is used for finding bans using any kind of data the user may have, nickname, steam id, ip, anything.
    // advSearch is executed through the advanced text collapse.
    //
    // In order to not have to alter the root sourcebans frontend we will have our own search system which will support the data a teamban
    // entry will have in the database, with that being offender name, steam id, duration and reason (and admin for admins only).
    $searchCriteria = array();
    $searchValue = trim($_GET['search']);
    $searchType = $_GET['searchType'];

    switch ($type) {
        case "name":
            $where   = "WHERE offender_name LIKE ?";
            $searchCriteria = array(
                "%$value%"
            );
            break;
        case "steamid":
            $where   = "WHERE offender_id = ?";
            $searchCriteria = array(
                $value
            );
            break;
        case "reason":
            $where   = "WHERE reason LIKE ?";
            $searchCriteria = array(
                "%$value%"
            );
            break;
        case "date":
            $date    = explode(",", $value);
            $time    = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
            $time2   = mktime(23, 59, 59, $date[1], $date[0], $date[2]);
            $where   = "WHERE timestamp > ? AND timestamp < ?";
            $searchCriteria = array(
                $time,
                $time2
            );
            break;
        case "length":
            $len         = explode(",", $value);
            $length_type = $len[0];
            $length      = $len[1] * 60;
            $where       = "WHERE length ";
            switch ($length_type) {
                case "e":
                    $where .= "=";
                    break;
                case "h":
                    $where .= ">";
                    break;
                case "l":
                    $where .= "<";
                    break;
                case "eh":
                    $where .= ">=";
                    break;
                case "el":
                    $where .= "<=";
                    break;
            }
            $where .= " ?";
            $searchCriteria = array(
                $length
            );
            break;
        case "timeLeft":
            $len         = explode(",", $value);
            $length_type = $len[0];
            $length      = $len[1] * 60;
            $where       = "WHERE timeleft ";
            switch ($length_type) {
                case "e":
                    $where .= "=";
                    break;
                case "h":
                    $where .= ">";
                    break;
                case "l":
                    $where .= "<";
                    break;
                case "eh":
                    $where .= ">=";
                    break;
                case "el":
                    $where .= "<=";
                    break;
            }
            $where .= " ?";
            $searchCriteria = array(
                $length
            );
            break;
        case "admin":
            if ($GLOBALS['config']['banlist.hideadminname'] && !$userbank->is_admin()) {
                $where   = "";
                $searchCriteria = array();
            } else {
                $where   = "WHERE admin_name=?";
                $searchCriteria = array(
                    $value
                );
            }
            break;
        default:
            $where              = "";
            $_GET['search']     = "";
            $_GET['searchType'] = "";
            $searchCriteria           = array();
            break;
    }

     // Make sure we got a "WHERE" clause there, if we add the hide inactive condition.
     if (empty($where) && isset($_SESSION["hideinactive"])) {
        $hideinactive = $hideinactiven;
    }

    $res = $GLOBALS['db']->Execute("SELECT * FROM " . TEAMBANS_DB_NAME . $where . $hideinactive . " ORDER BY timestamp DESC
    LIMIT ?,?", array_merge($advcrit, array(intval($BansStart), intval($BansPerPage))));

    $searchlink = "&search=" . $_GET['search'] . "&searchType=" . $_GET['searchType'];
}

$BanCount = $res_count->fields[0];
if ($BansEnd > $BanCount) {
    $BansEnd = $BanCount;
}
if (!$res) {
    echo "No Bans Found.";
    PageDie();
}

//----------------------------------------
// BAN ENTRY CONSTRUCTOR
// 
// Construct a ban entry by fetching its data, comments, etc.
//----------------------------------------
$view_comments = false;
$bans          = array();
while (!$res->EOF) {
    $data = array();

    $data['ban_id'] = $res->fields['id'];
    $data['ban_date'] = date(Config::get('config.dateformat'), strtotime($res->fields['timestamp']));
    $data['player'] = addslashes($res->fields['offender_name']);
    $data['reason'] = stripslashes($res->fields['reason']);
    // offender_id is the Steam3 auth id.
    $data['steamid'] = SteamID::toSteam2('[U:1:'. $res->fields['offender_id'] . ']');
    $data['steamid3'] = SteamID::toSteam3($data['steamid']);
    $data['communityid'] = SteamID::toSteam64($data['steamid']);

    if (Config::getBool('banlist.hideadminname') && !$userbank->is_admin()) {
        $data['admin'] = false;
    } else {
        $data['admin'] = stripslashes($res->fields['admin_name']);
    }

    $data['ban_length'] = $res->fields['length'] == 0 ? 'Permanent' : SecondsToString(intval($res->fields['length']));
    $data['ban_timeleft'] = $res->fields['timeleft'] == 0 ? '' : SecondsToString(intval($res->fields['timeleft'])) . " /";

    if ($res->fields['length'] == 0) {
        $data['expires']   = 'never';
        $data['class']     = "listtable_1_permanent";
        $data['ub_reason'] = "";
    }
    else {
        $data['expires'] = date(Config::get('config.dateformat'), strtotime($data['ban_date'] . '+' . $res->fields['length'] .' seconds'));
        $data['class']     = "listtable_1_banned";
        $data['ub_reason'] = "";
    }

    if (($res->fields['timeleft'] == '0' && $res->fields['timeleft'] < time()) && $res->fields['length'] !== $res->fields['timeleft']) {
        $data['class']    = "listtable_1_unbanned";
        $data['unbanned']  = true;
        $data['ub_reason'] = "(Expired)";
    }

    // Create admin links.
    $data['edit_link'] = CreateLinkR('<i class="fas fa-edit fa-lg"></i> Edit Details', "index.php?p=admin&c=teambans&o=edit" . $pagelink . "&id=" . $res->fields['id'] . "&key=" . $_SESSION['banlist_postkey']);
    $data['unban_link']  = CreateLinkR('<i class="fas fa-undo fa-lg"></i> Unban Teamban', "#", "", "_self", false, "UnbanTeamBan('" . $res->fields['id'] . "', '" . $_SESSION['banlist_postkey'] . "', '" . $pagelink . "', '" . $data['player'] . "', 1, false);return false;");
    $data['delete_link'] = CreateLinkR('<i class="fas fa-trash fa-lg"></i> Delete Teamban', "#", "", "_self", false, "RemoveTeamBan('" . $res->fields['id'] . "', '" . $_SESSION['banlist_postkey'] . "', '" . $pagelink . "', '" . $data['player'] . "', 0);return false;");

    // Show any previous teambans.
    $teamban_history = $GLOBALS['db']->GetAll("SELECT id FROM " . TEAMBANS_DB_NAME . " WHERE offender_id = (?)", $res->fields['offender_id']);
    if (sizeof($teamban_history) > 1) {
        $data['prevoff_link'] = sizeof($teamban_history) . " " . CreateLinkR("(search)", "index.php?p=teambans&search=" . $data['steamid'] . "&Submit");
    } else {
        $data['prevoff_link'] = "No previous bans";
    }

    // BAN ENTRY COMMENT CONTROL
    if ($userbank->is_admin()) {
        $view_comments = true;
        $commentres    = $GLOBALS['db']->Execute("SELECT cid, aid, commenttxt, added, edittime,
											(SELECT user FROM `" . DB_PREFIX . "_admins` WHERE aid = C.aid) AS comname,
											(SELECT user FROM `" . DB_PREFIX . "_admins` WHERE aid = C.editaid) AS editname
											FROM `" . DB_PREFIX . "_comments` AS C
											WHERE type = 'T' AND bid = '" . $data['ban_id'] . "' ORDER BY added desc");

        if ($commentres->RecordCount() > 0) {
            $comment = array();
            $morecom = 0;
            while (!$commentres->EOF) {
                $cdata            = array();
                $cdata['morecom'] = ($morecom == 1 ? true : false);

                // Comment edit/delete control for owner.
                if ($commentres->fields['aid'] == $userbank->GetAid() || $userbank->HasAccess(ADMIN_OWNER)) {
                    $cdata['editcomlink'] = CreateLinkR('<i class="fas fa-edit fa-lg"></i>', 'index.php?p=teambans&comment=' . $data['ban_id'] . '&ctype=T&cid=' . $commentres->fields['cid'] . $pagelink, 'Edit Comment');
                    if ($userbank->HasAccess(ADMIN_OWNER)) {
                        $cdata['delcomlink'] = "<a href=\"#\" class=\"tip\" title=\"Delete Comment\" target=\"_self\" onclick=\"RemoveComment(" . $commentres->fields['cid'] . ",'T'," . (isset($_GET["page"]) ? $page : -1) . ");\"><i class='fas fa-trash fa-lg'></i></a>";
                    }
                }
                else
                {
                    $cdata['editcomlink'] = "";
                    $cdata['delcomlink']  = "";
                }

                $cdata['comname']    = $commentres->fields['comname'];
                $cdata['added']      = date(Config::get('config.dateformat'), $commentres->fields['added']);
                $cdata['commenttxt'] = htmlspecialchars($commentres->fields['commenttxt']);
                $cdata['commenttxt'] = str_replace("\n", "<br />", $cdata['commenttxt']);
                // Parse links and wrap them in a <a href=""></a> tag to be easily clickable
                $cdata['commenttxt'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $cdata['commenttxt']);

                if (!empty($commentres->fields['edittime'])) {
                    $cdata['edittime'] = date(Config::get('config.dateformat'), $commentres->fields['edittime']);
                    $cdata['editname'] = $commentres->fields['editname'];
                } else {
                    $cdata['edittime'] = "";
                    $cdata['editname'] = "";
                }

                $morecom = 1;
                array_push($comment, $cdata);
                $commentres->MoveNext();
            }
        } else {
            $comment = "None";
        }

        $data['commentdata'] = $comment;
    }


    $data['addcomment'] = CreateLinkR('<i class="fas fa-comment-dots fa-lg"></i> Add Comment', 'index.php?p=teambans&comment=' . $data['ban_id'] . '&ctype=T' . $pagelink);
    //-----------------------------------
    $data['banlength']   = $data['ban_length'] . " " . $data['ub_reason'];
    $data['view_edit']   = ($userbank->HasAccess(ADMIN_OWNER | ADMIN_EDIT_ALL_BANS) || ($userbank->HasAccess(ADMIN_EDIT_OWN_BANS) && $res->fields['aid'] == $userbank->GetAid()) || ($userbank->HasAccess(ADMIN_EDIT_GROUP_BANS) && $res->fields['gid'] == $userbank->GetProperty('gid')));
    $data['view_unban']  = ($userbank->HasAccess(ADMIN_OWNER | ADMIN_UNBAN) || ($userbank->HasAccess(ADMIN_UNBAN_OWN_BANS) && $res->fields['aid'] == $userbank->GetAid()) || ($userbank->HasAccess(ADMIN_UNBAN_GROUP_BANS) && $res->fields['gid'] == $userbank->GetProperty('gid')));
    $data['view_delete'] = ($userbank->HasAccess(ADMIN_OWNER | ADMIN_DELETE_BAN));
    array_push($bans, $data);
    $res->MoveNext();
}

if (isset($_GET['search'])) {
    $advSearchString = "&search=" . (isset($_GET['search']) ? $_GET['search'] : '') . "&searchType=" . (isset($_GET['searchType']) ? $_GET['searchType'] : '');
} else {
    $advSearchString = '';
}

if ($page > 1) {
    if (isset($_GET['c']) && $_GET['c'] == "bans") {
        $prev = CreateLinkR('<i class="fas fa-arrow-left fa-lg"></i> prev', "javascript:void(0);", "", "_self", false, $prev);
    } else {
        $prev = CreateLinkR('<i class="fas fa-arrow-left fa-lg"></i> prev', "index.php?p=teambans&page=" . ($page - 1) . (isset($_GET['search']) > 0 ? "&search=" . $_GET['search'] : '' . $advSearchString));
    }
} else {
    $prev = "";
}

if ($BansEnd < $BanCount) {
    if (isset($_GET['c']) && $_GET['c'] == "bans") {
        if (!isset($nxt)) {
            $nxt = "";
        }
        $next = CreateLinkR('next <i class="fas fa-arrow-right fa-lg"></i>', "javascript:void(0);", "", "_self", false, $nxt);
    } else {
        $next = CreateLinkR('next <i class="fas fa-arrow-right fa-lg"></i>', "index.php?p=teambans&page=" . ($page + 1) . (isset($_GET['searchText']) ? "&searchText=" . $_GET['searchText'] : '' . $advSearchString));
    }
} else {
    $next = "";
}

//=================[ Start Layout ]==================================
$ban_nav = 'displaying&nbsp;' . $BansStart . '&nbsp;-&nbsp;' . $BansEnd . '&nbsp;of&nbsp;' . $BanCount . '&nbsp;results';

if (strlen($prev) > 0) {
    $ban_nav .= ' | <b>' . $prev . '</b>';
}
if (strlen($next) > 0) {
    $ban_nav .= ' | <b>' . $next . '</b>';
}
$pages = ceil($BanCount / $BansPerPage);
if ($pages > 1) {
    $ban_nav .= '&nbsp;<select onchange="changePage(this,\'B\',\'' . (isset($_GET['search']) ? $_GET['search'] : '') . '\',\'' . (isset($_GET['searchType']) ? $_GET['searchType'] : '') . '\');">';
    for ($i = 1; $i <= $pages; $i++) {
        if (isset($_GET["page"]) && $i == $page) {
            $ban_nav .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
            continue;
        }
        $ban_nav .= '<option value="' . $i . '">' . $i . '</option>';
    }
    $ban_nav .= '</select>';
}

//----------------------------------------
// HANDLE COMMENT REQUESTS
//----------------------------------------
if (isset($_GET["comment"]))
{
    $_GET["comment"] = (int) $_GET["comment"];

    $theme->assign('commenttype', (isset($_GET["cid"]) ? "Edit" : "Add"));

    if (isset($_GET["cid"]))
    {
        $_GET["cid"]    = (int) $_GET["cid"];
        $ceditdata      = $GLOBALS['db']->GetRow("SELECT * FROM " . DB_PREFIX . "_comments WHERE cid = '" . $_GET["cid"] . "'");
        $ctext          = htmlspecialchars($ceditdata['commenttxt']);
        $cotherdataedit = " AND cid != '" . $_GET["cid"] . "'";
    } 
    else 
    {
        $cotherdataedit = "";
        $ctext          = "";
    }

    $_GET["ctype"] = substr($_GET["ctype"], 0, 1);

    $cotherdata = $GLOBALS['db']->Execute("SELECT cid, aid, commenttxt, added, edittime,
											(SELECT user FROM `" . DB_PREFIX . "_admins` WHERE aid = C.aid) AS comname,
											(SELECT user FROM `" . DB_PREFIX . "_admins` WHERE aid = C.editaid) AS editname
											FROM `" . DB_PREFIX . "_comments` AS C
											WHERE type = ? AND bid = ?" . $cotherdataedit . " ORDER BY added desc", array(
        $_GET["ctype"],
        $_GET["comment"]
    ));

    $ocomments = array();
    while (!$cotherdata->EOF) {
        $coment               = array();
        $coment['comname']    = $cotherdata->fields['comname'];
        $coment['added']      = date(Config::get('config.dateformat'), $cotherdata->fields['added']);
        $coment['commenttxt'] = htmlspecialchars($cotherdata->fields['commenttxt']);
        $coment['commenttxt'] = str_replace("\n", "<br />", $coment['commenttxt']);
        // Parse links and wrap them in a <a href=""></a> tag to be easily clickable
        $coment['commenttxt'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $coment['commenttxt']);
        if ($cotherdata->fields['editname'] != "") {
            $coment['edittime'] = date(Config::get('config.dateformat'), $cotherdata->fields['edittime']);
            $coment['editname'] = $cotherdata->fields['editname'];
        } else {
            $coment['editname'] = "";
            $coment['edittime'] = "";
        }
        array_push($ocomments, $coment);
        $cotherdata->MoveNext();
    }

    $theme->assign('page', (isset($_GET["page"]) ? $page : -1));
    $theme->assign('othercomments', $ocomments);
    $theme->assign('commenttext', (isset($ctext) ? $ctext : ""));
    $theme->assign('ctype', $_GET["ctype"]);
    $theme->assign('cid', (isset($_GET["cid"]) ? $_GET["cid"] : ""));
}
$theme->assign('view_comments', $view_comments);
$theme->assign('comment', (isset($_GET["comment"]) && $view_comments ? $_GET["comment"] : false));

//----------------------------------------
// Showtime, render all our stuff.
//----------------------------------------
unset($_SESSION['CountryFetchHndl']);

$theme->assign('searchlink', $searchlink);
$theme->assign('hidetext', $hidetext);
$theme->assign('total_bans', $BanCount);
$theme->assign('active_bans', $BanCount);

$theme->assign('ban_nav', $ban_nav);
$theme->assign('ban_list', $bans);
$theme->assign('admin_nick', $userbank->GetProperty("user"));

$theme->assign('admin_postkey', $_SESSION['banlist_postkey']);
$theme->assign('hideadminname', (Config::getBool('banlist.hideadminname') && !$userbank->is_admin()));
$theme->assign('general_unban', $userbank->HasAccess(ADMIN_OWNER | ADMIN_UNBAN | ADMIN_UNBAN_OWN_BANS | ADMIN_UNBAN_GROUP_BANS));
$theme->assign('can_delete', $userbank->HasAccess(ADMIN_DELETE_BAN));
$theme->assign('view_bans', ($userbank->HasAccess(ADMIN_OWNER | ADMIN_EDIT_ALL_BANS | ADMIN_EDIT_OWN_BANS | ADMIN_EDIT_GROUP_BANS | ADMIN_UNBAN | ADMIN_UNBAN_OWN_BANS | ADMIN_UNBAN_GROUP_BANS | ADMIN_DELETE_BAN)));
$theme->display('page_teambans.tpl');

