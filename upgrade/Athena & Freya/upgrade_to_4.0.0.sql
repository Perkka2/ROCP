DROP TABLE `cp_admin_announce`;
DROP TABLE `cp_gm_announce`;
DROP TABLE `cp_user_announce`;
DROP TABLE `cp_access_log`;
DROP TABLE `cp_admin_log`;
DROP TABLE `cp_anti_bot`;
DROP TABLE `cp_ban_log`;
DROP TABLE `cp_exploit_log`;
DROP TABLE `cp_ladder_ignore`;
DROP TABLE `cp_privilege`;
DROP TABLE `cp_mailbox`;
DROP TABLE `cp_money_log`;
DROP TABLE `cp_register_log`;
DROP TABLE `cp_user_log`;
DROP TABLE `cp_skins`;
DROP TABLE IF EXISTS `cp_athenaitem`;
DELETE FROM `login` WHERE account_id = '20';

#Change to the new roster format
UPDATE `char` SET char_num = char_num + 10 WHERE char_num > 2;

#Create a new database named cp, go to the install folder and refer to those queries to install the new tables.