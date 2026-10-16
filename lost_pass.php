<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$CONFIG_smtp_host) {
	redir("index.php", "The email feature has not been enabled.");
}
// Reset links are valid for one hour, and stop working once the password has been changed
$reset_link_lifetime = 60 * 60;

if ($POST_action == "Reset My Password!") {
	if (too_many_attempts("Password reset requested", 3, 60)) {
		redir("index.php", "Too many password reset requests, please try again later.");
	}
	add_access_entry("Password reset requested");
	$query = query_format(CHECK_LOST_PASS, $POST_retrieve_account, $POST_retrieve_email);
	$result = execute_query($query, "lost_pass.php");
	if ($result->RowCount() > 0) {
		$query = query_format(GET_RESET_ACCOUNT, $POST_retrieve_account);
		$result = execute_query($query, "lost_pass.php");
		$line = $result->FetchRow();
		$expires = time() + $reset_link_lifetime;
		$reset_link = "$CONFIG_cp_location/lost_pass.php?reset_user=" . urlencode($line[0])
		. "&reset_expires=$expires&reset_key=" . reset_key($line[0], $expires, $line[2]);
		$msg = sprintf($lang['lost_pass_link'], $CONFIG_server_name, $reset_link, $CONFIG_server_name);
		// Always sent to the email stored for the account
		SendMail($line[0], $line[1], "Password Reset", $msg);
	}
	// Same answer whether the account exists or not
	redir("login.php", "If the account name and email match, a link to reset your password has been sent to that email address.");
}
elseif ($GET_reset_key != "" || $POST_reset_key != "") {
	if ($POST_reset_key != "") {
		$reset_user = $POST_reset_user;
		$reset_expires = intval($POST_reset_expires);
		$reset_key = $POST_reset_key;
	}
	else {
		$reset_user = $GET_reset_user;
		$reset_expires = intval($GET_reset_expires);
		$reset_key = $GET_reset_key;
	}
	$line = false;
	if ($reset_expires >= time() && $reset_expires <= time() + $reset_link_lifetime) {
		$query = query_format(GET_RESET_ACCOUNT, $reset_user);
		$result = execute_query($query, "lost_pass.php");
		if ($result->RowCount() > 0) {
			$line = $result->FetchRow();
			if (!safe_equals(reset_key($line[0], $reset_expires, $line[2]), $reset_key)) {
				$line = false;
			}
		}
	}
	if (!$line) {
		redir("lost_pass.php", "This password reset link is invalid or has expired. Please request a new one.");
	}

	if ($POST_action == "Set New Password!") {
		$new_password = $POST_new_pass;
		if ($new_password != $POST_new_pass2) {
			redir("lost_pass.php?reset_user=" . urlencode($reset_user) . "&reset_expires=$reset_expires&reset_key=$reset_key", "Your two passwords do not match! Please try again.");
		}
		elseif (strlen($new_password) < 4 || strlen($new_password) > 24 || !isalphanumeric($new_password)) {
			redir("lost_pass.php?reset_user=" . urlencode($reset_user) . "&reset_expires=$reset_expires&reset_key=$reset_key", "Password has to be between 4 and 24 letters, using A-Z, a-z, 0-9 only.");
		}
		if ($CONFIG_use_md5) {
			$stored_password = md5($new_password);
		}
		else {
			$stored_password = $new_password;
		}
		$query = query_format(RESET_NEW_PASS, $stored_password, $line[0]);
		$result = execute_query($query, "lost_pass.php");
		add_access_entry("Password reset for {$line[0]}");
		redir("login.php", "Your password has been changed, you can now log in.");
	}

	EchoHead(50);
	echo "
	<form action=\"lost_pass.php\" method=\"POST\">
	<input type=\"hidden\" name=\"reset_user\" value=\"" . html_escape($reset_user) . "\">
	<input type=\"hidden\" name=\"reset_expires\" value=\"$reset_expires\">
	<input type=\"hidden\" name=\"reset_key\" value=\"" . html_escape($reset_key) . "\">
	<tr class=mytitle>
		<td colspan=2>Choose a New Password</td>
	</tr>
	<tr class=myheader>
		<td colspan=2>
			Account: <b>" . html_escape($line[0]) . "</b><br>
			Password has to be between 4 and 24 letters, using A-Z, a-z, 0-9 only.
		</td>
	</tr>
	<tr class=mycell>
		<td class=\"mytext\">New Password: </td>
		<td><input type=\"password\" class=\"myctl\" name=\"new_pass\" value=\"\"></td>
	</tr>
	<tr class=mycell>
		<td class=\"mytext\">Repeat Password: </td>
		<td><input type=\"password\" class=\"myctl\" name=\"new_pass2\" value=\"\"></td>
	</tr>
	<tr class=mycell>
		<td colspan=2>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Set New Password!\">
		</td>
	</tr>
	</form>
</table>
	";
}
else {
	EchoHead(50);
	echo "
	<form action=\"lost_pass.php\" method=\"POST\">
	<tr class=mytitle>
		<td colspan=2>Reset Password</td>
	</tr>
	<tr class=myheader>
		<td colspan=2>
			Forgot your password? Enter your account and email, and a link to choose a new one will be sent to you.<p>\n
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