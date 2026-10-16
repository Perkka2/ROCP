<?php
// Buffer all output so headers (session id renewal, redirects) still work after early warnings,
// and add the CSRF token to forms and action links (csrf_add_tokens() in functions.php).
// Only once: some pages (terms.php) require memory.php a second time.
if (!defined('CSRF_OUTPUT_FILTER')) {
	define('CSRF_OUTPUT_FILTER', true);
	ob_start(function ($buffer) {
		return function_exists('csrf_add_tokens') ? csrf_add_tokens($buffer) : $buffer;
	});
}
    //Extra Security layer for traverse exploits
    // Get called URL
    $URL = "http://";
    $URL.= $_SERVER["HTTP_HOST"];
    $URL.= $_SERVER["PHP_SELF"];

    //DEBUG
    //echo "<br>".$URL;
    //echo "<br>".$_SERVER['PATH_INFO'];

    // Get position of .php occurrence
    $startpos = strpos($URL, ".php");

    // Parse rest of URL, block users accessing
    // URLs with slashes behind the PHP script's
    // name
    if($startpos > 0)
    {
        for($i = $startpos; $i < strlen($URL); $i++)
        {
            if($URL[$i] == "/")
                die("Illegal URL.");
        }
    }


$php_version = phpversion();
$cp_version = "5.0.4";
// Checks PHP Versions, will be used when PHP5 is mandatory
/*
if (!version_compare($version, "5.0.0", ">=")) {
	die("You are currently running PHP Version $version. The Control Panel is only compatible with version 5. Please download the new version at <a href=\"http://www.php.net\">http://www.php.net</a>");
}
*/
error_reporting(E_ALL ^ E_NOTICE); // General Use
//error_reporting(E_ALL);	// Debugging
//error_reporting('NONE');	// None
// This is the function that calls on all the actions to be executed on each action
include_once 'config.php'; // loads config variables
include_once 'extract.inc';
include_once 'access.php';
// Includes the ADODB library
include_once 'adodb/adodb.inc.php';
// Connects to database
switch ($CONFIG_server_type) {
	// Aegis
	case 0:
		if($CONFIG_server_db_conn == 0){
		$link = ADONewConnection('mssql');
		if(!$link->Connect($CONFIG_db_host, $CONFIG_db_username, $CONFIG_db_password, "nLogin")){
			$error = "Failed to connect to database: " . $link->ErrorMsg();
		}
		}
		elseif($CONFIG_server_db_conn == 1){
		$link = ADONewConnection('odbc_mssql');
		if(!$link->Connect($CONFIG_odbc_datasource, $CONFIG_db_username, $CONFIG_db_password)){
			$error = "Failed to connect to database: " . $link->ErrorMsg();
		}
		}
		if($CONFIG_server_db_conn == 2){
		$link = ADONewConnection('mssqlnative');
		if(!$link->Connect($CONFIG_db_host, $CONFIG_db_username, $CONFIG_db_password, "nLogin")){
			$error = "Failed to connect to database: " . $link->ErrorMsg();
		}
		}
		$link->SetFetchMode(ADODB_FETCH_NUM);
		break;
	// MySQL Athena/Freya
	case 1: case 2: case 3:
		$link = ADONewConnection('mysql');
		if(!$link->Connect($CONFIG_db_host, $CONFIG_db_username, $CONFIG_db_password, $CONFIG_db_name)){
			$error = "Failed to connect to database: " . $link->ErrorMsg();
		}
		$link->SetFetchMode(ADODB_FETCH_NUM);
		break;
}
include_once 'functions.php'; // declares functions
$start_time = getmicrotime();
switch ($CONFIG_server_type) {
	case 0:
		include_once "query/Aegis/query.php"; // imports queries
		break;
	case 1: case 2:
		include_once "query/Athena/query.php"; // imports queries
		break;
	case 3:
		include_once "query/Freya/query.php"; // imports queries
		break;
}



// Only load language files from an existing lang/<name>/ folder
if (!preg_match('/^[A-Za-z0-9_-]+$/', $CONFIG_language) || !is_file("lang/$CONFIG_language/lang.php")) {
	$CONFIG_language = "english";
}
include_once "lang/$CONFIG_language/lang.php"; // imports lang file

// Token that csrf_add_tokens() adds to forms and action links
$csrf_token_value = csrf_token();

ini_set('magic_quotes_gpc', 'On');

// You REALLY should not be enabling register_globals
ini_set('register_globals', 'Off');

// Makes sure that a passphrase has been set.

if (!$CONFIG_passphrase) {
	$error = "
	A Passphrase has not been set in config.php! You must set one up before
	you can use this script.
	";
}

// Check the config.php file, and makes sure it is up to date

// The updated config index
$config_array = array(
"db_host", "db_username", "db_password", "cp_db_name", "db_name",
"passphrase", "server_type", "language", "backup_interval",
"default_skin", "check_server", "maintenance", "accip", "accport",
"charip", "charport", "mapip", "mapport", "server_name", "do_gzip_compress",
"use_md5", "results_per_page", "validchars", "agit_start", "agit_end",
"agit_days", "save_type", "minimum_transfer", "sex_change", "max_announce",
"debug", "log_select", "log_insert", "log_update", "log_delete", "website",
"forums_location", "patch_location", "irc_channel", "cp_location",
"max_characters", "ladder_limit", "display_guild_limit", "register",
"register_type", "secure_mode", "max_per_ip", "max_per_email", "max_accounts",
"inactive_days", "smtp_host", "smtp_auth", "smtp_login", "smtp_pass",
"sendmail_name", "sendmail_from", "admin_colour", "gm_colour", "game_gm_colour",
"adjust_rate", "exp_rate", "jexp_rate", "drop_rate"
);

// Obtains the keys in config.php, and stores them in another array
$config_file_array = array_keys($CONFIG);
// Compares the 2 arrays, storing values in $config_array that are not in $config_file_array
$diff = array_diff($config_array, $config_file_array);
if (count($diff) > 0) {
	$error = "You are missing the following config.php variables: " . implode(", ", $diff);
}
unset($config_array);


if ($error) {
	$panel_name = $CONFIG_panel_name != "" ? $CONFIG_panel_name : "Ragnarok Online Control Panel";
	echo "
	<html>
		<head>
			<title>" . html_escape($panel_name) . "</title>
		</head>
		<body>
			<link href=\"skin/$CONFIG_default_skin/style.css\" TYPE=text/css REL=stylesheet>
			<br>
			<br>
			<table border=0 class=redir width=400 height=75 cellpadding=0 cellspacing=0 align=center>
				<tr>
					<td>
						<b>" . nl2br(html_escape(trim($error))) . "</b>
					</td>
				</tr>
			</table>
		</body>
	</html>
	";
	exit();
}

// Set access level, and skin, global variables if logged in
if ($STORED_login != "") {
	// Makes sure the session is still valid (password unchanged, not banned), and assigns the proper privilege level
	$STORED_level = authenticate($STORED_login, $STORED_password);
	if ($STORED_level <= 0) {
		$_SESSION = array();
		session_regenerate_id(true);
		$STORED_login = "";
		$STORED_level = 0;
		require 'header.inc';
		redir("login.php", "Your session is no longer valid, please log in again.");
	}
	$query = query_format(GET_SKIN, $STORED_login);
	$result = execute_query($query, "memory.php");
	if ($result->RowCount() > 0) {
		// Skin is in DB, check if skin is valid
		$line = $result->FetchRow();
		if (valid_skin($line[0])) {
			$STORED_skin = $line[0];
		}
		else {
			// Illegal skin, update the DB entry
			$query = query_format(UPDATE_SKIN, $CONFIG_default_skin, $STORED_login);
			$result = execute_query($query, "memory.php", 0, 0, true);
			$STORED_skin = $CONFIG_default_skin;
		}
	}
	else {
		// Skin choice not in DB, give them the default one
		$query = query_format(INSERT_SKIN, $STORED_login, $CONFIG_default_skin);
		$result = execute_query($query, "memory.php", 0, 0, true);
		$STORED_skin = $CONFIG_default_skin;
	}
	$STORED_id = UserID_To_AccountID($STORED_login);
	$privilege = privilege_string($STORED_level);
	if (!$STORED_skin) {
		$STORED_skin = $CONFIG_default_skin;
	}
}
else {
	$STORED_level = 0;
	$STORED_skin = $CONFIG_default_skin;
}

// Only validates inputs from users and in-game GMs.
// GMs/Admins are skipped because announcements and config values need more characters.
if ($STORED_level < 3 || !IsSet($STORED_level)) {
	// Validates all values given by user, eliminates illegal inputs and logs each attempt
	foreach ($_GET as $index => $test_value) {
		// Skips the checking of inputs in following GET felds.
		$skipped_entries = array("Style", "rc", "p", "reg_id");
		if (in_array($index, $skipped_entries)) {
			continue;
		}
		elseif (($index == 'retrieve_email' || $index == 'resend_email') && strlen($test_value) > 0) {
			if (!preg_match("/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/", $test_value, $test)) {
				$message = "Invalid Input for " . '$_GET' . "[$index] as \"$test_value\"!";
				add_exploit_entry($message);
				require 'header.inc';
				redir("index.php", "$message");
			}
		}
		else {
			// Checks Length
			if (strlen($test_value) > 30 && strlen($test_value) != 32) {
				$message = '$_GET' . "[$index] as \"$test_value\" is too long!";
				add_exploit_entry($message);
				require 'header.inc';
				redir("index.php", "$message");
			}
			if (!validate_string($test_value) && strlen($test_value) > 0) {
				$message = "Invalid Input for " . '$_GET' . "[$index] as \"$test_value\"!";
				add_exploit_entry($message);
				require 'header.inc';
				redir("index.php", "$message");
			}
		}
	}

	foreach ($_POST as $index => $test_value) {
		// Even login names can be used to inject, so all login/passwords must be alphanumeric!
		// login_pass is skipped: it is only used as md5(), and older accounts may contain other characters
		$skipped_entries = array('option', 'login_pass');
		if (in_array($index, $skipped_entries)) {
			continue;
		}
		// Validate the email by it's own method
		if (($index == 'register_email' || $index == 'new_email' || $index == 'retrieve_email') && strlen($test_value) > 0) {
			if (!preg_match("/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/", $test_value, $test)) {
				$message = "Invalid Input for " . '$_POST' . "[$index] as \"$test_value\"!";
				add_exploit_entry($message);
				redir("index.php", "$message");
			}
		}
		else {
			// Checks Length
			if (strlen($test_value) > 30 && strlen($test_value) != 32) {
				$message = '$_POST' . "[$index] as \"$test_value\" is too long!";
				add_exploit_entry($message);
				redir("index.php", "$message");
			}
			if (!validate_string($test_value) && strlen($test_value) > 0) {
				$message = "Invalid Input for " . '$_POST' . "[$index] as \"$test_value\"!";
				//add_exploit_entry($message);
				redir("index.php", "$message");
			}
		}
	}
}

// Every POST, and every GET that performs an action, must carry the CSRF token of this session
if (csrf_required(basename($_SERVER['PHP_SELF'])) && !csrf_valid()) {
	add_exploit_entry("Missing or invalid CSRF token for " . basename($_SERVER['PHP_SELF']));
	require 'header.inc';
	redir("index.php", "Your session has expired or the request did not come from this site. Please try again.");
}
?>
