<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

// True if $password is the password of the logged in account
function password_is_correct($password) {
	global $CONFIG_use_md5, $STORED_login;
	$query = query_format($CONFIG_use_md5 ? CHECK_OLD_MD5_PASS : CHECK_OLD_PASS, $STORED_login, $password);
	$result = execute_query($query, "account.php");
	return $result->RowCount() > 0;
}

// "ar***@example.com": enough to recognise the address without showing it whole
function mask_email($email) {
	$at = strpos($email, "@");
	if ($at === false) {
		return html_escape($email);
	}
	$name = substr($email, 0, $at);
	return html_escape(substr($name, 0, min(2, strlen($name)))) . "***" . html_escape(substr($email, $at));
}

if ($POST_action == "Change Password") {
	$new_password = $POST_new_pass;
	if (!password_is_correct($POST_old_pass)) {
		redir("account.php", "Your current password was not entered correctly!");
	}
	elseif ($new_password != $POST_new_pass2) {
		redir("account.php", "You have not repeated your new password correctly!");
	}
	elseif (strlen($new_password) < 4 || strlen($new_password) > 24) {
		redir("account.php", "The password has to be between 4 and 24 letters.");
	}
	elseif (!isalphanumeric($new_password)) {
		redir("account.php", "Illegal characters detected. Please use A-Z, a-z, 0-9 only.");
	}
	elseif ($new_password == $POST_old_pass) {
		redir("account.php", "The new password is the same as the current one.");
	}
	$query = query_format($CONFIG_use_md5 ? UPDATE_NEW_MD5_PASS : UPDATE_NEW_PASS, $new_password, $STORED_id);
	$result = execute_query($query, "account.php", 0, 0, true);
	add_user_entry("Changed password");
	// the session keeps a hash of the password and is checked on every page: keep it valid
	$_SESSION['password'] = md5($new_password);
	redir("account.php", "Your password has been changed.");
}
elseif ($POST_action == "Change Email") {
	$new_email = trim($POST_new_email);
	if (!password_is_correct($POST_email_pass)) {
		redir("account.php", "Your current password was not entered correctly!");
	}
	elseif (strlen($new_email) < 6 || strlen($new_email) > 50 || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
		redir("account.php", "That is not a valid email address.");
	}
	if ($CONFIG_max_per_email > 0) {
		$result = execute_query(query_format(CHECK_MAX_ACCOUNTS_EMAIL, $new_email), "account.php");
		if ($result->fields[0] >= $CONFIG_max_per_email) {
			redir("account.php", "There are already $CONFIG_max_per_email accounts with that email address.");
		}
	}
	$result = execute_query(query_format(UPDATE_EMAIL, $new_email, $STORED_id), "account.php", 0, 0, true);
	add_user_entry("Changed email");
	redir("account.php", "Your email has been changed.");
}
elseif ($POST_action == "Change Gender") {
	if (!$CONFIG_sex_change) {
		redir("account.php", "Changing gender is not allowed on this server.");
	}
	if (is_online($STORED_id)) {
		redir("account.php", "Log out of the game before changing your gender.");
	}
	// No characters in a class that only exists for one gender (Bard, Dancer, Clown, Gypsy...)
	$query = query_format(CHECK_BARD, $STORED_id);
	$result = execute_query($query, "account.php");
	$log_gender = $POST_gender == "F" ? "F" : "M";
	$new_gender = $log_gender;
	if ($CONFIG_server_type == 0) {
		$new_gender = $log_gender == "F" ? 0 : 1;
	}
	if ($result->RowCount() == 0) {
		$query = query_format(UPDATE_SEX, $new_gender, $STORED_id);
		$result = execute_query($query, "account.php");
		add_user_entry("Changed Account to $log_gender");
		redir("account.php", "Your gender has been changed.");
	}
	else {
		redir("account.php", "You cannot change gender while you have a character in a class that only exists for one gender.");
	}
}

// The account at a glance
$info = array('name' => $STORED_login, 'email' => '', 'sex' => '', 'registered' => '', 'characters' => '');
if (defined('GET_ACCOUNT_INFO')) {
	$result = execute_query(query_format(GET_ACCOUNT_INFO, $STORED_id), "account.php");
	if ($line = $result->FetchRow()) {
		$info = array('name' => $line[0], 'email' => $line[1], 'sex' => ($line[2] == 0 ? "F" : "M"),
			'registered' => $line[3], 'characters' => $line[4]);
	}
}
else {
	$result = execute_query(query_format(CHECK_SEX, $STORED_login), "account.php");
	if ($line = $result->FetchRow()) {
		$info['sex'] = $line[0];
	}
}
$tiles = array(array(html_escape($info['name']), "Account"));
if ($info['email'] != '') {
	$tiles[] = array(mask_email($info['email']), "Email");
}
if ($info['sex'] != '') {
	$tiles[] = array($info['sex'] == "F" ? "Female" : "Male", "Gender");
}
if ($info['registered'] != '') {
	$tiles[] = array(html_escape(date("M j, Y", strtotime($info['registered']))), "Registered");
}
if ($info['characters'] !== '') {
	$tiles[] = array((int)$info['characters'], "Characters");
}
echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Account Options</div><div class=\"infoTiles\">";
foreach ($tiles as $tile) {
	echo "<div class=\"infoTile\"><div class=\"infoNumber\">{$tile[0]}</div><div class=\"infoLabel\">{$tile[1]}</div></div>";
}
echo "</div></div>";

// Password and email: both need the current password
echo "
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Change Password</div>
		<form action=\"account.php\" method=\"POST\" class=\"accountForm\">
			<label>Current password <input type=\"password\" class=\"myctl\" name=\"old_pass\" autocomplete=\"current-password\"></label>
			<label>New password <input type=\"password\" class=\"myctl\" name=\"new_pass\" maxlength=\"24\" autocomplete=\"new-password\"></label>
			<label>New password again <input type=\"password\" class=\"myctl\" name=\"new_pass2\" maxlength=\"24\" autocomplete=\"new-password\"></label>
			<div class=\"newsInfo\">4 to 24 letters and digits (A-Z, a-z, 0-9).</div>
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Password\">
		</form>
	</div>
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Change Email</div>
		<form action=\"account.php\" method=\"POST\" class=\"accountForm\">
			<label>New email <input type=\"email\" class=\"myctl\" name=\"new_email\" maxlength=\"50\" autocomplete=\"email\"></label>
			<label>Current password <input type=\"password\" class=\"myctl\" name=\"email_pass\" autocomplete=\"current-password\"></label>
			<div class=\"newsInfo\">The email is used to reset your password, so keep it up to date.</div>
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Email\">
		</form>
	</div>
";

// Gender: only while no character is in the game
if ($CONFIG_sex_change) {
	echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Change Gender</div><div class=\"accountForm\">";
	$result = execute_query(query_format(CHECK_BARD, $STORED_id), "account.php");
	if ($line = $result->FetchRow()) {
		echo "<div>You cannot change gender while you have " . html_escape($line[0]) . " (" . html_escape(determine_class($line[1]))
			. "): that class only exists for one gender.</div>";
	}
	elseif (is_online($STORED_id)) {
		echo "<div>Log out of the game to change your gender.</div>";
	}
	else {
		echo "<form action=\"account.php\" method=\"POST\">
				<label>Gender <select name=\"gender\" class=\"myctl\">
					<option value=\"M\"" . ($info['sex'] == "M" ? " selected" : "") . ">Male</option>
					<option value=\"F\"" . ($info['sex'] == "F" ? " selected" : "") . ">Female</option>
				</select></label>
				<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Gender\">
			</form>";
	}
	echo "</div></div>";
}

// Recent logins to the control panel, so a login that was not you stands out
if (defined('GET_LOGIN_HISTORY')) {
	echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Recent Logins</div>";
	$result = execute_query(query_format(GET_LOGIN_HISTORY, $STORED_login), "account.php");
	$logins = 0;
	while ($line = $result->FetchRow()) {
		$failed = (stripos($line[1], "Failed") === 0);
		echo "<div class=\"newsItem" . ($failed ? " loginFailed" : "") . "\"><div class=\"newsMessage\">" . html_escape($line[1]) . "</div>"
			. "<div class=\"newsInfo\">" . html_escape(date("M j, Y H:i", strtotime($line[0]))) . "</div></div>";
		$logins++;
	}
	if ($logins == 0) {
		echo "<div class=\"newsItem\">No logins recorded yet.</div>";
	}
	echo "<div class=\"newsInfo accountNote\">Logins to this control panel. If you see one that was not you, change your password.</div></div>";
}
echo "</div>\n";

require 'footer.inc';
?>
