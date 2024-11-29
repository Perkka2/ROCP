<?php
$CONFIG['db_host'] 				=			'127.0.0.1';		// SQL Host
$CONFIG['db_username'] 			=			'';			// SQL User
$CONFIG['db_password'] 			=			'';			// SQL Password
$CONFIG['cp_db_name'] 			=			'cp';			// SQL CP Database name
$CONFIG['db_name'] 				=			'';			// SQL Ragnarok Database name (Athena Only)
$CONFIG['db_logs']				=			'';			// SQL Log Database name (Athena SVN only)
$CONFIG['passphrase'] 			=			'';			// Passphrase to encrypt various strings (Will render current emblems useless if changed.)
$CONFIG['server_type'] 			=			'0';			// Server Type (0 = Aegis (MSSQL), 1 = oAthena (MySQL), 2 = eAthena(MySQL), 3 = Freya(MySQL))
$CONFIG['server_db_conn'] 		=			'1';			// Server DB Connection (Aegis Only) 0 = mssql_connect, 1 = odbc_connect, 2 = sqlsrv_connect
$CONFIG['odbc_datasource'] 		=			'Aegis';			// ODBC Datasource (Needed if Server Database Connection is set to 1)
$CONFIG['aegis_version'] 		=			'1';		// 1 = ep8 0 = newer
$CONFIG['panel_name']			=			'Ragnarok Online Control Panel'; 	// Page title replaced with server type
$CONFIG['language'] 			=			'english';		// Language
$CONFIG['backup_interval'] 		=			'24';			// Backup interval reminder (Hours)
$CONFIG['default_skin'] 		=			'default';		// Default skin to users.
$CONFIG['check_server'] 		=			'1';			// Check Server?
$CONFIG['maintenance'] 			=			'0';			// Maintenance?
$CONFIG['accip'] 				=			'127.0.0.1';		// Account/Login Server IP
$CONFIG['accport'] 				=			'6900';			// Account/Login Server Port
$CONFIG['charip'] 				=			'127.0.0.1';		// Char Server IP
$CONFIG['charport'] 			=			'7000';			// Char Server Port
$CONFIG['mapip'] 				=			'127.0.0.1';		// Zone/Map Server IP
$CONFIG['mapport'] 				=			'4500';			// Zone/Map Server Port Start (Needs consecutive ports for multiple zones)
$CONFIG['mapcount'] 			=			'5';			// Zone/Map Server Count
$CONFIG['server_name'] 			=			'RO';			// Server Name
$CONFIG['do_gzip_compress'] 	=			'0';			// Use GZIP Compression?
$CONFIG['use_md5'] 				=			'0';			// Use MD5 Encryption?
$CONFIG['results_per_page'] 	=			'100';			// Number of results per page.
$CONFIG['validchars'] 			=			'/^[\w\s\.!-]+$/';	// Valid characters in GET/POST inputs (Regex)
$CONFIG['agit_days'] 			=			'0,3,6';		// Guild War Days (ex. 0,3,6 for Sun, Wed, Sat) - Hours are aligned to these days
$CONFIG['agit_start'] 			=			'1300';			// Guild War Start (Ex. 1300)
$CONFIG['agit_end'] 			=			'1400';			// Guild War End (Ex. 1400)
$CONFIG['agit_offset']			=			'0';			// Guild War Time offset (24 hour time) for displaying times
$CONFIG['save_type'] 			=			'1';			// Save Type
$CONFIG['minimum_transfer'] 	=			'20';			// Minimum Character Level to Transfer Money
$CONFIG['sex_change'] 			=			'0';			// Allow Sex Changes?
$CONFIG['max_announce'] 		=			'2';			// Max Announcements to View?
$CONFIG['debug'] 				=			'1';			// Query Debug?
$CONFIG['log_select'] 			=			'0';			// Log SELECT queries?
$CONFIG['log_insert'] 			=			'1';			// Log INSERT queries?
$CONFIG['log_update'] 			=			'1';			// Log UPDATE queries?
$CONFIG['log_delete'] 			=			'1';			// Log DELETE queries?
$CONFIG['website'] 				=			'http://';		// Website Location
$CONFIG['forums_location'] 		=			'http://';		// Forums Location
$CONFIG['patch_location'] 		=			'http://';		// Patch Location
$CONFIG['irc_channel'] 			=			'irc://';		// IRC Location
$CONFIG['cp_location'] 			=			'http://';		// CP Location (http://____)
$CONFIG['max_characters'] 		=			'9';			// Maximum Characters Detected by Script
$CONFIG['ladder_limit'] 		=			'100';			// Ladder Display Limit
$CONFIG['display_guild_limit'] 	=			'50';			// Guild Ladder Display Limit
$CONFIG['register'] 			=			'1';			// Registration On?
$CONFIG['register_type'] 		=			'0';			// Method of Registration: 0 = Normal, 1 = Email Auth, 2 = Admin Auth
$CONFIG['default_level']		=			'0';			// Default GM level of registered accounts (Athena/Freya Only)
$CONFIG['secure_mode'] 			=			'0';			// Use security codes in registration?
$CONFIG['sim_pass']				=			'0';			// Stops user from registering with a password similar to their username
$CONFIG['max_per_ip'] 			=			'0';			// Maximum accounts per IP? (0 = Unlimited)
$CONFIG['max_per_email'] 		=			'1';			// Maximum accounts per Email? (0 = Unlimited)
$CONFIG['max_accounts'] 		=			'0';			// Maximum Accounts on Server (0 = Unlimited)
$CONFIG['inactive_days'] 		=			'30';			// Days until account is considered inactive (Athena Only, 0 = Disable this feature)
$CONFIG['smtp_host'] 			=			'';			// Email SMTP Host
$CONFIG['smtp_auth']			=			'0';			// Whether or not to authenticate SMTP
$CONFIG['smtp_login']			= 			'';			// SMTP Login (If auth is on)
$CONFIG['smtp_pass']			= 			'';			// SMTP Password (If auth is on)
$CONFIG['sendmail_name'] 		=			'';			// Display Name on Email
$CONFIG['sendmail_from'] 		=			'';			// SMTP Email Address to use
$CONFIG['admin_colour'] 		=			'FF0000';		// Colour used for Admin
$CONFIG['gm_colour'] 			=			'0000FF';		// Colour used for GM
$CONFIG['game_gm_colour'] 		=			'00FFFF';		// Colour used for Game GM
$CONFIG['adjust_rate'] 			=			'0';			// 1 if you are using Stored Procedure to increase rates, otherwise 0. (Aegis Only)
$CONFIG['exp_rate'] 			=			'1';			// EXP Rate
$CONFIG['jexp_rate'] 			=			'1';			// JEXP Rate
$CONFIG['drop_rate'] 			=			'1';			// Drop Rate
?>
