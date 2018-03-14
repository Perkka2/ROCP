<?php
// Listing of all strings used in the CP
$lang = array(

// footer.inc
'queriesexecuted' => "Queries Executed:",
'query#' => "#",
'querytype' => "Type",
'querysource' => "Source",
'queryquery' => "Query",
'querytime' => "Time",
'querytotal' => "Total Query Execution Time:",
'useroptions' => "User Options:",

'page' => array(
	'index.php' => "Home",
	'server_info.php' => "Server Information",
	'view_db.php' => "Item & Monster Database",
	'whosonline.php' => "Who's Online",
	'ladder.php' => "Ladder",
	'mvp_ladder.php' => "MVP Ladder",
	'guild_standings.php' => "Guild Standings",
	'account.php' => "Account Options",
	'roster.php' => "Roster",
	'equipment.php' => "Inventory",
	'exptable.php' => "Experience Table",
	'maplist.php' => "Maplist",
	'money_transfer.php' => "Transfer Money",
	'hairstyle.php' => "Edit %s's Hairstyle",
	'upload_emblem.php' => "Upload %s Emblem"
),

'gmpage' => array(
	'none1' => "Management Tools",
	'account_manage.php' => "Account Management",
	'char_manage.php' => "Character Management",
	'guild_manage.php' => "Guild Management",
	'item_search.php' => "Item Search",
	'none2' => "Ban Controls",
	'ban.php' => "Ban",
	'unban.php' => "Unban",
	'clear_temp_banned.php' => "Clear Temp Bans",
	'clear_banned.php' => "Clear Banned Account",
	'clear_all_banned.php' => "Clear All Banned Accounts",
	'view_ban_list.php' => "View Ban List",
	'none3' => "GM Tools",
	'add_announcement.php' => "Add Announcement",
	'edit_announcement.php' => "Edit Announcement",
	'add_account.php' => "Add Account",
	'pending.php' => "Pending Registration",
	'lookup.php' => "Lookup Accounts/Characters",
	'ladder_ignore.php' => "Ladder Ignore",
	'whosonline_ignore.php' => "Who's Online Ignore",
	'none4' => "Admin Tools",
	'clear.php' => "Clear Accounts/Characters",
	'rebuild_items.php' => "Rebuild Item Database",
	'rebuild_mobs.php' => "Rebuild Mob Database",
	'privileges.php' => "Privileges",
	'none5' => "Logs",
	'view_access_log.php' => "View Access Logs",
	'view_admin_log.php' => "View Admin Logs",
	'view_ban_log.php' => "View Ban Logs",
	'view_exploit_log.php' => "View Exploit Logs",
	'login_log.php' => "View Login Logs",
	'view_money_log.php' => "View Money Transfer Logs",
	'view_register_log.php' => "View Register Logs",
	'view_server_log.php' => "View Server Logs",
	'view_user_log.php' => "View User Logs"
),

'menugo' => "Go",
'poweredby' => "Powered by PHP %s and %s",
'codedby' => "Coded by <a href='about.php'>Azndragon and Perkka</a> &#169; 2018 Version %s",
'skinby' => "Skin: %s by <a href=\"about.php\">%s</a>",
'bestviewed' => "Best viewed using <a href=\"http://www.mozilla.org/products/firefox/\">Firefox</a> and 1024x768",
'exectime' => "Execution Time: %s seconds",
'execquery' => "Queries Executed:",
'gzipenabled' => "GZIP Enabled",
'gzipdisabled' => "GZIP Disabled",

// header.inc
'current' => "Current Server Status",
'accserv' => "Acc Server",
'charserv' => "Char Server",
'mapserv' => "Map Server",
'totalonline' => "Users Online: %d",
'loggedas' => "Logged in as:",
'guest' => "Guest",
'accesslevel' => "Access Level:",
'mail' => "%d new message(s)!",
'changeskin' => "Change Skin",
'terms' => "CP Rules",
'login' => "Login",
'logout' => "Logout",

// home.php
'welcome' => "Welcome to the %s for %s",
'nobackup' => "A backup has not been made yet!",
'backup' => "It has been %d day(s), %d hour(s), %d minute(s), %d second(s) since the last backup!",
'backupnow' => "Backup Now! (Please wait a while for backup to be saved)",
'userheader' => "Announcements for All Users",
'gmheader' => "Announcements for All GMs",
'adminheader' => "Announcements for All Admins",
'poster' => "Poster",
'message' => "Message",
'date' => "Date",
'viewmore' => "View More...",
'yourchars' => "Your Characters Are:",
'yourname' => "Name",
'yourclass' => "Class",
'yourbase' => "Base Level",
'yourjob' => "Job Level",
'yourzeny' => "Zeny",
'yourmap' => "Map",

// ladder.php
'ladderheader' => "%s Player Ladder Top %d",
'ladderheader2' => "%s Most Silenced %d Players",
'laddersort' => "Overall",
'ladderdisplay' => "Display",
'ladderdisplaytop' => "Display Top:",
'ladderinject' => "Possible SQL injection attempt in ladder.php",
'ladderinvalid' => "Invalid Class to search!",
'laddercannotdisplay' => "No characters can be displayed!",
'laddersorttype' => "Sort Type:",
'laddersortlevel' => "Level",
'laddersortzeny' => "Zeny",
'laddersorthonor' => "Honor",
//'laddersortfame' => "Fame",
'ladderrank' => "Rank",
'laddername' => "Name",
'ladderclass' => "Class",
'ladderlevel' => "Level",
'ladderjlevel' => "Job Level",
'ladderzeny' => "Zeny",
'ladderhonor' => "Honor",

// lost_pass.php
'lost_pass' => '
You have requested a password reset for %s. Your new password is "%s".
Please log into the control panel at %s and change your password.

Thank you,

GameMasters of %s
',

// mvp_ladder.php
'mvpcantdisplay' => "MVP Logs are not available for this server.",

// register.php
'confirmemail' => '
You have just requested registration for %s
Your Account Details Are:
Account: %s
Password: %s
Please follow this link to confirm your email and to activate your %s account.
%s/register.php?auth=%s&user=%s
Do not click the link more than once!

Thank you,
GMs of %s
',

'accountadded' => '
Your account for %s is now ready.
Your Account is: %s

You may now connect to the server.

Website: %s
Forums: %s
Patch: %s
IRC: %s
CP: %s

Thank you,
GMs of %s
',


// server_info.php
'server_type' => "Server Running On:",
'serverinfo' => "Server Information",
'website' => "Website:",
'forums' => "Forums:",
'patch' => "Patch:",
'irc' => "IRC Channel:",
'totalacc' => "Total Accounts:",
'totalchar' => "Total Characters:",
'totalguild' => "Total Guilds:",
'totalzeny' => "Total Zeny:",
'serverrate' => "Server Rates:",
'guildwars' => "War of Emperium",
'guildwarsto' =>  "%s to %s",
'rulesofserver' => "Rules of %s",
'serverclass' => "Class",
'serveramount' => "Amount",
'serveradmins' => "GMs and Admins of %s",
'serverchars' => "Character Name",

// whosonline.php
'onlineusers' => "Users Online",
'onlineusersmap' => "Users Online in %s.gat",
'onlinename' => "Character Name",
'onlineclass' => "Class",
'onlinebase' => "Base Level",
'onlinejob' => "Job Level",
'onlinex' => "X",
'onliney' => "Y",
'onlinemap' => "Map",
'onlineguildwar' => "You cannot use this page when guild wars are on!",
'onlineinvalid' => "Invalid Map!",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",
'' => "",

'test' => "test"
);
/*
foreach ($lang as $index => $value) {
	$lang[$index] = "*" . $value . "*";
}
*/


?>
