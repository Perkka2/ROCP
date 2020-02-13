<?php
// Access Levels
// Set each value to the MINIMUM access level to access each page.
// -1 = Nobody can access (Disabled)
// 0 = No Login
// 1 = Logged in as User
// 2 = [Leave empty, because In-Game GMs should not get any additional privileges]
// 3 = GM and Admin
// 4 = Admin Only

// Note: Don't change the if statements, unless you know what you are doing.
// They are used to enable/disable server-specific pages.

$access['about.php'] = 0;
$access['account.php'] = 1;
$access['account_manage.php'] = 3;
$access['add_account.php'] = 3;
$access['add_announcement.php'] = 3;
if ($CONFIG_server_type == 0) {
	$access['backup_server.php'] = -1;
}
else {
	$access['backup_server.php'] = 3;
}
$access['ban.php'] = 3;
$access['char_manage.php'] = 3;
$access['change_skin.php'] = 1;
$access['equipment.php'] = 1;
$access['exptable.php'] = 1;
$access['maplist.php'] = 1;
if ($CONFIG_server_type == 0) {
	$access['clear_all_banned.php'] = 4;
	$access['clear_banned.php'] = 4;
	$access['clear.php'] = 4;
}
else {
	$access['clear_all_banned.php'] = 4;
	$access['clear_banned.php'] = 4;
	$access['clear.php'] = 4;
}
if ($CONFIG_server_type == 0) {
	$access['clear_temp_banned.php'] = -1;
}
else {
	$access['clear_temp_banned.php'] = 3;
}
$access['edit_announcement.php'] = 3;
$access['edit_config.php'] = 4;
$access['guild_manage.php'] = 3;
$access['guild_standings.php'] = 1;
$access['hairstyle.php'] = -1;
$access['index.php'] = 1;
$access['item_manage.php'] = 3;
$access['item_search.php'] = 3;
$access['ladder.php'] = 1;
$access['ladder_ignore.php'] = 3;
if ($CONFIG_server_type == 0) {
	$access['whosonline_ignore.php'] = -1;
}
else {
	$access['whosonline_ignore.php'] = 3;
}
$access['login.php'] = 0;
if ($CONFIG_server_type == 0) {
	$access['login_log.php'] = -1;
}
else {
	$access['login_log.php'] = 4;
}
$access['lookup.php'] = 3;
$access['lost_pass.php'] = 0;
$access['money_transfer.php'] = 1;
if (($CONFIG_server_type < 3) || ($CONFIG_server_type == 2 && $CONFIG_db_logs)) {
	$access['mvp_ladder.php'] = 1;
}
else {
	$access['mvp_ladder.php'] = -1;
}
$access['pending.php'] = 3;
$access['privileges.php'] = 4;
$access['rebuild_items.php'] = 4;
if ($CONFIG_server_type == 0) {
	$access['rebuild_mobs.php'] = -1;
}
else {
	$access['rebuild_mobs.php'] = 4;
}
$access['register.php'] = 0;
$access['roster.php'] = 1;
$access['server_info.php'] = 1;
$access['terms.php'] = 0;
$access['unban.php'] = 3;
$access['upload_emblem.php'] = 1;
$access['view_access_log.php'] = 4;
$access['view_admin_log.php'] = 4;
$access['view_ban_list.php'] = 3;
$access['view_ban_log.php'] = 3;
$access['view_mobs.php'] = 1;
$access['view_items.php'] = 1;
$access['view_exploit_log.php'] = 4;
$access['view_money_log.php'] = 3;
$access['view_register_log.php'] = 3;

if (($CONFIG_server_type < 2) || ($CONFIG_server_type == 2 && $CONFIG_db_logs)) {
	$access['view_server_log.php'] = 4;
}
else {
	$access['view_server_log.php'] = -1;
}
$access['view_user_log.php'] = 3;
// Aegis who's online shows account names, better for GMs and over only
if ($CONFIG_server_type == 0) {
	$access['whosonline.php'] = 3;
}
else {
	$access['whosonline.php'] = 1;
}

?>
