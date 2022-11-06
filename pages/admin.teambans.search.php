<?php

global $userbank, $theme;
$admin_list   = $GLOBALS['db']->GetAll("SELECT * FROM `" . DB_PREFIX . "_admins` ORDER BY user ASC");
$server_list  = $GLOBALS['db']->Execute("SELECT sid, ip, port FROM `" . DB_PREFIX . "_servers` WHERE enabled = 1");
$servers      = array();
$serverscript = "<script type=\"text/javascript\">";
while (!$server_list->EOF) {
    $info = array();
    $serverscript .= "xajax_ServerHostPlayers('" . $server_list->fields[0] . "', 'id', 'ss" . $server_list->fields[0] . "', '', '', false, 200);";
    $info['sid']  = $server_list->fields[0];
    $info['ip']   = $server_list->fields[1];
    $info['port'] = $server_list->fields[2];
    array_push($servers, $info);
    $server_list->MoveNext();
}
$serverscript .= "</script>";
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$theme->assign('is_admin', $userbank->is_admin());
$theme->assign('admin_list', $admin_list);
$theme->assign('server_list', $servers);
$theme->assign('server_script', $serverscript);

$theme->display('box_admin_teambans_search.tpl');
?>

<script type="text/javascript">
function switch_length(opt)
{
    if (opt.options[opt.selectedIndex].value=='other') {
        $('other_length').setStyle('display', 'block');
        $('other_length').focus();
        $('length').setStyle('width', '20px');
    } else {
        $('other_length').setStyle('display', 'none');
        $('length').setStyle('width', '210px');
    }
}
</script>
