<?php
$char = "character.dbo";
$cp = "$CONFIG_cp_db_name.dbo";
$log = "ItemLog.dbo";
$login = "nLogin.dbo";
$script = "script.dbo";
$user = "[user].dbo";

// Query "dictionary" for MSSQL

// account.php
DEFINE('CHECK_BARD', "SELECT * FROM $char.charinfo WHERE AID = %d AND (job = 19 OR job = 20)");
DEFINE('CHECK_SEX', "SELECT sex FROM $login.account WHERE [Name] = '%s'");
DEFINE('CHECK_OLD_PASS', "SELECT * FROM $login.login WHERE ID = '%s' AND passwd = '%s'");
DEFINE('CHECK_OLD_MD5_PASS', "SELECT * FROM login WHERE ID = '%s' AND passwd = SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', '%s')), 3, 32)");
DEFINE('UPDATE_NEW_PASS', "UPDATE $login.login SET passwd = '%s' WHERE AID = %d");
DEFINE('UPDATE_NEW_MD5_PASS', "UPDATE $login.login SET passwd = SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', '%s')), 3, 32) WHERE AID = %d");
DEFINE('UPDATE_SEX', "UPDATE $login.account SET sex = %d WHERE AID = %d");
DEFINE('UPDATE_EMAIL', "UPDATE $login.account SET Email = '%s' WHERE AID = '%s'");

// account_manage.php
DEFINE('ACCOUNT_SEARCH', "SELECT login.AID, login.ID, login.passwd, account.sex, account.Email, login.isConfirmed,
$cp.privilege.privilege, $cp.ladder_ignore.AID, $user.t_user.tu_state
FROM login
LEFT JOIN account ON account.AID = login.AID
LEFT JOIN $cp.privilege ON login.AID = $cp.privilege.AID
LEFT JOIN $cp.ladder_ignore ON login.AID = $cp.ladder_ignore.AID
LEFT JOIN $user.t_user ON $user.t_user.tu_aid = login.AID
WHERE ID LIKE '%%%s%%' OR passwd LIKE '%%%s%%'
OR Email LIKE '%%%s%%'
");
DEFINE('ACCOUNT_SHOW_LIST', "SELECT login.AID, login.ID, login.passwd, account.sex, account.Email, login.isConfirmed,
$cp.privilege.privilege, $cp.ladder_ignore.AID, $user.t_user.tu_state
FROM login
LEFT JOIN account ON account.AID = login.AID
LEFT JOIN $cp.privilege ON login.AID = $cp.privilege.AID
LEFT JOIN $cp.ladder_ignore ON login.AID = $cp.ladder_ignore.AID
LEFT JOIN $user.t_user ON $user.t_user.tu_aid = login.AID
ORDER BY login.AID
");
DEFINE('UPDATE_ACCOUNT', "UPDATE $login.login
SET ID = '%s', passwd = '%s', isConfirmed = %d WHERE AID = %d
");
DEFINE('UPDATE_ACCOUNT2', "UPDATE $login.account
SET [Name] = '%s', sex = %d, Email = '%s'
WHERE AID = %d
");
DEFINE('ACCOUNT_SHOW_EDIT', "SELECT login.AID, ID, passwd, sex, Email, $login.login.isConfirmed, $cp.privilege.privilege
FROM $login.login
LEFT JOIN $login.account ON $login.account.AID = $login.login.AID
LEFT JOIN $cp.privilege ON login.AID = $cp.privilege.AID
WHERE login.AID = %d
");
DEFINE('DISPLAY_ACCOUNT_ITEMS', "SELECT GID FROM $char.charinfo WHERE AID = %d");

// add_announcement.php
DEFINE('ANNOUNCE_ADD_USER', "INSERT INTO $cp.user_announce ([Date], message, poster) VALUES (getDate(), '%s', '%s')");
DEFINE('ANNOUNCE_ADD_GM', "INSERT INTO $cp.gm_announce ([Date], message, poster) VALUES (getDate(), '%s', '%s')");
DEFINE('ANNOUNCE_ADD_ADMIN', "INSERT INTO $cp.admin_announce ([Date], message, poster) VALUES (getDate(), '%s', '%s')");

// ban.php
DEFINE('BAN_ACCOUNT', "UPDATE $login.login
SET isConfirmed = 1
WHERE AID = %d
");

// change_skin.php
DEFINE('UPDATE_SKIN', "UPDATE $cp.skins
SET skin = '%s'
WHERE [Name] = '%s'
");

// char_manage.php
DEFINE('CHAR_COUNT', "SELECT count(*) FROM $char.charinfo %s");
DEFINE('CHAR_COUNT_CONDITION_NAME', "WHERE charname LIKE '%%%s%%'");
DEFINE('CHAR_COUNT_CONDITION_CLASS', "WHERE job = %d");
DEFINE('CHAR_LIST', "SELECT GID, $char.charinfo.AID, $login.login.ID, CharNum, charname, job, clevel, joblevel, money, STR, AGI, VIT, [INT], DEX, LUK, maxhp, maxsp,
$cp.privilege.privilege
FROM $char.charinfo
LEFT JOIN $cp.privilege ON $char.charinfo.AID = $cp.privilege.AID
LEFT JOIN $login.login ON $char.charinfo.AID = $login.login.AID
%s
");
DEFINE('CHAR_SEARCH', "WHERE charname LIKE '%%%s%%'");
DEFINE('CHAR_SHOW_LIST', "ORDER BY GID");
DEFINE('CHAR_SORT', "ORDER BY [%s] DESC");
DEFINE('CHAR_SORT_CLASS', "WHERE job = %d ORDER by GID");
DEFINE('CHAR_EDIT', "UPDATE $char.charinfo
SET CharNum = %d, charname = '%s', job = %d, clevel = %d, joblevel = %d, money = %d, STR = %d,
AGI = %d, VIT = %d, [INT] = %d, DEX = %d, LUK = %d, maxhp = %d, maxsp = %d, jobpoint = %d,
sppoint = %d, mapName = '%s', xPos = %d, yPos = %d, restartMapName = '%s', sxPos = %d,
syPos = %d, effectstate = %d, bodystate = %d, healthstate = %d WHERE GID = %d
");
DEFINE('CHAR_SHOW_EDIT', "SELECT GID, AID, CharNum, charname, job, clevel, joblevel, money, STR,
AGI, VIT, [INT], DEX, LUK, maxhp, maxsp, jobpoint, sppoint, mapName, xPos, yPos, restartMapName,
sxPos, syPos, 1, $cp.DecToBin(effectstate, 13), $cp.DecToBin(bodystate, 3), $cp.DecToBin(healthstate, 5)
FROM $char.charinfo WHERE GID = %d
");

// clear.php
DEFINE('SHOW_ILLEGAL_CHARS', "SELECT $char.charinfo.AID, $char.charinfo.GID, $char.charinfo.charname,
$login.login.ID
FROM $char.charinfo
LEFT JOIN $login.login ON $login.login.AID = $char.charinfo.AID
WHERE ($cp.ValidateInput(charname) = 0)
ORDER BY GID
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
DEFINE('SHOW_HAIRSTYLE', "SELECT charname, CharNum
FROM $char.charinfo
WHERE AID = %d
ORDER by CharNum
");
DEFINE('SHOW_GUILD_MASTER', "SELECT GDID, Name FROM $char.GuildInfoDB WHERE MName = '%s'");

// functions.php
DEFINE('AUTH', "SELECT AID FROM $login.login
WHERE CAST(ID AS varbinary) = CAST('%s' AS varbinary)
AND SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', passwd)), 3, 32) LIKE '%s'
COLLATE SQL_Latin1_General_CP1_CI_AS
");
DEFINE('AUTH_MD5', "SELECT AID FROM $login.login
WHERE CAST(ID AS varbinary) = CAST('%s' AS varbinary)
AND passwd = '%s'
");
DEFINE('GET_LEVEL', "SELECT privilege
FROM $cp.privilege
WHERE AID = %d
");
DEFINE('IS_ONLINE', "SELECT * FROM $user.t_user WHERE tu_state = 1 AND tu_aid = %d");
DEFINE('USERID_TO_ACCOUNTID', "SELECT AID FROM $login.login WHERE ID = '%s'");
DEFINE('ACCOUNTID_TO_USERID', "SELECT ID FROM $login.login WHERE AID = '%s'");
DEFINE('CHARID_TO_CHARNAME', "SELECT charname FROM $char.charinfo WHERE GID = %d");
DEFINE('CHARNAME_TO_CHARID', "SELECT GID FROM $char.charinfo WHERE charname = '%s'");
DEFINE('GUILDID_TO_GUILDNAME', "SELECT Name FROM $char.GuildInfoDB WHERE GDID = %d");
DEFINE('GUILDNAME_TO_GUILDID', "SELECT GDID FROM $char.GuildInfoDB WHERE Name = '%s'");
DEFINE('ITEMNAME_TO_ITEMID', "SELECT ID FROM $cp.item_db WHERE Name = '%s'");
DEFINE('ITEMID_TO_ITEMNAME', "SELECT Name FROM $cp.item_db WHERE ID = %d");
DEFINE('GET_ONLINE', "SELECT count(*) FROM $user.t_user WHERE tu_state = 1");
DEFINE('GET_ACC_COUNT', "SELECT count(*) FROM $login.login");
DEFINE('GET_CHAR_COUNT', "SELECT count(*) FROM $char.charinfo");
DEFINE('GET_ZENY_COUNT', "SELECT sum(money) FROM $char.charinfo
LEFT JOIN $cp.ladder_ignore ON $cp.ladder_ignore.AID = $char.charinfo.AID
WHERE $cp.ladder_ignore.AID IS NULL
");
DEFINE('GET_GUILD_COUNT', "SELECT count(*) FROM $char.GuildInfoDB");
DEFINE('CHARS_ON_ACCOUNT', "SELECT $char.charinfo.GID FROM $login.login, $char.charinfo
WHERE $char.charinfo.AID = $login.login.AID AND $login.login.AID = %d
");
DEFINE('ACCOUNT_OF_CHAR', "SELECT AID FROM $char.charinfo WHERE charname = '%s'");
DEFINE('SHOW_GUILD_INFO', "SELECT GDID, [Name] FROM $char.GuildInfoDB
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GDID AS varchar(6)) + '%s')), 3, 32) = '%s'
COLLATE SQL_Latin1_General_CP1_CI_AS
");
DEFINE('SHOW_GUILD_ALLIANCE', "SELECT Relation, GuildName FROM $char.GuildAllyInfo
WHERE GDID = %d
");
DEFINE('SHOW_GUILD_MEMBERS', "SELECT $char.GuildMInfo.CharName, Class, Level, MemberExp, $char.GuildMInfo.PositionID, $char.GuildMPosition.PosName
FROM $char.GuildMInfo
LEFT JOIN $char.GuildMPosition ON (
$char.GuildMPosition.GDID = $char.GuildMInfo.GDID
AND $char.GuildMPosition.PositionID = $char.GuildMInfo.PositionID
)
WHERE $char.GuildMInfo.GDID = %d
ORDER BY $char.GuildMInfo.PositionID
");
DEFINE('ADD_ACCESS_ENTRY', "INSERT INTO $cp.access_log ([Date], [User/IP], Action) VALUES(getDate(), '%s', '%s')");
DEFINE('ADD_ADMIN_ENTRY', "INSERT INTO $cp.admin_log ([Date], [User], Action) VALUES(getDate(), '%s', '%s')");
DEFINE('ADD_BAN_ENTRY', "INSERT INTO $cp.ban_log (Date, set_ID, ban_ID, reason) VALUES(getDate(), '%s', '%s', '%s')");
DEFINE('ADD_EXPLOIT_ENTRY', "INSERT INTO $cp.exploit_log ([Date], [User/IP], Action) VALUES(getDate(), '%s', '%s')");
DEFINE('CHECK_LOG_CHAR_ID', "SELECT GID FROM $char.charinfo
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GID AS varchar(6)) + '%s')), 3, 32) LIKE '%s' COLLATE SQL_Latin1_General_CP1_CI_AS
");
DEFINE('ADD_MONEY_ENTRY', "INSERT INTO $cp.money_log ([Date], [From], [To], Action) VALUES(getDate(), %d, %d, '%s')");
DEFINE('ADD_QUERY_ENTRY', "INSERT INTO $cp.query_log ([Date], [User], [IP], [Page], [Query]) VALUES(getDate(), '%s', '%s', '%s', '%s')");
DEFINE('ADD_UNBAN_ENTRY', "INSERT INTO $cp.ban_log (Date, set_ID, ban_ID, reason) VALUES(getDate(), '%s', '%s', '%s')");
DEFINE('ADD_USER_ENTRY', "INSERT INTO $cp.user_log (Date, [User], Action) VALUES(getDate(), '%s', '%s')");
DEFINE('CHECK_BAN_ACCOUNT', "SELECT AID FROM $login.login
WHERE AID = '%s' OR ID = '%s'
");
DEFINE('CHECK_IF_BANNED', "SELECT AID, ID FROM $login.login
WHERE AID = %d
AND isConfirmed <> 1
");
DEFINE('CHECK_IF_UNBANNED', "SELECT AID, ID FROM $login.login
WHERE AID = %d
AND isConfirmed = 1
");
DEFINE('CHECK_GUILD_MASTER', "SELECT GDID FROM $char.GuildInfoDB WHERE MName = '%s'");
DEFINE('DELETE_GUILD', "DELETE FROM $char.%s WHERE GDID = %d");
DEFINE('LEAVE_GUILD', "DELETE FROM $char.GuildMInfo WHERE GID = %d");
DEFINE('CHECK_PARTY_MASTER', "SELECT GRID FROM $char.GroupMInfo WHERE GID = %d AND Role = 1");
DEFINE('DELETE_PARTY', "DELETE FROM $char.GroupInfo WHERE GRID = %d");
DEFINE('DELETE_PARTY2', "DELETE FROM $char.GroupMInfo WHERE GRID = %d");
DEFINE('DELETE_CHAR', "DELETE FROM $char.%s WHERE GID = %d");

// guild_standings.php
DEFINE('SHOW_GUILD_LADDER', "SELECT Info.GDID, [Name], MName, Info.[Level],
count($char.GuildMInfo.CharName), MaxUserNum, avg($char.GuildMInfo.[Level]), Info.Exp
FROM $char.GuildInfoDB as Info
LEFT JOIN $char.GuildMInfo ON Info.GDID = $char.GuildMInfo.GDID
GROUP BY Info.GDID, Info.Name, Info.MName, Info.[Level], Info.MaxUserNum, Info.Exp
ORDER BY Info.Level DESC, Info.Exp DESC
");
DEFINE('SHOW_GUILD_CASTLES', "SELECT $char.Agit.agitName, $char.Agit.GDID, $char.GuildInfoDB.Name
FROM $char.Agit, $char.GuildInfoDB
WHERE $char.Agit.GDID = $char.GuildInfoDB.GDID
");

// guild_manage.php
DEFINE('DISPLAY_GUILD_MANAGE', "SELECT Info.GDID, [Name], MName, Info.[Level], count($char.GuildMInfo.CharName), MaxUserNum, AVG($char.GuildMInfo.Level), Info.Exp
FROM $char.GuildInfoDB as Info
LEFT JOIN $char.GuildMInfo ON Info.GDID = $char.GuildMInfo.GDID
GROUP BY Info.GDID, Info.Name, Info.MName, Info.[Level], Info.MaxUserNum, Info.Exp
ORDER BY Info.GDID
");
DEFINE('DISPLAY_GUILD_CASTLES', "SELECT $char.Agit.agitName, $char.GuildInfoDB.Name, economy, defense, visibleC,
visibleG0, visibleG1, visibleG2, visibleG3, visibleG4, visibleG5, visibleG6, visibleG7
FROM $char.Agit, $char.GuildInfoDB
WHERE $char.Agit.GDID = $char.GuildInfoDB.GDID
ORDER by $char.Agit.agitName
");
DEFINE('GUILD_SHOW_EDIT', "SELECT GDID, Name, MName, Level, Exp FROM $char.GuildInfoDB
WHERE GDID = %d
");
DEFINE('CASTLE_SHOW_EDIT', "SELECT agitName, GDID, economy, defense, visibleC,
visibleG0, visibleG1, visibleG2, visibleG3, visibleG4, visibleG5, visibleG6, visibleG7
FROM $char.Agit
WHERE $char.Agit.agitName = '%s'
");
DEFINE('GUILD_SEARCH', "SELECT Info.GDID, [Name], MName, Info.[Level], count($char.GuildMInfo.CharName), MaxUserNum, AVG($char.GuildMInfo.Level), Info.Exp
FROM $char.GuildInfoDB as Info
LEFT JOIN $char.GuildMInfo ON Info.GDID = $char.GuildMInfo.GDID
WHERE [Name] LIKE '%%%s%%'
GROUP BY Info.GDID, Info.Name, Info.MName, Info.[Level], Info.MaxUserNum, Info.Exp
");
DEFINE('DELETE_ALLIANCE', "DELETE FROM $char.GuildAllyInfo
WHERE GDID = %d OR OpponentGDID = %d
");
DEFINE('UPDATE_GUILD', "UPDATE $char.GuildInfoDB
SET [Name] = '%s', Level = %d, Exp = %d WHERE GDID = %d
");
DEFINE('UPDATE_GUILD_CASTLE', "UPDATE $char.Agit
SET GDID = %d, economy = %d, defense = %d, visibleC = %d, visibleG0 = %d, visibleG1 = %d, visibleG2 = %d,
visibleG3 = %d, visibleG4 = %d, visibleG5 = %d, visibleG6 = %d, visibleG7 = %d WHERE agitName = '%s'
");
DEFINE('EMPTY_GUILD_CASTLE', "UPDATE $char.Agit
SET GDID = 0, economy = 0, defense = 0, triggerE = 0,
triggerD = 0, nextTime = 0, payTime = 0, createTime = 0,
visibleC = 0, visibleG0 = 0,  visibleG1 = 0,  visibleG2 = 0,
visibleG3 = 0,  visibleG4 = 0,  visibleG5 = 0,  visibleG6 = 0,
visibleG7 = 0 WHERE agitName = '%s'
");

// hairstyle.php
DEFINE('GET_HAIR_NUMBER', "SELECT head FROM $char.charinfo
WHERE CharNum = %d
AND AID = %d
");
DEFINE('UPDATE_HAIR', "UPDATE $char.charinfo SET head = %d WHERE GID = %d AND AID = %d");

// header.inc
DEFINE('CHECK_STATUS', "SELECT * FROM $cp.status");
DEFINE('UPDATE_STATUS', "UPDATE $cp.status SET last_checked = getDate(), login_serv = %d, char_serv = %d, zone_serv = %d");
DEFINE('INSERT_STATUS', "INSERT INTO $cp.status VALUES(getDate(), 0, 0, 0)");

// home.php
DEFINE('USER_ANNOUNCE', "SELECT poster, message, date FROM $cp.user_announce ORDER BY post_id DESC");
DEFINE('GM_ANNOUNCE', "SELECT poster, message, date FROM $cp.gm_announce ORDER BY post_id DESC");
DEFINE('ADMIN_ANNOUNCE', "SELECT poster, message, date FROM $cp.admin_announce ORDER BY post_id DESC");
DEFINE('HOME_CHARS', "SELECT charname, job, clevel, joblevel, money, mapname
FROM $char.charinfo
WHERE AID = %d
");

// item_functions.php
DEFINE('SEARCH_ITEMS', "EXEC $cp.%s %s");
DEFINE('SHOW_SOURCE_ITEMS', "SELECT %s FROM $char.%s WHERE %s = %d");
DEFINE('IS_IN_ITEMDB', "SELECT * FROM $script.%s WHERE ID = %d");
//DEFINE('IS_ARROW', "SELECT * FROM $script.arrow where ID = %d");
//DEFINE('IS_QUEST', "SELECT * FROM $script.guest where ID = %d");

// item_search.php
DEFINE('LIST_ITEMS', "SELECT ID, Name FROM $cp.item_db ORDER BY Name");

// ladder.php
DEFINE('LADDER_SORT_DEFAULT',"clevel DESC, exp DESC");
DEFINE('LADDER_SORT_ZENY',"money DESC");
DEFINE('LADDER_SORT_HONOR', "honor");

DEFINE('LADDER_SORT_LEVEL', "SELECT $char.charinfo.AID, GID, charname, job, clevel, joblevel, money
FROM $char.charinfo
LEFT JOIN $cp.ladder_ignore ON $char.charinfo.AID = $cp.ladder_ignore.AID
WHERE $cp.ladder_ignore.AID IS NULL
ORDER BY %s
");
DEFINE('LADDER_SORT_MULTI_CLASS', "SELECT $char.charinfo.AID, GID, charname, job, clevel, joblevel, money
FROM $char.charinfo
LEFT JOIN $cp.ladder_ignore ON $char.charinfo.AID = $cp.ladder_ignore.AID
WHERE $cp.ladder_ignore.AID IS NULL
AND (job = %d OR job = %d)
ORDER BY %s
");
DEFINE('LADDER_SORT_CLASS', "SELECT $char.charinfo.AID, GID, charname, job, clevel, joblevel, money
FROM $char.charinfo
LEFT JOIN $cp.ladder_ignore ON $char.charinfo.AID = $cp.ladder_ignore.AID
WHERE $cp.ladder_ignore.AID IS NULL
AND job = %d
ORDER BY %s
");
DEFINE('LADDER_SHOW_ALL', "SELECT $char.charinfo.AID, GID, charname, job, clevel, joblevel, money
FROM $char.charinfo
ORDER BY charname
");

// ladder_ignore.php
DEFINE('SHOW_IGNORED', "SELECT $cp.ladder_ignore.AID, login.ID
FROM $cp.ladder_ignore, login
WHERE $cp.ladder_ignore.AID = login.AID
ORDER BY $login.login.AID
");
DEFINE('ADD_IGNORED', "INSERT INTO $cp.ladder_ignore VALUES (%d)");
DEFINE('DEL_IGNORED', "DELETE FROM $cp.ladder_ignore WHERE AID = %d");

// lookup.php
DEFINE('DISPLAY_CHAR_DATA', "SELECT charname, job, clevel, joblevel, money
FROM $char.charinfo WHERE AID = %d
");
DEFINE('SEARCH_ACCOUNT', "SELECT AID, ID FROM $login.login WHERE ID LIKE '%%%s%%'");
DEFINE('SEARCH_ACCOUNT_ID', "SELECT AID, ID FROM $login.login WHERE AID = %d");
DEFINE('SEARCH_CHAR', "SELECT $char.charinfo.AID, $login.login.ID
FROM $char.charinfo
LEFT JOIN $login.login ON $login.login.AID = $char.charinfo.AID
WHERE charname LIKE '%%%s%%'
");
DEFINE('SEARCH_CHAR_ID', "SELECT $char.charinfo.AID, $login.login.ID
FROM $char.charinfo
LEFT JOIN $login.login ON $login.login.AID = $char.charinfo.AID
WHERE GID = %d
");

// login.php
DEFINE('REQUEST_RESEND', "SELECT * FROM $cp.pending WHERE ID = '%s' AND email = '%s'");

// lost_pass.php
DEFINE('CHECK_LOST_PASS', "SELECT $login.login.ID, $login.account.Email FROM $login.login
LEFT JOIN $login.account ON $login.account.AID = $login.login.AID
WHERE $login.login.ID = '%s'
AND $login.account.Email = '%s'
");
DEFINE('RESET_NEW_PASS', "UPDATE $login.login
SET passwd = '%s'
WHERE ID = '%s'
");

// memory.php
DEFINE('GET_SKIN', "SELECT skin FROM $cp.skins WHERE name = '%s'");
DEFINE('INSERT_SKIN', "INSERT INTO $cp.skins VALUES('%s', '%s')");

// money_transfer.php
DEFINE('MONEY_GET_FIRST', "SELECT GID, CharNum, charname, job, clevel, joblevel, money from $char.charinfo
WHERE AID = %d
AND clevel >= 20
ORDER BY CharNum
");
DEFINE('MONEY_GET_SECOND', "SELECT GID, CharNum, charname, job, clevel, joblevel, money FROM $char.charinfo
WHERE AID = %d
AND clevel >= 20
AND SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GID AS varchar(6)) + '%s')), 3, 32) NOT LIKE '%s'
COLLATE SQL_Latin1_General_CP1_CI_AS
ORDER BY CharNum
");
DEFINE('GET_TRANSFER_INFO', "SELECT charname, money FROM $char.charinfo
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GID AS varchar(6)) + '%s')), 3, 32) LIKE '%s' COLLATE SQL_Latin1_General_CP1_CI_AS
");
DEFINE('CHECK_TRANSFER_INFO', "SELECT AID, charname, clevel, money FROM $char.charinfo
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GID AS varchar(6)) + '%s')), 3, 32) LIKE '%s' COLLATE SQL_Latin1_General_CP1_CI_AS
");
DEFINE('FINAL_TRANSFER', "UPDATE $char.charinfo SET money = money %s %d
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GID AS varchar(6)) + '%s')), 3, 32) LIKE '%s' COLLATE SQL_Latin1_General_CP1_CI_AS
AND money = %d
");

// mvp_ladder.php
DEFINE('GET_MVP_DATE', "SELECT logtime FROM $log.itemLog WHERE Action = 7 ORDER BY logtime");
DEFINE('SHOW_MVP', "SELECT srcCharID, $char.charinfo.charname, $char.charinfo.job, $char.charinfo.clevel, $char.charinfo.joblevel, count(*) AS MVP
FROM $log.itemLog
LEFT JOIN $char.charinfo ON $char.charinfo.GID = $log.itemLog.srcCharID
LEFT JOIN $cp.ladder_ignore ON $cp.ladder_ignore.AID = $log.itemLog.srcAccountID
WHERE $log.itemLog.Action = 7 AND $cp.ladder_ignore.AID IS NULL AND $char.charinfo.charname IS NOT NULL
GROUP BY $log.itemLog.srcCharID, $char.charinfo.charname, $char.charinfo.job, $char.charinfo.clevel, $char.charinfo.joblevel
ORDER BY MVP DESC, clevel DESC
");

// pending.php
DEFINE('VIEW_PENDING', "SELECT * FROM $cp.pending");
DEFINE('AUTH_PENDING', "SELECT * FROM $cp.pending WHERE auth_code = '%s'");
DEFINE('DEL_PENDING', "DELETE FROM $cp.pending WHERE auth_code = '%s'");
DEFINE('DEL_ALL_PENDING', "DELETE FROM $cp.pending");

// privileges.php
DEFINE('GET_PRIVILEGE_LIST', "SELECT login.ID, privilege, login.AID
FROM $login.login, $cp.privilege
WHERE $login.login.AID = $cp.privilege.AID
ORDER by privilege, $cp.privilege.AID
");
DEFINE('PRIVILEGE_EDIT', "SELECT login.ID, privilege, login.AID
FROM $login.login, $cp.privilege
WHERE $login.login.AID = $cp.privilege.AID
AND $cp.privilege.AID = %d
");
DEFINE('CHECK_PREV_PRIVILEGE', "SELECT $cp.privilege.AID FROM $cp.privilege
LEFT JOIN $login.login ON $login.login.AID = $cp.privilege.AID
WHERE $login.login.ID = '%s'");
DEFINE('ADD_PRIVILEGE', "INSERT INTO $cp.privilege VALUES(%d, %d)");
DEFINE('CHECK_LAST_ADMIN', "SELECT * FROM $cp.privilege WHERE privilege = 4");
DEFINE('DEL_PRIVILEGE', "DELETE FROM $cp.privilege WHERE AID = %d");
DEFINE('UPDATE_PRIVILEGE', "UPDATE $cp.privilege
SET privilege = %d
WHERE AID = %d
");

// rebuild_items.php
DEFINE('CLEAR_ITEM_TABLE', "DELETE FROM $cp.item_db");
DEFINE('INSERT_ITEM_TABLE', "INSERT INTO $cp.item_db VALUES(%d, '%s', %s, %s, %s, %s, %s, %s, %s, %s,
%s, %s, %s, %s)");

// register.php
DEFINE('CLEAR_CODES', "DELETE FROM $cp.anti_bot WHERE ctime < %d");
DEFINE('INSERT_CODE', "INSERT INTO $cp.anti_bot VALUES('%s', %d, %d)");
DEFINE('GET_CODE', "SELECT reg_code FROM $cp.anti_bot WHERE reg_id LIKE '%s'");
DEFINE('CHECK_DUPE_ACCOUNT', "SELECT ID FROM $login.login WHERE ID = '%s'");
DEFINE('CHECK_DUPE_PENDING_ACCOUNT', "SELECT ID FROM $cp.pending WHERE ID = '%s'");
DEFINE('CHECK_CODE', "SELECT reg_code FROM $cp.anti_bot WHERE reg_id = '%s' AND reg_code = %d");
DEFINE('DELETE_CODE', "DELETE FROM $cp.anti_bot WHERE reg_id = '%s'");
DEFINE('CHECK_MAX_ACCOUNTS', "SELECT count(*) FROM $login.login");
DEFINE('CHECK_MAX_ACCOUNTS_IP', "SELECT count(*) FROM $cp.register_log WHERE ip = '%s'");
DEFINE('CHECK_MAX_ACCOUNTS_EMAIL', "SELECT count(*) FROM $login.account WHERE Email = '%s'");
DEFINE('CHECK_MAX_PENDING_ACCOUNTS_IP', "SELECT count(*) FROM $cp.pending WHERE ip = '%s'");
DEFINE('CHECK_MAX_PENDING_ACCOUNTS_EMAIL', "SELECT count(*) FROM $cp.pending WHERE email = '%s'");
DEFINE('ADD_ACCOUNT', "INSERT INTO $login.login (ID, passwd, grade, isConfirmed) VALUES('%s', '%s', 2, 3)");
DEFINE('ADD_ACCOUNT2', "INSERT INTO $login.account (AID, [Name], Address, Phone, RegNum, zipcode, sex, Email, News, nation) VALUES(%d, '%s', '', '', '', '', %d, '%s', '', '')");
DEFINE('INSERT_T_USER', "INSERT INTO $user.t_user VALUES (0, 0, %d, '%s', 0, '%s', %d, 0, -1, 0)");
DEFINE('ADD_PENDING', "INSERT INTO $cp.pending VALUES(getDate(), '%s', '%s', '%s', '%s', '%s', '%s')");
DEFINE('ADD_REGISTER_ENTRY', "INSERT INTO $cp.register_log (account_name, [ip], reg_time, Email) VALUES('%s', %d, getDate(), '%s')");
DEFINE('CONFIRM_AUTH', "SELECT * FROM $cp.pending WHERE auth_code = '%s' AND ID = '%s'");

// roster.php
DEFINE('GET_ROSTER_CHARS', "SELECT CharNum, charname, job, clevel, joblevel, money
FROM $char.charinfo
WHERE AID = %d ORDER BY CharNum
");
DEFINE('GET_ROSTER_GID', "SELECT GID
FROM $char.charinfo
WHERE CharNum = %d
AND AID = %d
");
DEFINE('MOVE_ROSTER', "UPDATE $char.charinfo
SET CharNum = %d
WHERE GID = %d
AND AID = %d
");

// server_info.php
DEFINE('GET_CLASS_COUNT', "SELECT job, count(charname) FROM $char.charinfo GROUP BY job");
DEFINE('SHOW_ADMIN', "SELECT $char.charinfo.charname, privilege
FROM $char.charinfo, $login.login, $cp.privilege
WHERE $char.charinfo.AID = $login.login.AID
AND $login.login.AID = $cp.privilege.AID
ORDER BY $cp.privilege.privilege
");
DEFINE('CLASS_BREAKDOWN', "SELECT charname, clevel
FROM $char.charinfo
LEFT JOIN $cp.ladder_ignore ON $char.charinfo.AID = $cp.ladder_ignore.AID
WHERE $cp.ladder_ignore.AID IS NULL
AND (job = %d OR job = %d)
ORDER BY clevel DESC, exp DESC
");

// unban.php
DEFINE('UNBAN_ACCOUNT', "UPDATE $login.login
SET isConfirmed = 3
WHERE AID = %d
");

// upload_emblem.php
DEFINE('CHECK_MASTER', "SELECT MName FROM $char.GuildInfoDB
WHERE SUBSTRING(master.dbo.fn_varbintohexstr(HashBytes('MD5', CAST(GDID AS varchar(6)) + '%s')), 3, 32) LIKE '%s' COLLATE SQL_Latin1_General_CP1_CI_AS
");

// view_access_log.php
DEFINE('VIEW_ACCESS_LOG', "SELECT * FROM $cp.access_log");

// view_admin_log.php
DEFINE('VIEW_ADMIN_LOG', "SELECT * FROM $cp.admin_log");

// view_announcement.php
DEFINE('VIEW_ANNOUNCE', "SELECT * FROM $cp.%s");

// view_ban_list.php
DEFINE('SHOW_BAN_LIST', "SELECT AID, ID FROM $login.login WHERE isConfirmed = 1");

// view_ban_log.php
DEFINE('VIEW_BAN_LOG', "SELECT * FROM $cp.ban_log");

// view_exploit_log.php
DEFINE('VIEW_EXPLOIT_LOG', "SELECT * FROM $cp.exploit_log");

// view_money_log.php
DEFINE('VIEW_MONEY_LOG', "SELECT action_id, [Date], char1.charname, char2.charname, Action
FROM $cp.money_log
LEFT JOIN $char.charinfo AS char1 ON char1.GID = $cp.money_log.[From]
LEFT JOIN $char.charinfo AS char2 ON char2.GID = $cp.money_log.[To]
");

// view_register_log.php
DEFINE('VIEW_REGISTER_LOG', "SELECT * FROM $cp.register_log ORDER BY reg_time");
DEFINE('VIEW_SORT_REGISTER_LOG', "SELECT * FROM $cp.register_log WHERE ip = '%s' ORDER BY reg_time");

// view_user_log.php
DEFINE('VIEW_USER_LOG', "SELECT * FROM $cp.user_log");

// view_db.php
DEFINE('SHOW_FULL_ITEMS', "SELECT id, NAME, type, price, weight, ATK, DEF,
range, Slots, Equip_jobs, Equip_genders, Equip_location, weapon_level, equipableLevel
FROM $cp.item_db
%s AND (Type < 4 OR Type = 6 OR Type = 8)
ORDER BY NAME
");
DEFINE('COUNT_FULL_ITEMS', "SELECT id
FROM $cp.item_db
%s AND (Type < 4 OR Type = 6)
ORDER BY NAME
");
DEFINE('SEARCH_CLASS', "WHERE SUBSTRING($cp.DecToBin(Equip_jobs, 20) COLLATE SQL_Latin1_General_CP1_CI_AS, 20 - %d, 1) = '1' AND Equip_jobs IS NOT NULL");
DEFINE('COUNT_FULL_MOBS', "SELECT $script.monparameter.Name
FROM $script.monparameter
LEFT JOIN $script.MVP ON $script.MVP.Name = $script.monparameter.Name 
LEFT JOIN $script.monmakingitem ON $script.monmakingitem.Name = $script.monparameter.Name
LEFT JOIN $cp.item_db AS ID1 ON ID1.Name = $script.monmakingitem.item1
LEFT JOIN $cp.item_db AS ID2 ON ID2.Name = $script.monmakingitem.item2
LEFT JOIN $cp.item_db AS ID3 ON ID3.Name = $script.monmakingitem.item3
LEFT JOIN $cp.item_db AS ID4 ON ID4.Name = $script.monmakingitem.item4
LEFT JOIN $cp.item_db AS ID5 ON ID5.Name = $script.monmakingitem.item5
LEFT JOIN $cp.item_db AS ID6 ON ID6.Name = $script.monmakingitem.item6
LEFT JOIN $cp.item_db AS ID7 ON ID7.Name = $script.monmakingitem.item7
LEFT JOIN $cp.item_db AS ID8 ON ID8.Name = $script.monmakingitem.item8
LEFT JOIN $cp.item_db AS MVPID1 ON MVPID1.Name = $script.MVP.itemName1
LEFT JOIN $cp.item_db AS MVPID2 ON MVPID2.Name = $script.MVP.itemName2
LEFT JOIN $cp.item_db AS MVPID3 ON MVPID3.Name = $script.MVP.itemName3
%s AND LV > 0
ORDER BY $script.monparameter.Name
");
/*DEFINE('COUNT_FULL_MOBS', "SELECT $script.monparameter.Name
FROM $script.monparameter
LEFT JOIN $script.monmakingitem ON $script.monmakingitem.Name = $script.monparameter.Name
LEFT JOIN $cp.item_db AS ID1 ON ID1.Name = $script.monmakingitem.item1
LEFT JOIN $cp.item_db AS ID2 ON ID2.Name = $script.monmakingitem.item2
LEFT JOIN $cp.item_db AS ID3 ON ID3.Name = $script.monmakingitem.item3
LEFT JOIN $cp.item_db AS ID4 ON ID4.Name = $script.monmakingitem.item4
LEFT JOIN $cp.item_db AS ID5 ON ID5.Name = $script.monmakingitem.item5
LEFT JOIN $cp.item_db AS ID6 ON ID6.Name = $script.monmakingitem.item6
LEFT JOIN $cp.item_db AS ID7 ON ID7.Name = $script.monmakingitem.item7
LEFT JOIN $cp.item_db AS ID8 ON ID8.Name = $script.monmakingitem.item8
%s AND LV > 0
ORDER BY $script.monparameter.Name
");*/
DEFINE('SHOW_FULL_MOBS', "SELECT $script.monparameter.Name, LV, HP, exp, jexp, atk1, atk2, $script.monparameter.def, mdef, 
property, scale, race, str, agi, vit, [int], dex, luk, 
item1, percent1, ID1.ID, item2, percent2, ID2.ID, item3, percent3, ID3.ID, item4, percent4, ID4.ID, 
item5, percent5, ID5.ID, item6, percent6, ID6.ID, item7, percent7, ID7.ID, item8, percent8, ID8.ID, 
$script.MVP.expPercent ,$script.MVP.itemName1 ,$script.MVP.itemPercent1, MVPID1.ID, 
$script.MVP.itemName2 ,$script.MVP.itemPercent2, MVPID2.ID, 
$script.MVP.itemName3 ,$script.MVP.itemPercent3, MVPID3.ID 
FROM $script.monparameter
LEFT JOIN $script.MVP ON $script.MVP.Name = $script.monparameter.Name 
LEFT JOIN $script.monmakingitem ON $script.monmakingitem.Name = $script.monparameter.Name
LEFT JOIN $cp.item_db AS ID1 ON ID1.Name = $script.monmakingitem.item1
LEFT JOIN $cp.item_db AS ID2 ON ID2.Name = $script.monmakingitem.item2
LEFT JOIN $cp.item_db AS ID3 ON ID3.Name = $script.monmakingitem.item3
LEFT JOIN $cp.item_db AS ID4 ON ID4.Name = $script.monmakingitem.item4
LEFT JOIN $cp.item_db AS ID5 ON ID5.Name = $script.monmakingitem.item5
LEFT JOIN $cp.item_db AS ID6 ON ID6.Name = $script.monmakingitem.item6
LEFT JOIN $cp.item_db AS ID7 ON ID7.Name = $script.monmakingitem.item7
LEFT JOIN $cp.item_db AS ID8 ON ID8.Name = $script.monmakingitem.item8
LEFT JOIN $cp.item_db AS MVPID1 ON MVPID1.Name = $script.MVP.itemName1
LEFT JOIN $cp.item_db AS MVPID2 ON MVPID2.Name = $script.MVP.itemName2
LEFT JOIN $cp.item_db AS MVPID3 ON MVPID3.Name = $script.MVP.itemName3
%s AND LV > 0 
ORDER BY $script.monparameter.Name
");
/*DEFINE('SHOW_FULL_MOBS', "SELECT $script.monparameter.Name, LV, HP, exp, jexp, atk1, atk2, $script.monparameter.def, mdef,
property, scale, race, str, agi, vit, [int], dex, luk,
item1, percent1, ID1.ID, item2, percent2, ID2.ID, item3, percent3, ID3.ID, item4, percent4, ID4.ID,
item5, percent5, ID5.ID, item6, percent6, ID6.ID, item7, percent7, ID7.ID, item8, percent8, ID8.ID
FROM $script.monparameter
LEFT JOIN $script.monmakingitem ON $script.monmakingitem.Name = $script.monparameter.Name
LEFT JOIN $cp.item_db AS ID1 ON ID1.Name = $script.monmakingitem.item1
LEFT JOIN $cp.item_db AS ID2 ON ID2.Name = $script.monmakingitem.item2
LEFT JOIN $cp.item_db AS ID3 ON ID3.Name = $script.monmakingitem.item3
LEFT JOIN $cp.item_db AS ID4 ON ID4.Name = $script.monmakingitem.item4
LEFT JOIN $cp.item_db AS ID5 ON ID5.Name = $script.monmakingitem.item5
LEFT JOIN $cp.item_db AS ID6 ON ID6.Name = $script.monmakingitem.item6
LEFT JOIN $cp.item_db AS ID7 ON ID7.Name = $script.monmakingitem.item7
LEFT JOIN $cp.item_db AS ID8 ON ID8.Name = $script.monmakingitem.item8
%s AND LV > 0
ORDER BY $script.monparameter.Name
");*/
DEFINE('SEARCH_MONSTER', "WHERE $script.monparameter.Name LIKE '%%%s%%'");
/*DEFINE('SEARCH_ITEM', "WHERE
(
$script.monmakingitem.item1 LIKE '%%%s%%' OR
$script.monmakingitem.item2 LIKE '%%%s%%' OR
$script.monmakingitem.item3 LIKE '%%%s%%' OR
$script.monmakingitem.item4 LIKE '%%%s%%' OR
$script.monmakingitem.item5 LIKE '%%%s%%' OR
$script.monmakingitem.item6 LIKE '%%%s%%' OR
$script.monmakingitem.item7 LIKE '%%%s%%' OR
$script.monmakingitem.item8 LIKE '%%%s%%' 
)");*/
DEFINE('SEARCH_ITEM', "WHERE
(
$script.monmakingitem.item1 LIKE '%%%s%%' OR
$script.monmakingitem.item2 LIKE '%%%s%%' OR
$script.monmakingitem.item3 LIKE '%%%s%%' OR
$script.monmakingitem.item4 LIKE '%%%s%%' OR
$script.monmakingitem.item5 LIKE '%%%s%%' OR
$script.monmakingitem.item6 LIKE '%%%s%%' OR
$script.monmakingitem.item7 LIKE '%%%s%%' OR
$script.monmakingitem.item8 LIKE '%%%s%%' OR
$script.MVP.itemName1 LIKE '%%%s%%' OR
$script.MVP.itemName2 LIKE '%%%s%%' OR
$script.MVP.itemName3 LIKE '%%%s%%'
)");

// whosonline.php
DEFINE('ONLINE_WITH_GM', "AND $cp.privilege.AID IS NOT NULL");
DEFINE('ONLINE_WITHOUT_GM', "AND $cp.privilege.AID IS NULL");

// Arguements: show/not show position, show gm/nongm, filter/not filter by map
DEFINE('SHOW_ONLINE', "SELECT tu_aid, tu_id, tu_email, tu_sex, tu_ip
FROM $user.t_user
LEFT JOIN $cp.privilege ON $user.t_user.tu_aid = $cp.privilege.AID
WHERE tu_state = 1
%s
%s
");

//equipment.php
DEFINE('GET_CHARACTER_ITEMS', "SELECT equipItem FROM $char.item
WHERE GID = %d");
if($CONFIG['aegis_version'] == 0 || $CONFIG['aegis_version'] == 2){
	DEFINE('GET_ALL_ITEMS', "USE [Script]
	SELECT [ID],[NAME],'ammo' AS table_name, Null as [SLOT] FROM [ammo]
	UNION
	SELECT [ID],[NAME],'armor' AS table_name, [SLOT] FROM [armor]
	UNION
	SELECT [ID],[NAME],'armorMB' AS table_name, [SLOT] FROM [armorMB]
	UNION
	SELECT [ID],[NAME],'armorTB' AS table_name, [SLOT] FROM [armorTB]
	UNION
	SELECT [ID],[NAME],'armorTM' AS table_name, [SLOT] FROM [armorTM]
	UNION
	SELECT [ID],[NAME],'armorTMB' AS table_name, [SLOT] FROM [armorTMB]
	UNION
	SELECT [ID],[NAME],'arrow' AS table_name, Null as [SLOT] FROM [arrow]
	UNION
	SELECT [ID],[NAME],'bothhand' AS table_name, [SLOT] FROM [bothhand]
	UNION
	SELECT [ID],[NAME],'bow' AS table_name, [SLOT] FROM [bow]
	UNION
	SELECT [ID],[NAME],'cannonball' AS table_name, Null as [SLOT] FROM [cannonball]
	UNION
	SELECT [ID],[NAME],'card' AS table_name, Null as [SLOT] FROM [card]
	UNION
	SELECT [ID],[NAME],'CashPointItem' AS table_name, Null as [SLOT] FROM [CashPointItem]
	UNION
	SELECT [ID],[NAME],'event' AS table_name, Null as [SLOT] FROM [event]
	UNION
	SELECT [ID],[NAME],'gun' AS table_name, [SLOT] FROM [gun]
	UNION
	SELECT [ID],[NAME],'heal' AS table_name, Null as [SLOT] FROM [heal]
	UNION
	SELECT [ID],[NAME],'special' AS table_name, Null as [SLOT] FROM [special]
	UNION
	SELECT [ID],[NAME],'ThrowWeapon' AS table_name, Null as [SLOT] FROM [ThrowWeapon]
	UNION
	SELECT [ID],[NAME],'weapon' AS table_name, [SLOT] FROM [weapon]
	UNION
	SELECT [ID],[NAME],'guest' AS table_name, Null as [SLOT] FROM [guest]");
}
else{
	DEFINE('GET_ALL_ITEMS', "USE [Script]
	SELECT [ID],[NAME],'armor' AS table_name, [SLOT] FROM [armor]
	UNION
	SELECT [ID],[NAME],'armorMB' AS table_name, [SLOT] FROM [armorMB]
	UNION
	SELECT [ID],[NAME],'armorTB' AS table_name, [SLOT] FROM [armorTB]
	UNION
	SELECT [ID],[NAME],'armorTM' AS table_name, [SLOT] FROM [armorTM]
	UNION
	SELECT [ID],[NAME],'armorTMB' AS table_name, [SLOT] FROM [armorTMB]
	UNION
	SELECT [ID],[NAME],'arrow' AS table_name, Null as [SLOT] FROM [arrow]
	UNION
	SELECT [ID],[NAME],'bothhand' AS table_name, [SLOT] FROM [bothhand]
	UNION
	SELECT [ID],[NAME],'bow' AS table_name, [SLOT] FROM [bow]
	UNION
	SELECT [ID],[NAME],'card' AS table_name, Null as [SLOT] FROM [card]
	UNION
	SELECT [ID],[NAME],'event' AS table_name, Null as [SLOT] FROM [event]
	UNION
	SELECT [ID],[NAME],'heal' AS table_name, Null as [SLOT] FROM [heal]
	UNION
	SELECT [ID],[NAME],'special' AS table_name, Null as [SLOT] FROM [special]
	UNION
	SELECT [ID],[NAME],'weapon' AS table_name, [SLOT] FROM [weapon]
	UNION
	SELECT [ID],[NAME],'guest' AS table_name, Null as [SLOT] FROM [guest]");
}
// roster.php
DEFINE('GET_CHARACTER_FROM_USER', "SELECT GID, charname, job, clevel, joblevel, money, bodypalette, head, headpalette, accessory, accessory2, accessory3, weapon, shield, effectstate
FROM $char.charinfo
WHERE AID = %d ORDER BY charname
");
DEFINE('CHECK_SEX_AID', "SELECT sex FROM $login.account WHERE [AID] = '%s'");

// exptable.php
if($CONFIG['aegis_version'] == 0){
	DEFINE('GET_ALL_EXP', "USE [Script]
	select levels.level,
	ExpParameter.exp as ExpParameterExp,
	ExpParameter2.exp as ExpParameter2Exp,
	ExpParameter3.exp as ExpParameter3Exp,
	NoviceJobExpParameter.exp as NoviceJobExpParameterExp,
	FirstJobExpParameter.exp as FirstJobExpParameterExp,
	SecondJobExpParameter.exp as SecondJobExpParameterExp,
	NoviceJobExpParameter2.exp as NoviceJobExpParameter2Exp,
	FirstJobExpParameter2.exp as FirstJobExpParameter2Exp,
	SecondJobExpParameter2.exp as SecondJobExpParameter2Exp,
	FirstJobExpParameter3.exp as FirstJobExpParameter3Exp,
	ThirdJobExpParameter.exp as ThirdJobExpParameterExp
	from
	(
				select [level]
				from ExpParameter
				union
				select [level]
				from ExpParameter2
				union
				select [level]
				from ExpParameter3
				union
				select [level]
				from FirstJobExpParameter
				union
				select [level]
				from FirstJobExpParameter2
				union
				select [level]
				from FirstJobExpParameter3
				union
				select [level]
				from SecondJobExpParameter
				union
				select [level]
				from SecondJobExpParameter2
				union
				select [level]
				from NoviceJobExpParameter
				union
				select [level]
				from NoviceJobExpParameter2
				union
				select [level]
				from ThirdJobExpParameter
	) as levels
	left outer join ExpParameter on ExpParameter.level = levels.level
	left outer join ExpParameter2 on ExpParameter2.level = levels.level
	left outer join ExpParameter3 on ExpParameter3.level = levels.level
	left outer join FirstJobExpParameter on FirstJobExpParameter.level = levels.level
	left outer join FirstJobExpParameter2 on FirstJobExpParameter2.level = levels.level
	left outer join FirstJobExpParameter3 on FirstJobExpParameter3.level = levels.level
	left outer join SecondJobExpParameter on SecondJobExpParameter.level = levels.level
	left outer join SecondJobExpParameter2 on SecondJobExpParameter2.level = levels.level
	left outer join NoviceJobExpParameter on NoviceJobExpParameter.level = levels.level
	left outer join NoviceJobExpParameter2 on NoviceJobExpParameter2.level = levels.level
	left outer join ThirdJobExpParameter on ThirdJobExpParameter.level = levels.level");
}
elseif($CONFIG['aegis_version'] == 0){
	DEFINE('GET_ALL_EXP', "USE [Script]
	select levels.level,
	ExpParameter.exp as ExpParameterExp,
	ExpParameter2.exp as ExpParameter2Exp,
	NoviceJobExpParameter.exp as NoviceJobExpParameterExp,
	FirstJobExpParameter.exp as FirstJobExpParameterExp,
	SecondJobExpParameter.exp as SecondJobExpParameterExp,
	NoviceJobExpParameter2.exp as NoviceJobExpParameter2Exp,
	FirstJobExpParameter2.exp as FirstJobExpParameter2Exp,
	SecondJobExpParameter2.exp as SecondJobExpParameter2Exp,
	FirstJobExpParameter3.exp as FirstJobExpParameter3Exp,
	ThirdJobExpParameter.exp as ThirdJobExpParameterExp
	from
	(
				select [level]
				from ExpParameter
				union
				select [level]
				from ExpParameter2
				union
				select [level]
				from FirstJobExpParameter
				union
				select [level]
				from FirstJobExpParameter2
				union
				select [level]
				from FirstJobExpParameter3
				union
				select [level]
				from SecondJobExpParameter
				union
				select [level]
				from SecondJobExpParameter2
				union
				select [level]
				from NoviceJobExpParameter
				union
				select [level]
				from NoviceJobExpParameter2
				union
				select [level]
				from ThirdJobExpParameter
	) as levels
	left outer join ExpParameter on ExpParameter.level = levels.level
	left outer join ExpParameter2 on ExpParameter2.level = levels.level
	left outer join FirstJobExpParameter on FirstJobExpParameter.level = levels.level
	left outer join FirstJobExpParameter2 on FirstJobExpParameter2.level = levels.level
	left outer join FirstJobExpParameter3 on FirstJobExpParameter3.level = levels.level
	left outer join SecondJobExpParameter on SecondJobExpParameter.level = levels.level
	left outer join SecondJobExpParameter2 on SecondJobExpParameter2.level = levels.level
	left outer join NoviceJobExpParameter on NoviceJobExpParameter.level = levels.level
	left outer join NoviceJobExpParameter2 on NoviceJobExpParameter2.level = levels.level
	left outer join ThirdJobExpParameter on ThirdJobExpParameter.level = levels.level");
}
else {
	DEFINE('GET_ALL_EXP', "USE [Script]
	select levels.level,
	ExpParameter.exp as ExpParameterExp,
	ExpParameter2.exp as ExpParameter2Exp,
	NoviceJobExpParameter.exp as NoviceJobExpParameterExp,
	FirstJobExpParameter.exp as FirstJobExpParameterExp,
	SecondJobExpParameter.exp as SecondJobExpParameterExp,
	NoviceJobExpParameter2.exp as NoviceJobExpParameter2Exp,
	FirstJobExpParameter2.exp as FirstJobExpParameter2Exp,
	SecondJobExpParameter2.exp as SecondJobExpParameter2Exp
	from
	(
				select [level]
				from ExpParameter
				union
				select [level]
				from ExpParameter2
				union
				select [level]
				from FirstJobExpParameter
				union
				select [level]
				from FirstJobExpParameter2
				union
				select [level]
				from SecondJobExpParameter
				union
				select [level]
				from SecondJobExpParameter2
				union
				select [level]
				from NoviceJobExpParameter
				union
				select [level]
				from NoviceJobExpParameter2
	) as levels
	left outer join ExpParameter on ExpParameter.level = levels.level
	left outer join ExpParameter2 on ExpParameter2.level = levels.level
	left outer join FirstJobExpParameter on FirstJobExpParameter.level = levels.level
	left outer join FirstJobExpParameter2 on FirstJobExpParameter2.level = levels.level
	left outer join SecondJobExpParameter on SecondJobExpParameter.level = levels.level
	left outer join SecondJobExpParameter2 on SecondJobExpParameter2.level = levels.level
	left outer join NoviceJobExpParameter on NoviceJobExpParameter.level = levels.level
	left outer join NoviceJobExpParameter2 on NoviceJobExpParameter2.level = levels.level");	
}
// maplist.php
DEFINE('GET_ALL_MAPS', "USE [IPInfo]
SELECT * FROM Mapinfo ORDER BY ZSID");

?>
