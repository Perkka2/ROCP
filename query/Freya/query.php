<?php
// Query "dictionary" for MySQL

$cp = $CONFIG_cp_db_name;

// Freya item_db columns, may change later?
$item_name = "name_english";
$attack = "attack";
$defence = "defence";
$price = "price_buy";

// account.php
DEFINE('CHECK_BARD', "SELECT * FROM `char` WHERE account_id = %d AND (class = 19 OR class = 20)");
DEFINE('CHECK_SEX', "SELECT sex FROM login WHERE userid = '%s'");
DEFINE('CHECK_OLD_PASS', "SELECT * FROM login WHERE userid = '%s' AND user_pass = '%s'");
DEFINE('CHECK_OLD_MD5_PASS', "SELECT * FROM login WHERE userid = '%s' AND user_pass = MD5('%s')");
DEFINE('UPDATE_NEW_PASS', "UPDATE login SET user_pass = '%s' WHERE account_id = %d");
DEFINE('UPDATE_NEW_MD5_PASS', "UPDATE login SET user_pass = MD5('%s') WHERE account_id = %d");
DEFINE('UPDATE_SEX', "UPDATE login SET sex = '%s' WHERE account_id = %d");
DEFINE('UPDATE_EMAIL', "UPDATE login SET email = '%s' WHERE account_id = '%s'");

// account_manage.php
DEFINE('ACCOUNT_SEARCH', "SELECT login.account_id, userid, user_pass, sex, email, level,
$cp.privilege.privilege, $cp.ladder_ignore.account_id
FROM login
LEFT JOIN $cp.privilege ON login.account_id = $cp.privilege.account_id
LEFT JOIN $cp.ladder_ignore ON login.account_id = $cp.ladder_ignore.account_id
WHERE userid LIKE '%%%s%%' OR user_pass LIKE '%%%s%%'
OR email LIKE '%%%s%%'
");
DEFINE('ACCOUNT_SHOW_LIST', "SELECT login.account_id, userid, user_pass, sex, email, level,
$cp.privilege.privilege, $cp.ladder_ignore.account_id
FROM login
LEFT JOIN $cp.privilege ON login.account_id = $cp.privilege.account_id
LEFT JOIN $cp.ladder_ignore ON login.account_id = $cp.ladder_ignore.account_id
ORDER BY login.account_id
");
DEFINE('UPDATE_ACCOUNT', "UPDATE login
SET userid = '%s', user_pass = '%s', sex = '%s', email = '%s', level = %d
WHERE account_id = %d
");
DEFINE('ACCOUNT_SHOW_EDIT', "SELECT login.account_id, userid, user_pass, sex, email, level, $cp.privilege.privilege
FROM login
LEFT JOIN $cp.privilege ON login.account_id = $cp.privilege.account_id
WHERE login.account_id = %d
");
DEFINE('DISPLAY_ACCOUNT_ITEMS', "SELECT char_id FROM `char` WHERE account_id = %d");

// add_announcement.php
DEFINE('ANNOUNCE_ADD_USER', "INSERT INTO $cp.user_announce (date, message, poster) VALUES (NOW(), '%s', '%s')");
DEFINE('ANNOUNCE_ADD_GM', "INSERT INTO $cp.gm_announce (date, message, poster) VALUES (NOW(), '%s', '%s')");
DEFINE('ANNOUNCE_ADD_ADMIN', "INSERT INTO $cp.admin_announce (date, message, poster) VALUES (NOW(), '%s', '%s')");

// ban.php
DEFINE('BAN_ACCOUNT', "UPDATE login
SET level = -1
WHERE account_id = %d
");

// change_skin.php
DEFINE('UPDATE_SKIN', "UPDATE $cp.skins
SET skin = '%s'
WHERE login = '%s'
");

// char_manage.php
DEFINE('CHAR_COUNT', "SELECT count(*) FROM `char` %s");
DEFINE('CHAR_COUNT_CONDITION_NAME', "WHERE name LIKE '%%%s%%'");
DEFINE('CHAR_COUNT_CONDITION_CLASS', "WHERE class = %d");
DEFINE('CHAR_LIST', "SELECT char_id, char.account_id, login.userid, char_num, name, class, base_level, job_level, zeny, str, agi, vit, `int`, dex, luk, max_hp, max_sp,
$cp.privilege.privilege, IF(online = 0, 0, 1)
FROM `char`
LEFT JOIN $cp.privilege ON char.account_id = $cp.privilege.account_id
LEFT JOIN login ON char.account_id = login.account_id
%s
");
DEFINE('CHAR_SEARCH', "WHERE name LIKE '%%%s%%'");
DEFINE('CHAR_SHOW_LIST', "ORDER BY char_id");
DEFINE('CHAR_SORT', "ORDER by `%s` DESC");
DEFINE('CHAR_SORT_CLASS', "WHERE class = %d ORDER by char_id");
DEFINE('CHAR_EDIT', "UPDATE `char`
SET char_num = %d, name = '%s', class = %d, base_level = %d, job_level = %d, zeny = %d,
str = %d, agi = %d, vit = %d, `int` = %d, dex = %d, luk = %d, max_hp = %d, max_sp = %d,
status_point = %d, skill_point = %d, last_map = '%s', last_x = %d, last_y = %d, save_map = '%s',
save_x = %d, save_y = %d, `option` = %d WHERE char_id = %d
");
DEFINE('CHAR_SHOW_EDIT', "SELECT char_id, account_id, char_num, name, class, base_level, job_level,
zeny, str, agi, vit, `int`, dex, luk, max_hp, max_sp, status_point, skill_point, last_map,
last_x, last_y, save_map, save_x, save_y, IF(online = 0, 0, 1), lpad(bin(`option`), 14, 0)
FROM `char` WHERE char_id = %d
");

// clear.php
DEFINE('SHOW_INACTIVE_ACCOUNTS', "SELECT account_id, userid, lastlogin, logincount
FROM `login`
WHERE lastlogin <= DATE_SUB(NOW(), INTERVAL %d DAY)
AND lastlogin > 0
ORDER BY lastlogin
");
DEFINE('SHOW_ILLEGAL_CHARS', "SELECT char.account_id, char_id, name, login.userid
FROM `char`
LEFT JOIN `login` ON login.account_id = char.account_id
WHERE name REGEXP '[^[:alnum:]_ -]'
ORDER BY char_id
");

// edit_announcement.php
DEFINE('SHOW_EDIT_USER_ANNOUNCE', "SELECT * FROM $cp.user_announce");
DEFINE('SHOW_EDIT_GM_ANNOUNCE', "SELECT * FROM $cp.gm_announce");
DEFINE('SHOW_EDIT_ADMIN_ANNOUNCE', "SELECT * FROM $cp.admin_announce");
DEFINE('SHOW_EDIT_MESSAGE', "SELECT * FROM $cp.%s WHERE post_id = %d");
DEFINE('SAVE_ANNOUNCEMENT', "UPDATE $cp.%s
SET date = '%s',
message = '%s',
poster = '%s'
WHERE post_id = %d
");
DEFINE('DELETE_ANNOUNCEMENT', "DELETE FROM $cp.%s WHERE post_id = %d");

// footer.inc
DEFINE('SHOW_HAIRSTYLE', "SELECT name, char_num
FROM `char`
WHERE account_id = %d
ORDER by char_num
");
DEFINE('SHOW_GUILD_MASTER', "SELECT guild_id, name FROM guild WHERE master = '%s'");

// functions.php
DEFINE('AUTH', "SELECT account_id FROM login
WHERE BINARY userid = BINARY '%s'
AND md5(user_pass) = '%s'
");
DEFINE('AUTH_MD5', "SELECT account_id FROM login
WHERE BINARY userid = BINARY '%s'
AND BINARY user_pass = BINARY '%s'
");
DEFINE('GET_LEVEL', "SELECT privilege
FROM $cp.privilege
WHERE account_id = %d
");
DEFINE('IS_ONLINE', "SELECT * FROM `char` WHERE online <> 0 AND account_id = %d");
DEFINE('USERID_TO_ACCOUNTID', "SELECT account_id FROM login WHERE userid = '%s'");
DEFINE('ACCOUNTID_TO_USERID', "SELECT userid FROM login WHERE account_id = '%s'");
DEFINE('CHARID_TO_CHARNAME', "SELECT name FROM `char` WHERE char_id = %d");
DEFINE('CHARNAME_TO_CHARID', "SELECT char_id FROM `char` WHERE name = '%s'");
DEFINE('GUILDID_TO_GUILDNAME', "SELECT name FROM guild WHERE guild_id = %d");
DEFINE('GUILDNAME_TO_GUILDID', "SELECT guild_id FROM guild WHERE name = '%s'");
DEFINE('ITEMNAME_TO_ITEMID', "SELECT ID FROM $cp.item_db WHERE name_english = '%s'");
DEFINE('ITEMID_TO_ITEMNAME', "SELECT Name FROM item_db WHERE ID = %d");
DEFINE('GET_ONLINE', "SELECT count(*) FROM `char` WHERE online <> 0 GROUP BY online");
DEFINE('GET_ACC_COUNT', "SELECT count(*) FROM login WHERE sex <> 'S'");
DEFINE('GET_CHAR_COUNT', "SELECT count(*) FROM `char`");
DEFINE('GET_ZENY_COUNT', "SELECT sum(zeny) FROM `char`
LEFT JOIN $cp.ladder_ignore ON $cp.ladder_ignore.account_id = char.account_id
WHERE $cp.ladder_ignore.account_id IS NULL
");
DEFINE('GET_GUILD_COUNT', "SELECT count(*) FROM guild");
DEFINE('CHARS_ON_ACCOUNT', "SELECT char.char_id FROM login, `char`
WHERE char.account_id = login.account_id AND login.account_id = %d
");
DEFINE('ACCOUNT_OF_CHAR', "SELECT account_id FROM `char` WHERE name = '%s'");
DEFINE('SHOW_GUILD_INFO', "SELECT guild_id, name FROM guild
WHERE md5(CONCAT(guild_id, '%s')) = '%s'
");
DEFINE('SHOW_GUILD_ALLIANCE', "SELECT opposition, name FROM guild_alliance
WHERE guild_id = %d
");
DEFINE('SHOW_GUILD_MEMBERS', "SELECT guild_member.name, class, lv, exp, guild_member.position, guild_position.name
FROM guild_member
LEFT JOIN guild_position ON (
guild_position.guild_id = guild_member.guild_id
AND guild_position.position = guild_member.position
)
WHERE guild_member.guild_id = %d
ORDER BY guild_member.position
");
DEFINE('ADD_ACCESS_ENTRY', "INSERT INTO $cp.access_log (Date, `User/IP`, Action) VALUES(NOW(), '%s', '%s')");
DEFINE('ADD_ADMIN_ENTRY', "INSERT INTO $cp.admin_log (Date, User, Action) VALUES(NOW(), '%s', '%s')");
DEFINE('ADD_BAN_ENTRY', "INSERT INTO $cp.ban_log (Date, set_account_id, ban_account_id, reason) VALUES(NOW(), '%s', '%s', '%s')");
DEFINE('ADD_EXPLOIT_ENTRY', "INSERT INTO $cp.exploit_log (Date, `User/IP`, Action) VALUES(NOW(), '%s', '%s')");
DEFINE('CHECK_LOG_CHAR_ID', "SELECT char_id FROM `char`
WHERE md5(CONCAT(char_id, '%s')) = '%s'
");
DEFINE('ADD_MONEY_ENTRY', "INSERT INTO $cp.money_log (Date, `From`, `To`, Action) VALUES(NOW(), %d, %d, '%s')");
DEFINE('ADD_QUERY_ENTRY', "INSERT INTO $cp.query_log (Date, User, IP, page, Query) VALUES(NOW(), '%s', '%s', '%s', '%s')");
DEFINE('ADD_UNBAN_ENTRY', "INSERT INTO $cp.ban_log (Date, set_account_id, ban_account_id, reason) VALUES(NOW(), '%s', '%s', '%s')");
DEFINE('ADD_USER_ENTRY', "INSERT INTO $cp.user_log (Date, User, Action) VALUES(NOW(), '%s', '%s')");
DEFINE('CHECK_BAN_ACCOUNT', "SELECT account_id FROM login
WHERE account_id = '%s' OR userid = '%s'
");
DEFINE('CHECK_IF_BANNED', "SELECT account_id, userid FROM login
WHERE account_id = %d
AND level <> -1
");
DEFINE('CHECK_IF_UNBANNED', "SELECT account_id, userid FROM login
WHERE account_id = %d
AND level = -1
");
DEFINE('CHECK_GUILD_MASTER', "SELECT guild_id FROM guild WHERE master = '%s'");
DEFINE('DELETE_GUILD', "DELETE FROM %s WHERE guild_id = %d");
DEFINE('LEAVE_GUILD', "DELETE FROM guild_member WHERE char_id = %d");
DEFINE('CHECK_PARTY_MASTER', "SELECT party_id FROM party WHERE leader_id = %d");
DEFINE('DELETE_PARTY', "DELETE FROM party WHERE party_id = %d");
DEFINE('DELETE_CHAR', "DELETE FROM `%s` WHERE char_id = %d");

// guild_standings.php
DEFINE('SHOW_GUILD_LADDER', "SELECT guild.guild_id, guild.name, master, guild_lv, count(guild_member.name) as blah, max_member, average_lv, guild.exp
FROM guild
LEFT JOIN guild_member ON guild.guild_id = guild_member.guild_id
GROUP BY guild_member.guild_id
ORDER BY guild_lv DESC, exp DESC
");
DEFINE('SHOW_GUILD_CASTLES', "SELECT guild_castle.castle_id, guild_castle.guild_id, name
FROM guild_castle, guild
WHERE guild_castle.guild_id = guild.guild_id
ORDER by castle_id
");

// guild_manage.php
DEFINE('DISPLAY_GUILD_MANAGE', "SELECT guild.guild_id, guild.name, master, guild_lv, connect_member, count(guild_member.name) as blah, max_member, average_lv, guild.exp
FROM guild
LEFT JOIN guild_member ON guild.guild_id = guild_member.guild_id
GROUP BY guild_member.guild_id
ORDER BY guild_id
");
DEFINE('DISPLAY_GUILD_CASTLES', "SELECT guild_castle.castle_id, guild.name, economy, defense, visibleC,
visibleG0, visibleG1, visibleG2, visibleG3, visibleG4, visibleG5, visibleG6, visibleG7
FROM guild_castle
LEFT JOIN guild ON guild.guild_id = guild_castle.guild_id
ORDER by castle_id
");
DEFINE('GUILD_SHOW_EDIT', "SELECT guild_id, name, master, guild_lv, exp
FROM guild WHERE guild_id = %d
");
DEFINE('CASTLE_SHOW_EDIT', "SELECT castle_id, guild_id, economy, defense, visibleC,
visibleG0, visibleG1, visibleG2, visibleG3, visibleG4, visibleG5, visibleG6, visibleG7
FROM guild_castle
WHERE castle_id = '%s'
");
DEFINE('GUILD_SEARCH', "SELECT guild.guild_id, guild.name, master, guild_lv, connect_member, count(guild_member.name) as blah, max_member, average_lv, guild.exp
FROM guild
LEFT JOIN guild_member ON guild.guild_id = guild_member.guild_id
WHERE guild.name LIKE '%%%s%%'
GROUP BY guild_member.guild_id
");
DEFINE('DELETE_ALLIANCE', "DELETE FROM guild_alliance 
WHERE guild_id = %d OR opposition = %d
");
DEFINE('UPDATE_GUILD', "UPDATE guild
SET name = '%s',
guild_lv = %d,
exp = %d
WHERE guild_id = %d
");
DEFINE('UPDATE_GUILD_CASTLE', "UPDATE guild_castle
SET guild_id = %d, economy = %d, defense = %d, visibleC = %d, visibleG0 = %d, visibleG1 = %d,
visibleG2 = %d, visibleG3 = %d, visibleG4 = %d, visibleG5 = %d, visibleG6 = %d, visibleG7 = %d
WHERE castle_id = %d
");
DEFINE('EMPTY_GUILD_CASTLE', "UPDATE guild_castle
SET guild_id = 0, economy = 0, defense = 0, triggerE = 0,
triggerD = 0, nextTime = 0, payTime = 0, createTime = 0,
visibleC = 0, visibleG0 = 0,  visibleG1 = 0,  visibleG2 = 0,
visibleG3 = 0,  visibleG4 = 0,  visibleG5 = 0,  visibleG6 = 0,
visibleG7 = 0 WHERE castle_id = %d
");

// hairstyle.php
DEFINE('GET_HAIR_NUMBER', "SELECT hair FROM `char`
WHERE char_num = %d
AND account_id = %d
");
DEFINE('UPDATE_HAIR', "UPDATE `char`
SET hair = %d
WHERE char_num = %d
AND account_id = %d
");

// header.inc
DEFINE('CHECK_STATUS', "SELECT * FROM $cp.status");
DEFINE('UPDATE_STATUS', "UPDATE $cp.status SET last_checked = NOW(), login_serv = %d, char_serv = %d, map_serv = %d");
DEFINE('INSERT_STATUS', "INSERT INTO $cp.status VALUES(NOW(), 0, 0, 0)");

// home.php
DEFINE('USER_ANNOUNCE', "SELECT poster, message, date FROM $cp.user_announce ORDER BY post_id DESC");
DEFINE('GM_ANNOUNCE', "SELECT poster, message, date FROM $cp.gm_announce ORDER BY post_id DESC");
DEFINE('ADMIN_ANNOUNCE', "SELECT poster, message, date FROM $cp.admin_announce ORDER BY post_id DESC");
DEFINE('HOME_CHARS', "SELECT name, class, base_level, job_level, zeny
FROM `char`
WHERE account_id = %d
");

// item_search.php
DEFINE('LIST_ITEMS', "SELECT ID, name_english FROM $cp.item_db WHERE name_english <> 'Unknown_Item' ORDER BY name_english");

// ladder.php
DEFINE('LADDER_SORT_DEFAULT',"base_level DESC, base_exp DESC");
DEFINE('LADDER_SORT_ZENY',"zeny DESC");
DEFINE('LADDER_SORT_LEVEL', "SELECT char.account_id, char_id, name, class, base_level, job_level, zeny
FROM `char`
LEFT JOIN $cp.ladder_ignore ON char.account_id = $cp.ladder_ignore.account_id
WHERE $cp.ladder_ignore.account_id IS NULL
ORDER BY %s
");
DEFINE('LADDER_SORT_MULTI_CLASS', "SELECT char.account_id, char_id, name, class, base_level, job_level, zeny
FROM `char`
LEFT JOIN $cp.ladder_ignore ON char.account_id = $cp.ladder_ignore.account_id
WHERE $cp.ladder_ignore.account_id IS NULL
AND (class = %d OR class = %d)
ORDER BY %s
");
DEFINE('LADDER_SORT_CLASS', "SELECT char.account_id, char_id, name, class, base_level, job_level, zeny
FROM `char`
LEFT JOIN $cp.ladder_ignore ON char.account_id = $cp.ladder_ignore.account_id
WHERE $cp.ladder_ignore.account_id IS NULL
AND class = %d
ORDER BY %s
");

// ladder_ignore.php
DEFINE('SHOW_IGNORED', "SELECT $cp.ladder_ignore.account_id, login.userid
FROM $cp.ladder_ignore, login
WHERE $cp.ladder_ignore.account_id = login.account_id
ORDER BY account_id
");
DEFINE('ADD_IGNORED', "INSERT INTO $cp.ladder_ignore VALUES (%d)");
DEFINE('DEL_IGNORED', "DELETE FROM $cp.ladder_ignore WHERE account_id = %d");

// lookup.php
DEFINE('DISPLAY_CHAR_DATA', "SELECT name, class, base_level, job_level, zeny
FROM `char` WHERE account_id = %d
");
DEFINE('SEARCH_ACCOUNT', "SELECT account_id, userid FROM login WHERE userid LIKE '%%%s%%'");
DEFINE('SEARCH_ACCOUNT_ID', "SELECT account_id, userid FROM login WHERE account_id = %d");
DEFINE('SEARCH_CHAR', "SELECT char.account_id, login.userid
FROM `char`
LEFT JOIN login ON login.account_id = char.account_id
WHERE name LIKE '%%%s%%'
");
DEFINE('SEARCH_CHAR_ID', "SELECT char.account_id, login.userid
FROM `char`
LEFT JOIN login ON login.account_id = char.account_id
WHERE char_id = %d
");

// login.php
DEFINE('REQUEST_RESEND', "SELECT * FROM $cp.pending WHERE userid = '%s' AND email = '%s'");

// lost_pass.php
DEFINE('CHECK_LOST_PASS', "SELECT userid, email FROM login
WHERE userid = '%s'
AND email = '%s'
");
DEFINE('RESET_NEW_PASS', "UPDATE login
SET user_pass = '%s'
WHERE userid = '%s'
");

// memory.php
DEFINE('GET_SKIN', "SELECT skin FROM $cp.skins WHERE login = '%s'");
DEFINE('INSERT_SKIN', "INSERT INTO $cp.skins VALUES('%s', '%s')");

// money_transfer.php
DEFINE('MONEY_GET_FIRST', "SELECT char_id, char_num, name, class, base_level, job_level, zeny FROM `char`
WHERE account_id = %d
AND base_level >= 20
ORDER BY char_num
");
DEFINE('MONEY_GET_SECOND', "SELECT char_id, char_num, name, class, base_level, job_level, zeny FROM `char`
WHERE account_id = %d
AND base_level >= 20
AND md5(CONCAT(char_id, '%s')) <> '%s'
ORDER BY char_num
");
DEFINE('GET_TRANSFER_INFO', "SELECT name, zeny FROM `char`
WHERE md5(CONCAT(char_id, '%s')) = '%s'
");
DEFINE('CHECK_TRANSFER_INFO', "SELECT account_id, name, base_level, zeny FROM `char`
WHERE md5(CONCAT(char_id, '%s')) = '%s'
");
DEFINE('FINAL_TRANSFER', "UPDATE `char` SET zeny = zeny %s %d WHERE md5(CONCAT(char_id, '%s')) = '%s' AND online = 0 AND zeny = %d");

// mvp_ladder.php
DEFINE('GET_MVP_DATE', "SELECT mvp_date FROM log.mvplog ORDER BY mvp_id");
DEFINE('SHOW_MVP', "SELECT kill_char_id, char.name, char.class, char.base_level, char.job_level, count(*) AS MVP 
FROM log.mvplog
LEFT JOIN `char` ON char.char_id = log.mvplog.kill_char_id
LEFT JOIN $cp.ladder_ignore ON $cp.ladder_ignore.account_id = char.account_id
WHERE $cp.ladder_ignore.account_id IS NULL AND char.name IS NOT NULL
GROUP BY log.mvplog.kill_char_id 
ORDER BY MVP DESC, base_level DESC
");

// pending.php
DEFINE('VIEW_PENDING', "SELECT * FROM $cp.pending");
DEFINE('AUTH_PENDING', "SELECT * FROM $cp.pending WHERE auth_code = '%s'");
DEFINE('DEL_PENDING', "DELETE FROM $cp.pending WHERE auth_code = '%s'");
DEFINE('DEL_ALL_PENDING', "DELETE FROM $cp.pending");
DEFINE('CONFIRM_AUTH', "SELECT * FROM $cp.pending WHERE auth_code = '%s' AND userid = '%s'");

// privileges.php
DEFINE('GET_PRIVILEGE_LIST', "SELECT login.userid, privilege, login.account_id
FROM login, $cp.privilege
WHERE login.account_id = $cp.privilege.account_id
ORDER by privilege, account_id
");
DEFINE('PRIVILEGE_EDIT', "SELECT login.userid, privilege, login.account_id
FROM login, $cp.privilege
WHERE login.account_id = $cp.privilege.account_id
AND $cp.privilege.account_id = %d
");
DEFINE('CHECK_PREV_PRIVILEGE', "SELECT $cp.privilege.account_id FROM $cp.privilege
LEFT JOIN login ON login.account_id = $cp.privilege.account_id
WHERE login.userid = '%s'
");
DEFINE('ADD_PRIVILEGE', "INSERT INTO $cp.privilege VALUES(%d, %d)");
DEFINE('CHECK_LAST_ADMIN', "SELECT * FROM $cp.privilege WHERE privilege = 4");
DEFINE('DEL_PRIVILEGE', "DELETE FROM $cp.privilege WHERE account_id = %d");
DEFINE('UPDATE_PRIVILEGE', "UPDATE $cp.privilege
SET privilege = %d
WHERE account_id = %d
");

// rebuild_items.php
DEFINE('CLEAR_ITEM_TABLE', "DELETE FROM $cp.item_db");
DEFINE('GET_ITEM_TABLE', "SELECT ID, $item_name, type, $price, weight, $attack,
$defence, range, slots, equip_jobs, equip_genders, equip_locations, weapon_level, equip_level 
FROM item_db ORDER BY ID");
DEFINE('INSERT_ITEM_TABLE', "INSERT INTO $cp.item_db VALUES(%d, '%s', %s, %s, %s, %s, %s, %s, %s, %s,
%s, %s, %s, %s)");

// rebuild_mobs.php
DEFINE('CLEAR_MOB_TABLE', "DELETE FROM $cp.mob_db");
DEFINE('GET_MOB_TABLE', "SELECT ID, Name, LV, HP, EXP, JEXP, ATK1, ATK2, DEF, MDEF, STR, AGI,
VIT, `INT`, DEX, LUK, Scale, Race, Element, Drop1id, Drop1per, Drop2id, Drop2per, Drop3id, Drop3per,
Drop4id, Drop4per, Drop5id, Drop5per, Drop6id, Drop6per, Drop7id, Drop7per, Drop8id, Drop8per 
FROM mob_db ORDER BY ID");
DEFINE('INSERT_MOB_TABLE', "INSERT INTO $cp.mob_db VALUES(%d, '%s', %d, %d, %d, %d, %d, %d, %d, %d,
%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d
)");

// register.php
DEFINE('CLEAR_CODES', "
DELETE FROM $cp.anti_bot WHERE ctime < %d
");
DEFINE('INSERT_CODE', "INSERT INTO $cp.anti_bot VALUES('%s', %d, %d)");
DEFINE('GET_CODE', "SELECT reg_code FROM $cp.anti_bot WHERE reg_id = '%s'");
DEFINE('CHECK_DUPE_ACCOUNT', "SELECT userid FROM login WHERE userid = '%s'");
DEFINE('CHECK_DUPE_PENDING_ACCOUNT', "SELECT userid FROM $cp.pending WHERE userid = '%s'");
DEFINE('CHECK_CODE', "SELECT reg_code FROM $cp.anti_bot WHERE reg_id = '%s' AND reg_code = %d");
DEFINE('DELETE_CODE', "DELETE FROM $cp.anti_bot WHERE reg_id = '%s'");
DEFINE('CHECK_MAX_ACCOUNTS', "SELECT count(*) FROM login");
DEFINE('CHECK_MAX_ACCOUNTS_IP', "SELECT count(*) FROM $cp.register_log WHERE ip = '%s'");
DEFINE('CHECK_MAX_ACCOUNTS_EMAIL', "SELECT count(*) FROM login WHERE email = '%s'");
DEFINE('CHECK_MAX_PENDING_ACCOUNTS_IP', "SELECT count(*) FROM $cp.register_log WHERE ip = '%s'");
DEFINE('CHECK_MAX_PENDING_ACCOUNTS_EMAIL', "SELECT count(*) FROM $cp.pending WHERE email = '%s'");
DEFINE('ADD_ACCOUNT', "INSERT INTO login (userid, user_pass, lastlogin, sex, email, level) VALUES('%s', '%s', NOW(), '%s', '%s' %d)");
DEFINE('ADD_PENDING', "INSERT INTO $cp.pending VALUES(NOW(), '%s', '%s', '%s', '%s', '%s', '%s')");
DEFINE('ADD_REGISTER_ENTRY', "INSERT INTO $cp.register_log (account_name, ip, reg_time, Email, Level) VALUES('%s', %d, NOW(), '%s', %d)");

// roster.php
DEFINE('GET_ROSTER_CHARS', "SELECT char_num, name, class, base_level, job_level, zeny
FROM `char`
WHERE account_id = %d ORDER BY char_num
");
DEFINE('GET_ROSTER_GID', "SELECT char_id
FROM `char`
WHERE char_num = %d
AND account_id = %d
");
DEFINE('MOVE_ROSTER', "UPDATE `char`
SET char_num = %d
WHERE char_id = %d
AND account_id = %d
");

// server_info.php
DEFINE('GET_CLASS_COUNT', "SELECT class, count(name) FROM `char` GROUP BY class");
DEFINE('SHOW_ADMIN', "SELECT char.name, privilege
FROM `char`, login, $cp.privilege
WHERE char.account_id = login.account_id
AND login.account_id = $cp.privilege.account_id
ORDER BY $cp.privilege.privilege
");
DEFINE('CLASS_BREAKDOWN', "SELECT name, base_level
FROM `char`
LEFT JOIN $cp.ladder_ignore ON char.account_id = $cp.ladder_ignore.account_id
WHERE $cp.ladder_ignore.account_id IS NULL
AND (class = %d OR class = %d)
ORDER BY base_level DESC, base_exp DESC
");

// unban.php
DEFINE('UNBAN_ACCOUNT', "UPDATE login
SET level = '0'
WHERE account_id = %d
");

// upload_emblem.php
DEFINE('CHECK_MASTER', "SELECT master FROM guild WHERE md5(CONCAT(guild_id, '%s')) = '%s'");

// view_access_log.php
DEFINE('VIEW_ACCESS_LOG', "SELECT * FROM $cp.access_log");

// view_admin_log.php
DEFINE('VIEW_ADMIN_LOG', "SELECT * FROM $cp.admin_log");

// view_announcement.php
DEFINE('VIEW_ANNOUNCE', "SELECT * FROM $cp.%s");

// view_ban_list.php
DEFINE('SHOW_BAN_LIST', "SELECT account_id, userid FROM login WHERE level = -1");

// view_ban_log.php
DEFINE('VIEW_BAN_LOG', "SELECT * FROM $cp.ban_log");

// view_exploit_log.php
DEFINE('VIEW_EXPLOIT_LOG', "SELECT * FROM $cp.exploit_log");

// view_money_log.php
DEFINE('VIEW_MONEY_LOG', "SELECT action_id, Date, char1.name, char2.name, Action
FROM $cp.money_log
LEFT JOIN `char` AS char1 ON char1.char_id = $cp.money_log.From
LEFT JOIN `char` AS char2 ON char2.char_id = $cp.money_log.To
");

// view_register_log.php
DEFINE('VIEW_REGISTER_LOG', "SELECT * FROM $cp.register_log ORDER BY reg_time");
DEFINE('VIEW_SORT_REGISTER_LOG', "SELECT * FROM $cp.register_log WHERE ip = '%s' ORDER BY reg_time");

// view_user_log.php
DEFINE('VIEW_USER_LOG', "SELECT * FROM $cp.user_log");

// view_db.php
DEFINE('SHOW_FULL_ITEMS', "SELECT id, name_english, type, price_buy, weight, attack, defence,
range, slots, equip_jobs, equip_genders, equip_locations, weapon_level, equip_level
FROM $cp.item_db
%s
ORDER BY name_english
");
DEFINE('COUNT_FULL_ITEMS', "SELECT id
FROM $cp.item_db
%s
ORDER BY name_english
");
DEFINE('SEARCH_CLASS', "WHERE substring(lpad(bin(equip_jobs), 24, 0), 24 - %d, 1) = 1 AND equip_locations IS NOT NULL");
DEFINE('COUNT_FULL_MOBS', "SELECT $cp.mob_db.Name
FROM $cp.mob_db
%s AND exp > 0
ORDER BY $cp.mob_db.Name
");
DEFINE('SHOW_FULL_MOBS', "SELECT $cp.mob_db.Name, LV, HP, EXP, JEXP, ATK1, ATK2, mob_db.DEF, MDEF,
Element, scale, race, STR, AGI, VIT, `INT`, DEX, LUK,
Drop1id, Drop1per, Drop1id, Drop2id, Drop2per, Drop2id, Drop3id, Drop3per, Drop3id, Drop4id, Drop4per, Drop4id,
Drop5id, Drop5per, Drop5id, Drop6id, Drop6per, Drop6id, Drop7id, Drop7per, Drop7id, Drop8id, Drop8per, Drop8id 
FROM $cp.mob_db
%s AND exp > 0
ORDER BY $cp.mob_db.Name
");
DEFINE('SEARCH_MONSTER', "WHERE mob_db.Name LIKE '%%%s%%'");
DEFINE('SEARCH_ITEM', "
WHERE 
(
mob_db.Drop1id IN (%s) OR
mob_db.Drop2id IN (%s) OR
mob_db.Drop3id IN (%s) OR
mob_db.Drop4id IN (%s) OR
mob_db.Drop5id IN (%s) OR
mob_db.Drop6id IN (%s) OR
mob_db.Drop7id IN (%s) OR
mob_db.Drop8id IN (%s)
)");

// whosonline.php
DEFINE('ONLINE_WITH_GM', "AND $cp.privilege.account_id IS NOT NULL");
DEFINE('ONLINE_WITHOUT_GM', "AND $cp.privilege.account_id IS NULL");
DEFINE('CONDITION_MAP', "AND last_map = '%s.gat'");
DEFINE('SHOW_POSITION', "last_x, last_y,");

// Arguements: show/not show position, show gm/nongm, filter/not filter by map
DEFINE('SHOW_ONLINE', "SELECT char.name, class, base_level, job_level, %s last_map
FROM `char`
LEFT JOIN $cp.privilege ON char.account_id = $cp.privilege.account_id
WHERE online <> 0
%s
%s
ORDER BY last_map, char.name
");
?>