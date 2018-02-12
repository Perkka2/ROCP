<?php

/**
 * All global functions are declared here
 * @package Ragnarok Online Control Panel
 * @author Andrew Chung
 * @copyright 2003 - 2004 Andrew Chung
 */

/**
 * @return void
 * @param string $login The login name
 * @param string $password The password
 * @param string $sex M or F as gender
 * @param string $email The account's email
 * @param string $level The account's gm level
 * @param boolean $skip_encrypt Whether or not to apply MD5 Encryption (If MD5 is on)
 * @desc Adds the account to the login database. If $CONFIG_use_md5 is true, it will MD5, unless $skip_encrypt is true
*/
function add_account($login, $password, $sex, $email, $level, $skip_encrypt = false) {
	// Input the "raw" password, will be encrypted if necessary

	// Adds a new account to server
	global $CONFIG_server_type, $CONFIG_server_name, $CONFIG_website, $CONFIG_forums_location,
	$CONFIG_patch_location, $CONFIG_irc_channel, $CONFIG_cp_location, $CONFIG_use_md5, $lang;

	//$original_password = $password; <- What the hell was the point of that???

	if ($CONFIG_use_md5 && !$skip_encrypt) {
		$password = md5($password);
	}

	if ($CONFIG_server_type == 0) {
		$sex = strtoupper($sex) == "F"? 0 : 1;
		// Add to login table
		$query = sprintf(ADD_ACCOUNT, $login, $password);
		$result = execute_query($query, "functions.php");
		// Get last AID added
		$query = "SELECT AID FROM nLogin.dbo.login WHERE ID = '$login'";
		$result = execute_query($query, "functions.php");
		$line = $result->FetchRow();
		$register_aid = $line[0];
		// Add to account table
		$query = sprintf(ADD_ACCOUNT2, $register_aid, $login, $sex, $email);
		$result = execute_query($query, "functions.php");
		// Insert to user.dbo.t_user
		$query = sprintf(INSERT_T_USER, $register_aid, $login, $email, $sex);
		$result = execute_query($query, "functions.php");
	}
	else {
		// Add to login table
		$query = sprintf(ADD_ACCOUNT, $login, $password, $sex, $email, $level);
		$result = execute_query($query, "functions.php");
	}
	// Inserts the ip, time, as well as account that was registered into register log.
	$ip = ip2long($_SERVER['REMOTE_ADDR']);
	$query = sprintf(ADD_REGISTER_ENTRY, $login, $ip, $email,$level);
	$result = execute_query($query, "functions.php", 0, 0, true);
	$message = sprintf($lang['accountadded'], $CONFIG_server_name, $login,
	$CONFIG_website, $CONFIG_forums_location, $CONFIG_patch_location, $CONFIG_irc_channel,
	$CONFIG_cp_location, $CONFIG_server_name);
	SendMail($login, $email, "Your $CONFIG_server_name account is ready", $message);
}

/**
 * @return void
 * @param string $contactname The target's name
 * @param string $contactemail The target's email
 * @param string $subject Subject
 * @param string $message Message
 * @desc Sends an email to the email specified
*/
function SendMail($contactname, $contactemail, $subject, $message) {
	require 'config.php';
	require 'extract.inc';

	if ($CONFIG_smtp_host != "") {
		require_once 'phpmailer/class.phpmailer.php';
		$mail = new PHPMailer();

		$mail->IsSMTP();                                   // send via SMTP
		$mail->Host     = $CONFIG_smtp_host; // SMTP servers
		if ($CONFIG_smtp_auth == 0) {
			$mail->SMTPAuth = false;     // turn off SMTP authentication
		}
		else {
			$mail->SMTPAuth = true;     // turn on SMTP authentication
		}
		$mail->Username = $CONFIG_smtp_login;  // SMTP username
		$mail->Password = $CONFIG_smtp_pass; // SMTP password

		$mail->From     = $CONFIG_sendmail_from;
		$mail->FromName = $CONFIG_sendmail_name;

		$mail->AddAddress($contactemail, $contactname);

		$mail->Subject  =  $subject;
		$mail->Body     =  $message;
		$mail->Priority = 1;

		if(!$mail->Send()) {
		   redir("index.php", "Message was not sent: " . $mail->ErrorInfo);
		}
	}
}

/**
 * @return string
 * @param string $input_string
 * @desc Escapes the string, using ' for MSSQL, and \ for MySQL
*/
function add_escape($input_string) {
	global $CONFIG_server_type;
	if ($CONFIG_server_type == 0) {
		// Eliminates the need for magic_quotes to be disabled
		$input_string = str_replace("\\", "", $input_string);
		return str_replace("'","''",$input_string);
	}
	else {
		return addslashes($input_string);
	}
}

function del_escape($input_string) {
	global $CONFIG_server_type;
	if ($CONFIG_server_type == 0) {
		$input_string = str_replace("\\", "", $input_string);
		return str_replace("''","'",$input_string);
	}
	else {
		return stripslashes($input_string);
	}
}

function highlight_search_term($input_string, $search_term) {
	// Changed by Maldiablo
	if ($input_string && $search_term) {
		$position = strpos(strtolower($input_string), strtolower($search_term));
	}
	// End of changes
	if ($position !== FALSE) {
		$first_part = substr($input_string, 0, $position);
		$middle_part = "<font color=green>" . substr($input_string, $position, strlen($search_term));
		$final_part = "</font>" . substr($input_string, $position + strlen($search_term));
		$output = $first_part . $middle_part . $final_part;
	}
	else {
		$output = $input_string;
	}
	return $output;
}

function check_auth ($input_string) {
	global $access, $STORED_login, $STORED_level;
	if (checkbasename($input_string)) {
		add_exploit_entry("Tried to access $input_string", 1);
		redir("../index.php", "Access Denied");
	}
	else {
		$page_string = basename($input_string);
		if ($access[$page_string] == -1) {
			redir("index.php", "This page has been disabled by the Administrator.");
		}
		elseif (!IsSET($access[$page_string])) {
			redir("index.php", "Access Controls for this page has not been set.");
		}
		else {
			if ($STORED_level < $access[$page_string]) {
				if ($access[$page_string] > 1) {
					add_exploit_entry("Tried to access $input_string", 1);
					redir("index.php", "Access Denied");
				}
				else {
					redir("login.php", "You must be logged on to access this page!");
				}
			}
		}
	}
}

function checkbasename($name) {
	return (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']));
}

function redir($page, $msg, $time = 5) {
	global $STORED_skin, $start_time, $STORED_level, $queries, $logged_in, $lang,
	$debug_message, $cp_version;
	require 'config.php';
	require 'extract.inc';
	/*
	<head>
		<meta http-equiv=\"refresh\" content=\"$time;url=$page\">
	</head>
	*/
	echo "
	<br />
	<table width=\"250\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" class=\"redir\">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr align=\"center\">
			<td class=\"mytext\">
				<b>$msg</b><br />To continue, please <a href='$page'>click here</a><br />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	</table>
	";
	require 'footer.inc';
	exit();
}

/**
 * @return void
 * @param int $width Width of the table (%)
 * @desc Displays the table header
*/
function EchoHead($width = "") {
	//echo "<table width=\"$width%\" class=\"mytable\" cellpadding=\"0\" align=\"center\">	";
	echo "<table class=\"contentTable\">";
}

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * @return boolean
 * @param string $test_string The string to be validated
 * @desc Returns true if the string follows the regular expression declared in $CONFIG['validchars']
*/
function validate_string($test_string) {
	global $CONFIG_validchars;
	if (preg_match($CONFIG_validchars, $string)) {
		return true;
	}
	else {
		return false;
	}

}

function generate_breaks($input_string) {
	$message_data = explode("\r\n", $input_string);
	for ($i = 0; $i < sizeof($message_data); $i++) {
		$final_message .= $message_data[$i] . "<br>";
	}
	$final_message = substr($final_message, 0, strlen($final_message) - 4);
	return $final_message;
}

/**
 * @return resource
 * @param string $input_query The SQL Query to be executed
 * @param string $page_source The page which the query is called from
 * @param int $limit The maximum number of rows selected from the query
 * @param int $offset The number of rows to skip in a SELECT query
 * @param boolean $skip_log Whether or not to skip the query logging process
 * @desc Validates, Executes, Logs a query, returning an error message upon failure, a result identified on success.
*/
function execute_query($input_query, $page_source = 'none.php', $limit = 0, $offset = 0, $skip_log = false) {
	global $debug_message, $queries, $link, $CONFIG_log_select, $CONFIG_log_insert,
	$CONFIG_log_update, $CONFIG_log_delete, $CONFIG_debug, $CONFIG_passphrase,
	$total_execution, $STORED_login;

	$start_time = getmicrotime();
	$analyze_query = strtolower(htmlspecialchars($input_query)); // analyzes the query for illegal inputs
	// disables the use of UNION SELECT
	$banned_combos = array(
	"UNION SELECT" => "union",
	"xp_stored procedure" => "xp_",
	"comment" => "--"
	);
	foreach ($banned_combos as $index => $value) {
		if (strstr($analyze_query, $value) == true) {
			add_exploit_entry("Attempted to inject a $index into a query!");
			redir("index.php", "Invalid query to be executed!");
		}
	}
	$queries++;
	$debug_message .= "\n\t<tr>\n\t\t<td><b>$queries</b></td>\n\t\t<td>";
	if (!$skip_log) {
		if (strstr($analyze_query, 'select') !== false && $CONFIG_log_select) {
			$debug_message .= "Logging: ";
			$log_query = true;
		}
		if (strstr($analyze_query, 'insert') !== false && $CONFIG_log_insert) {
			$debug_message .= "Logging: ";
			$log_query = true;
		}
		elseif (strstr($analyze_query, 'update') !== false && $CONFIG_log_update) {
			$debug_message .= "Logging: ";
			$log_query = true;
		}
		elseif (strstr($analyze_query, 'delete') !== false && $CONFIG_log_delete) {
			$debug_message .= "Logging: ";
			$log_query = true;
		}
		else {
			$debug_message .= "Executing: ";
			$log_query = false;
		}
	}
	else {
		$debug_message .= "Executing: ";
		$log_query = false;
	}
	if ($limit == 0 && $offset == 0) {
		$result = $link->Execute($input_query) or die("Query (<b>$input_query</b>) failed: " . $link->ErrorMsg());
	}
	else {
		$result = $link->SelectLimit($input_query, $limit, $offset) or die("Query (<b>$input_query</b>) failed: " . $link->ErrorMsg());
	}

	// Displays each query (SELECT, INSERT, UPDATE, DELETE)
	$debug_message .= "</td>\n\t\t<td>";
	$execute_time = (getmicrotime() - $start_time);
	$total_execution += $execute_time;

	if ($CONFIG_debug) {
		$rows = $result->RowCount();
		$debug_message .= "$page_source</td>\n\t\t<td width=50%>";
		$debug_message .= "$input_query</td>\n\t\t<td>$execute_time</td>\n\t\t</tr>";
	}

	if ($log_query) {
		// replaces each line break with a space
		$input_query = str_replace("\r\n", " ", $input_query);
		add_query_entry($page_source, $input_query);
	}
	return $result;
}

function is_server_online() {
	global $CONFIG_check_server, $CONFIG_maintenance;
	if (!$CONFIG_check_server or $CONFIG_maintenance) {
		return false;
	}
	$query = CHECK_STATUS;
	$result = execute_query($query, "functions.php");
	$line = $result->FetchRow();
	// Pull values from DB
	$acc = $line[1];
	$char = $line[2];
	$map = $line[3];
	if (!$acc || !$char || !$map) {
		return false;
	}
	else {
		return true;
	}
}

function get_level($account_id) {
	$query = sprintf(GET_LEVEL, $account_id);
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
		return $result->fields[0];
	}
	else {
		return 1;
	}
}

function get_gmlevel($account_id) {
	$query = sprintf(GET_GMLEVEL, $account_id);
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
		return $result->fields[0];
	}
	else {
		return 0;
	}
}

/**
 * @return boolean
 * @param string $test The String to be validated
 * @desc Returns true if the string is alphanumeric.
*/
function isalphanumeric($test) {
	return !(preg_match("/[^a-z,A-Z,0-9]/", $test));
}

function authenticate ($login_username, $login_password) {
	/* Returns the following privileges:
	0: Fail
	1: User
	2: In-game GM
	3: GM
	4: Admin
	*/
	global $CONFIG_passphrase, $CONFIG_use_md5, $CONFIG_server_type;
	if ($CONFIG_server_type > 0) {
		if ($login_username == 's1' or $login_username == 's2' or $login_username == 's3' or $login_username == 's4' or $login_username == 's5') {
			return 0;
		}
	}
	if ($CONFIG_use_md5) {
		$query = sprintf(AUTH_MD5, $login_username, $login_password);
	}
	else {
		$query = sprintf(AUTH, $login_username, $login_password);
	}
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
		return get_level($result->fields[0]);
	}
	else {
		return 0;
	}
}

function privilege_string($access_level) {
	if ($access_level == -1) return "<font color=red>Disabled</font>";
	$access_string = explode("\r\n", file_get_contents("access.def"));
	return $access_string[$access_level];
}

/**
 * @return string
 * @param int $class_index The class number
 * @desc Returns the full class name, defined in class.def & class_advanced.def & class_baby.def
 * Baby support added by Vich
*/
function determine_class ($class_index) {
	if (($class_index > 4000) &&  ($class_index < 4023)) {
		$class = explode("\r\n", file_get_contents("class_advanced.def"));
		return $class[$class_index - 4001];
	}
	else if  ($class_index >= 4023) {
		$class = explode("\r\n", file_get_contents("class_baby.def"));
		return $class[$class_index - 4023];
	}
	else {
		$class = explode("\r\n",file_get_contents("class.def"));
		return $class[$class_index];
	}
}

/**
 * @return string
 * @param int $castle_index The castle number
 * @desc Returns the castle name as defined in guild_castles.def
*/
function determine_castle ($castle_index) {
	$castle_name = explode("\r\n", file_get_contents("guild_castles.def"));
	return $castle_name[$castle_index];
}

function determine_config_desc ($conf_index) {
	$conf_name = explode("\r\n", file_get_contents("conf.def"));
	return $conf_name[$conf_index];
}

function edit_config($edit_index, $edit_value) {
	require 'config.php';
	// Write the current config.php
	$write = fopen("config.php", "w");
	fwrite($write, "<?php\r\n");
	$comment_index = 0;
	foreach ($CONFIG as $config_index => $config_value) {
		if ($config_index == $edit_index) {
			// element of array is the one being edited
			if ($config_index == "server_rules") {
				$write_value = generate_breaks($edit_value);
				$new_rules = htmlspecialchars($write_value);
			}
			else {
				$write_value = $edit_value;
			}
		}
		else {
			// not the edited index, write the normal value
			$write_value = $config_value;
		}
		// Adjusts the tabbing for cleaner output
		if (strlen($config_index) < 12) {
			$tabs = "\t\t\t";
		}
		elseif (strlen($config_index) < 20) {
			$tabs = "\t\t";
		}
		else {
			$tabs = "\t";
		}

		if (strlen($write_value) < 5) {
			$comment_tabs = "\t\t\t";
		}
		elseif (strlen($write_value) < 13) {
			$comment_tabs = "\t\t";
		}
		elseif (strlen($write_value) < 21) {
			$comment_tabs = "\t";
		}
		else {
			$comment_tabs = "";
		}

		$comment = determine_config_desc($comment_index);
		$write_string = "\$CONFIG['$config_index'] $tabs=\t\t\t'$write_value';$comment_tabs// $comment\r\n";
		fwrite($write, $write_string);
		$comment_index++;
	}
	fwrite($write, "?>");
	// Close config.php
	fclose($write);
}

// The following functions are for logging purposes

/**
 * @return void
 * @param string $log_action
 * @desc Adds the specified string into the access log
*/
function add_access_entry($log_action) {
	// Different arguments
	// 1: Went to a page that they weren't allowed to be.
	// 2: Logged in as a GM/Admin
	global $STORED_login;
	$log_source = $_SERVER['REMOTE_ADDR'];
	$query = sprintf(ADD_ACCESS_ENTRY, $log_source, $log_action);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

/**
 * @return void
 * @param string $log_action
 * @desc Adds the specified string into the admin log
*/
function add_admin_entry($log_action) {
	global $STORED_login;
	$log_account = $STORED_login;
	$query = sprintf(ADD_ADMIN_ENTRY, $log_account, $log_action);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

/**
 * @return void
 * @param string $banned_account
 * @param string $log_reason
 * @desc Adds the banned account name and reason into the ban log
*/
function add_ban_entry($banned_account, $log_reason) {
	global $STORED_login;
	$log_account = $STORED_login;
	$log_reason = "Banned: " . $log_reason;
	$query = sprintf(ADD_BAN_ENTRY, $log_account, $banned_account, $log_reason);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

function add_exploit_entry($log_action) {
	global $STORED_login;
	$log_account = $STORED_login;
	if ($log_account == "") {
		$log_account = $_SERVER['REMOTE_ADDR'];
	}
	$query = sprintf(ADD_EXPLOIT_ENTRY, $log_account, $log_action);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

function add_money_entry($from, $to, $log_action) {
	global $CONFIG_passphrase;
	$query = sprintf(CHECK_LOG_CHAR_ID, $CONFIG_passphrase, $from);
	$result = execute_query($query, 'functions.php', 0, 0, true);
	$from = $result->fields[0];

	$query = sprintf(CHECK_LOG_CHAR_ID, $CONFIG_passphrase, $to);
	$result = execute_query($query, 'functions.php', 0, 0, true);
	$to = $result->fields[0];

	$query = sprintf(ADD_MONEY_ENTRY, $from, $to, $log_action);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

function add_query_entry($source, $log_query) {
	global $STORED_login, $link;
	$log_account = $STORED_login;
	$log_ip = $_SERVER['REMOTE_ADDR'];
	$log_query = add_escape($log_query);
	$query = sprintf(ADD_QUERY_ENTRY, $log_account, $log_ip, $source, $log_query);
	$result = $link->Execute($query) or die("Query ($query) failed: " . $link->ErrorMsg());
}

function add_unban_entry($unbanned_account, $log_reason) {
	global $STORED_login;
	$log_account = $STORED_login;
	$log_reason = "Unbanned: " . $log_reason;
	$query = sprintf(ADD_UNBAN_ENTRY, $log_account, $unbanned_account, $log_reason);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

function add_user_entry($log_action) {
	global $STORED_login;
	$log_account = $STORED_login;
	$query = sprintf(ADD_USER_ENTRY, $log_account, $log_action);
	$result = execute_query($query, 'functions.php', 0, 0, true);
}

// End logging functions

function GetUserCount() {
	// returns the number of users online
	$query = GET_ONLINE;
	$result = execute_query($query, "functions.php");
	return $result->fields[0];
}

function GetAccountCount() {
	// returns the number of accounts that are not for the server
	$query = GET_ACC_COUNT;
	$result = execute_query($query, 'functions.php');
	return $result->fields[0];
}

function GetCharacterCount() {
	// returns the number of characters on server
	$query = GET_CHAR_COUNT;
	$result = execute_query($query, 'functions.php');
	return $result->fields[0];
}
function GetZenyCount() {
	$query = GET_ZENY_COUNT;
	$result = execute_query($query, 'functions.php');
	return $result->fields[0];
}

/**
 * @return boolean
 * @param int $input_account_id
 * @desc Returns true if the account is online in-game. Otherwise, returns false.
*/
function is_online ($input_account_id) {
	// returns whether or not account is online
	$query = sprintf(IS_ONLINE, $input_account_id);
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
		return true;
	}
	else {
		return false;
	}

}

function GetGuildCount() {
	// returns the number of guilds on server
	$query = GET_GUILD_COUNT;
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
		$line = $result->FetchRow();
		return $line[0];
	}
	else {
		return 0;
	}
}

function ItemName_To_ItemID ($input_item_name) {
	global $athena_db;
	// Converts Item Name to Item #
	$query = sprintf(ITEMNAME_TO_ITEMID, $input_item_name);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return 0;
	}
	else {
		$line = $result->FetchRow();
		return $line[0];
	}
}

function ItemID_To_ItemName ($input_item_ID) {
	if ($input_item_ID == 0) {
		return "";
	}
	// Converts Item # to Item Name
	$query = sprintf(ITEMID_TO_ITEMNAME, $input_item_ID);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		if ($input_item_ID == 0 or $input_item_ID == 2 or $input_item_ID == 255) {
			return "";
		}
		elseif ($input_item_ID > 1280) {
			return "";
		}
		else {
			return "Unknown Item $input_item_ID";
		}
	}
	else {
		$line = $result->FetchRow();
		return $line[0];
	}
}

function CharName_To_CharID ($input_char_name) {
	// Converts Char Name to Char ID
	$query = sprintf(CHARNAME_TO_CHARID, add_escape($input_char_name));
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return 0;
	}
	else {
		return $result->fields[0];
	}
}

/**
 * @return string
 * @param int $input_char_id Character ID
 * @desc Returns the character name, for the given character ID
*/
function CharID_To_CharName ($input_char_id) {
	if ($input_char_id == 0) {
		return "";
	}
	// Converts Char ID to Char Name
	$query = sprintf(CHARID_TO_CHARNAME, $input_char_id);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return "";
	}
	else {
		return del_escape($result->fields[0]);
	}
}
/**
 * @return int
 * @param int $input_account_id
 * @desc Returns the account name of the account ID inputted.
*/
function AccountID_To_UserID($input_account_id) {
	if ($input_account_id == 0) {
		return "";
	}
	// Converts Account ID to Account Name
	$query = sprintf(ACCOUNTID_TO_USERID, $input_account_id);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return "";
	}
	else {
		return del_escape($result->fields[0]);
	}
}

function UserID_To_AccountID($input_user_id) {
	// Converts Account Name to Account ID
	$input_user_id = add_escape($input_user_id);
	$query = sprintf(USERID_TO_ACCOUNTID, $input_user_id);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return 0;
	}
	else {
		return $result->fields[0];
	}
}

function GuildID_To_GuildName($input_guild_id) {
	// Converts Account ID to Account Name
	$query = sprintf(GUILDID_TO_GUILDNAME, $input_guild_id);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return "";
	}
	else {
		return del_escape($result->fields[0]);
	}
}

function GuildName_To_GuildID($input_guild_name) {
	// Converts Account Name to Account ID
	$input_guild_name = add_escape($input_guild_name);
	$query = sprintf(GUILDNAME_TO_GUILDID, $input_guild_name);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return 0;
	}
	else {
		return $result->fields[0];
	}
}

/**
 * @return int
 * @param string $input_character_name
 * @desc Returns the account ID of the character name given
*/
function account_of_character ($input_character_name) {
	// character name, returns account name
	$input_character_name = add_escape($input_character_name);
	$query = sprintf(ACCOUNT_OF_CHAR, $input_character_name);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		return "";
	}
	else {
		return $result->fields[0];
	}
}

function clear_account($account_id) {
	global $CONFIG_server_type;
	// input account #, clears everything associated with account
	$query = sprintf(DISPLAY_ACCOUNT_ITEMS, $account_id);
	$result = execute_query($query, 'functions.php');
	while ($line = $result->FetchRow()) {
		$delete_char_id = $line[0];
		clear_character($delete_char_id);
	}
	// Delete storage, then account
	if ($CONFIG_server_type == 0) {
		$query = "DELETE FROM character.dbo.storeitem WHERE AID = '$account_id'";
		$result = execute_query($query, 'functions.php');
		$query = "DELETE FROM nLogin.dbo.account WHERE AID = '$account_id'";
		$result = execute_query($query, 'functions.php');
		$query = "DELETE FROM nLogin.dbo.login WHERE AID = '$account_id'";
		$result = execute_query($query, 'functions.php');
	}
	else {
		$query = "DELETE FROM `storage` WHERE account_id = '$account_id'";
		$result = execute_query($query, 'functions.php');
		$query = "DELETE FROM `login` WHERE account_id = '$account_id'";
		$result = execute_query($query, 'functions.php');
	}
}

function clear_character($delete_char_id) {
	global $CONFIG_server_type, $CONFIG_passphrase;
	// input char #, clears everything associated with character
	// Following Sections clean the database of any trace of the account.
	// Looking at the queries, you can see that it's very thorough, and that the only
	// way to reverse this process is to restore a backup of the database.

	// Guild/Party Clearing

	// Start Guild Clear
	// Check if that character is guild master
	# fix?

	$query = sprintf(CHARID_TO_CHARNAME, $delete_char_id );
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() > 0) {
			$delete_char = $result->fields[0];
	}
	else
		die( "Something fucked up." );

	# end fix?

	$query = sprintf(CHECK_GUILD_MASTER, $delete_char);
	$result = execute_query($query, "functions.php");
	$line = $result->FetchRow();
	if ($result->RowCount() > 0) {
		// Deleted Character owns a guild
	#	clear_guild($result->fields[0]);
		clear_guild($line[0]);	# fix2
	}
	else {
		// Deleted Character does not own a guild
		if ($CONFIG_server_type > 0) {
			// Only Athena has guild_id in character table

			// Set that character's guild id to 0
			$query = "UPDATE `char` SET guild_id = 0 WHERE char_id = $delete_char_id";
			$result = execute_query($query, "functions.php");
		}
		// Character leaves the guild
		$query = sprintf(LEAVE_GUILD, $delete_char_id);
		$result = execute_query($query, "functions.php");
	}
	// End Guild clearing

	// Start Party Clear

	// Check if character is party master
	$query = sprintf(CHECK_PARTY_MASTER, $delete_char_id);
	$result = execute_query($query, "functions.php");
	$line = $result->FetchRow();
	$delete_party_id = $line[0];
	if ($result->RowCount() > 0) {
		// Deleted Character owns a party
		if ($CONFIG_server_type > 0) {
			// Only Athena has party_id in character table

			// Deleted char owns a party
			// Determine party ID that they own
			// Go through each member of that party, set their party to 0
			$query = "UPDATE `char` SET party_id = 0 WHERE party_id = $delete_party_id";
			$result = execute_query($query, "functions.php");
		}

		// Delete party
		$query = sprintf(DELETE_PARTY, $delete_party_id);
		$result = execute_query($query, "functions.php");
		if ($CONFIG_server_type == 0) {
			$query = sprintf(DELETE_PARTY2, $delete_party_id);
			$result = execute_query($query, "functions.php");
		}
	}
	else {
		// Deleted Character does not own a party
		if ($CONFIG_server_type == 0) {
			// Aegis uses a separate table to store party-member relationships

			// Delete character from party
			$query = "DELETE FROM character.dbo.GroupMInfo WHERE GID = $delete_char_id";
			$result = execute_query($query, "functions.php");
		}
		else {
			// Athena uses party_id in character table to determine if char is in party

			// Set that character's party id to 0
			$query = "UPDATE `char` SET party_id = 0 WHERE char_id = $delete_char_id";
			$result = execute_query($query, "functions.php");
		}
	}
	// End Party clearing

	// Delete the character information
	if ($CONFIG_server_type == 0) {
		$delete_table = array(
		"cartItem", "charinfo", "item", "skill", "warpInfo"
		);
	}
	else {
		$delete_table = array(
		"cart_inventory", "char", "inventory", "memo", "pet", "skill"
		);
	}
	foreach ($delete_table as $table_name) {
		$query = sprintf(DELETE_CHAR, $table_name, $delete_char_id);
		$result = execute_query($query, "functions.php");
	}
}

function clear_guild($delete_guild_id) {
	global $CONFIG_server_type;
	$query = sprintf(DELETE_ALLIANCE, $delete_guild_id, $delete_guild_id);
	$result = execute_query($query, "functions.php");

	if ($CONFIG_server_type == 0) {
		$delete_table = array(
		"Agit", "GuildBanishInfo", "GuildInfoDB", "GuildMInfo", "GuildMPosition",
		"GuildNotice", "GuildSkill"
		);
	}
	else {
		$query = "UPDATE `char` SET guild_id = 0 WHERE guild_id = $delete_guild_id";
		$result = execute_query($query, "functions.php");
		$delete_table = array(
		"guild", "guild_castle", "guild_expulsion", "guild_member", "guild_position",
		"guild_skill", "guild_storage"
		);
	}
	foreach ($delete_table as $table_name) {
		$query = sprintf(DELETE_GUILD, $table_name, $delete_guild_id);
		$result = execute_query($query, "functions.php");
	}
}

function convert_date($input_number) {
	if ($input_number) {
		return date("M j @ g:ia", strtotime($input_number));
	}
	else {
		return "";
	}
}

// Ban/Unban Functions

function display_ban ($account_search) {
	if (!is_numeric($account_search)) {
		$first = 0;
	}
	else {
		$first = $account_search;
	}
	// displays the ban confirmation
	$query = sprintf(CHECK_BAN_ACCOUNT, $first, $account_search);
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "Account $account_search doesn't exist!");
	}
	else {
		$ban_id = $result->fields[0];
		$query = sprintf(CHECK_IF_BANNED, $ban_id);
		$result = execute_query($query, "functions.php");
		if ($result->RowCount() == 0) {
			redir("index.php", "$account_search is already banned!");
		}
		else {
			$account_name = $result->fields[1];
			EchoHead(50);
			echo "
	<form action=\"ban.php\" method=\"POST\">
	<tr class=mytitle>
		<td>Ban Account: $account_name</td>
	</tr>
	<tr class=myheader>
		<td>
			You are going to ban $account_name. Please list a reason below.<br>\n
			Note: Actions will be logged, so a good reason would be useful.<br>\n
		</td>
	</tr>
	<tr class=mycell>
		<td>
			Reason: <input type=\"text\" class=\"myctl\" name=\"reason\" size=50><p>\n
			There will be no confirmation screen, be absolutely sure that you are going to ban.<p>\n
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Ban This Account!\"><br>\n
		</td>
	</tr>
			<input type=\"hidden\" class=\"myctl\" name=\"account_name\" value=\"$account_name\">
	</form>
</table>
			";
		}
	}
	return 0;
}

function display_unban ($account_search) {
	// displays the ban confirmation
	if (!is_numeric($account_search)) {
		$first = 0;
	}
	else {
		$first = $account_search;
	}
	$query = sprintf(CHECK_BAN_ACCOUNT, $first, $account_search);
	$result = execute_query($query, "functions.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "Account $account_search doesn't exist!");
	}
	else {
		$ban_id = $result->fields[0];
		$query = sprintf(CHECK_IF_UNBANNED, $ban_id);
		$result = execute_query($query, "functions.php");
		if ($result->RowCount() == 0) {
			redir("unban.php", "$account_search is not banned!");
		}
		else {
			$account_name = $result->fields[1];
			EchoHead(50);
			echo "
	<form action=\"unban.php\" method=\"POST\">
	<tr class=mytitle>
		<td>Unban Account: $account_name</td>
	</tr>
	<tr class=myheader>
		<td>
			You are going to unban $account_name. Please list a reason below.<br>\n
			Note: Actions will be logged, so a good reason would be useful.<br>\n
		</td>
	</tr>
	<tr class=mycell>
		<td>
			Reason: <input type=\"text\" class=\"myctl\" name=\"reason\" size=50><p>\n
			There will be no confirmation screen, be absolutely sure that you are going to unban.<p>\n
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Unban This Account!\"><br>\n
		</td>
	</tr>
			<input type=\"hidden\" class=\"myctl\" name=\"account_name\" value=\"$account_name\">
	</form>
</table>
			";
		}
	}
	return 0;
}

// End Ban/Unban Functions

// Guild Functions

function ShowGuildInfo($guild_id) {
	global $CONFIG_passphrase;
	if (strlen($guild_id) == 32) {
		$query = sprintf(SHOW_GUILD_INFO, $CONFIG_passphrase, $guild_id);
		$result = execute_query($query, "functions.php");
		$guild_id = $result->fields[0];
		$guild_name = $result->fields[1];
	}
	else {
		$guild_name = GuildID_To_GuildName($guild_id);
	}
	// Looks at Alliances/Antagonists
	$query = sprintf(SHOW_GUILD_ALLIANCE, $guild_id);
	$result = execute_query($query, "functions.php");
	echo "
<table class=\"mytable\" cellpadding=0 width=80% align=center>
	<tr class=mytitle>
		<td colspan=5>Guild Information for: $guild_name</td>
	</tr>
	<tr class=mytitle>
		<td colspan=5>Alliances/Enemies</td>
	</tr>
	<tr class=myheader>
		<td colspan=3>Status</td>
		<td colspan=2>Guild</td>
	</tr>
	";
	if ($result->RowCount() > 0) {
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>";
			$current_row = 0;
			foreach ($line as $col_value) {
				$current_row++;
				if ($current_row == 1) {
					if ($col_value == '0') {
						$col_value = "Alliance";
					}
					else {
						$col_value = "Enemy";
					}
				}
				echo "<td colspan=3>$col_value</td>";
			}
			echo "</tr>";
		}
	}
	else {
		echo "
	<tr class=mycell>
		<td colspan=5>None</td>
	</tr>
		";
	}
	// Looks at Member List
	$query = sprintf(SHOW_GUILD_MEMBERS, $guild_id);
	$result = execute_query($query, "functions.php");
	echo "
	<tr>
		<tr class=mytitle>
			<td colspan=5>Guild Members</td>
		</tr>
		<tr class=myheader>
			<td>Member Name</td>
			<td>Class</td>
			<td>Level</td>
			<td>EXP Donated</td>
			<td>Position</td>
		</tr>
	";
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		$current_row = 0;
		foreach ($line as $col_value) {
			$current_row++;
			// Checks if entry is the leader
			if ($line[4] == 0) {
				$bold = true;
			}
			else {
				$bold = false;
			}
			if ($current_row == 2) {
				$col_value = determine_class($col_value);
			}
			elseif ($current_row == 5) {
				continue;
			}
			if ($bold) {
				echo "<td><b>$col_value</b></td>";
			}
			else {
				echo "<td>$col_value</td>";
			}
		}
		echo "</tr>";
	}
	echo "
	</tr>
</table>
	";
}
//Formats the Guild Times in server info - Vich
	function formattime($timestamp) { return date("g:ia",strtotime($timestamp)); }

	function gettime($time1, $time2, $offset) {
	global $lang;
		if (strstr($time1,"/") && strstr($time2,"/")) {
			$starts = explode("/",$time1); $ends = explode("/",$time2);
			foreach($starts as $index => $timestamp) {
				$timestamp += $offset;
				while ($timestamp >= 2400) { $timestamp -= 2400; }
				$return .= sprintf($lang['guildwarsto'],formattime($timestamp),formattime($ends[$index]));
				if ($index != (count($starts)-1)) { $return .= ", "; }
			}
			return $return;
		}
		else {
			$time1 += $offset; $time2 += $offset;
			while ($time1 >= 2400) { $time1 -= 2400; }
			while ($time2 >= 2400) { $time2 -= 2400; }
			return sprintf($lang['guildwarsto'],formattime($time1), formattime($time2));
		}
	}
// End Guild Functions

// Mobdef name parser by xmarv
function ParseMobDefNames($path)
{
		// Read file
		$contents = @file($path);
		if(!is_array($contents))
				die(sprintf("File missing or empty: %s", $path));

		// Parse lines
		$MobDefNames = array();
		foreach($contents as $i => $line)
		{
				// skip all non mob lines and clean up
				$line = trim($line);
				if(substr($line, 0, 3) != "mob")
						continue;
				$line = str_replace("\t", " ", $line);
				$line = str_replace("  ", " ", $line);

				// determine aegis mob name (e.g. PORING)
				$pos1 = strpos($line, " ");
				$pos2 = strpos($line, " ", $pos1+1);
				$aegisname = "";
				if(($pos1 > 0) && ($pos2 > 0) && ($pos1 < $pos2))
						$aegisname = substr($line, $pos1+1, $pos2-$pos1-1);

				// determine real mob name (e.g. Poring)
				$pos1 = strpos($line, "\"", $pos2);
				$pos2 = strpos($line, "\"", $pos1+1);
				$realname = "";
				if(($pos1 > 0) && ($pos2 > 0) && ($pos1 < $pos2))
						$realname = substr($line, $pos1+1, $pos2-$pos1-1);

				// check if names are not empty
				if(($aegisname != "") && ($realname != ""))
				{
						// Add to table
						$MobDefNames[$aegisname] = $realname;
				}
		}
		return $MobDefNames;
}
//DEBUG
//$table = ParseMobDefNames("./dbtranslation/mobdef.sc");
//var_dump($table);



// Parsing function
function ParseIdNum2ItemDisplayNameTable($path)
{
    // Read file
    $contents = @file($path);
    if(!is_array($contents))
        die(sprintf("File missing or empty: %s", $path));

    // Parse lines
    $IdNum2ItemDisplayNameTable = array();
    foreach($contents as $i => $line)
    {
        $line = str_replace("#", "\t", $line);
        $line = trim($line);
        if($line == "")
            continue;

        $split = explode("\t", $line);
        if(count($split) < 2)
            continue;

        // Add to table
        $IdNum2ItemDisplayNameTable[$split[0]] = $split[1];
    }
    return $IdNum2ItemDisplayNameTable;
}

//DEBUG
//$table = ParseIdNum2ItemDisplayNameTable("./dbtranslation/idnum2itemdisplaynametable.txt");
//var_dump($table);



// Parsing function
function ParseNPCIdentityTable($path)
{
    // Read file
    $contents = @file($path);
    if(!is_array($contents))
        die(sprintf("File missing or empty: %s", $path));

    // Parse lines
    $NPCIdentityTableTable = array();
    foreach($contents as $i => $line)
    {
        $line = str_replace("\t", "", $line);
        $line = str_replace(" ", "", $line);
        $line = str_replace("JT_", "", $line);
        $line = str_replace(",", "", $line);
        $line = trim($line);
        if($line == "")
            continue;

        $split = explode("=", $line);
        if(count($split) < 2)
            continue;

        // Add to table
        $NPCIdentityTableTable[$split[0]] = $split[1];
    }
    return $NPCIdentityTableTable;
}

//DEBUG
//$table = ParseNPCIdentityTable("./dbtranslation/NPCIdentity.lua");
//var_dump($table);

// Parsing function
function ParseMobNameDefTable($path)
{
    // Read file
    $contents = @file($path);
    if(!is_array($contents))
        die(sprintf("File missing or empty: %s", $path));

    // Parse lines
    $MobNameDefTable = array();
    foreach($contents as $i => $line)
    {
        if($line == "")
            continue;

        $split = explode(" ", $line);
        if(count($split) < 2)
            continue;

        // Add to table
        $MobNameDefTable[$split[0]] = trim($split[1]);
    }
    return $MobNameDefTable;
}

//DEBUG
//$table = ParseMobNameDefTable("./dbtranslation/mobname.def");
//var_dump($table);

function ParseMapNameTable($path)
{
    // Read file
    $contents = @file($path);
    if(!is_array($contents))
        die(sprintf("File missing or empty: %s", $path));

    // Parse lines
    $MapNameTable = array();
    foreach($contents as $i => $line)
    {
        if($line == "")
            continue;

        $split = explode("#", $line);
        if(count($split) < 2)
            continue;

        // Add to table
        $MapNameTable[str_replace(".rsw", ".gat", $split[0])] = trim($split[1]);
    }
    return $MapNameTable;
}

//DEBUG
//$table = ParseMapNameTable("./dbtranslation/mapnametable.txt");
//var_dump($table);


?>
