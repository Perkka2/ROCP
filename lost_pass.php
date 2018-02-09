<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$CONFIG_smtp_host) {
	redir("index.php", "The email feature has not been enabled.");
}
if ($GET_action == "Reset My Password!") {
	$query = sprintf(CHECK_LOST_PASS, $GET_retrieve_account, $GET_retrieve_email);
	$result = execute_query($query, "lost_pass.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "The Information you entered was not found in the database! Please make sure you entered all information correctly.");
	}
	else {
		// Generate a random password
		$alphanum = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 
		'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 
		'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3',
		'4', '5', '6', '7', '8', '9');
		$seed = time();
		mt_srand($seed);
		for ($i = 1; $i <= 20; $i++) {
			$randnum = intval(mt_rand(0, 36));
			$new_password .= $alphanum[$randnum];
		}
		if ($CONFIG_use_md5) {
			$stored_password = md5($new_password);
		}
		else {
			$stored_password = $new_password;
		}
		// Store Password in database
		$query = sprintf(RESET_NEW_PASS, $stored_password, $GET_retrieve_account);
		$result = execute_query($query, "lost_pass.php");
		
		if ($link->Affected_Rows() >= 0) {
			// If query was successful, construct message
			$msg = sprintf($lang['lost_pass'], $CONFIG_server_name, $new_password, $CONFIG_cp_location, $CONFIG_server_name);
			// Email the new password
			SendMail($GET_retrieve_account, $GET_retrieve_email, "Password Reset", $msg);
			redir("login.php", "Your new password has been sent to $GET_retrieve_email. If you do not get the password within 10 minutes,
			please contact a gamemaster.");
		}
		else {
			redir("login.php", "There was an error with your request, please contact one of the gamemasters.");
		}
	}
}
else {
	EchoHead(50);
	echo "
	<form action=\"lost_pass.php\" method=\"GET\">
	<tr class=mytitle>
		<td colspan=2>Reset Password</td>
	</tr>
	<tr class=myheader>
		<td colspan=2>
			Forgot your password? Enter your account and email, and a new one will be sent to you.<p>\n
		</td>
	</tr>
	<tr class=mycell>
		<td class=\"mytext\">Account Name: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"retrieve_account\" value=\"\"></td>
	</tr>
	<tr class=mycell>
		<td class=\"mytext\">Email: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"retrieve_email\" value=\"\"></td>
	</tr>
	<tr class=mycell>
		<td colspan=2>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Reset My Password!\">
		</td>
	</tr>
	</form>
</table>
	";
}
require 'footer.inc';
?>