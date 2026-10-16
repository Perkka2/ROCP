<?php
require 'memory.php';
if (!$POST_action && !$GET_action) {
	require 'header.inc';
	if ($CONFIG_register_type == 1) {
		$resend = "
	<tr class=\"mycell\">
		<td>
		<a href=\"login.php?action=resend\">Resend Validation Email</a>
		</td>
	</tr>
		";
	}
	require 'terms.php';
	// A skin can provide images for these links, otherwise buttons in the skin's own style are shown
	$login_links = "";
	$links = array("lost_pass.php" => array("lost.gif", "Lost Password?"), "register.php" => array("register.gif", "Register"));
	foreach ($links as $link_page => $link) {
		$link_image = "skin/$STORED_skin/images/{$link[0]}";
		if (file_exists($link_image)) {
			$login_links .= "<a href=\"$link_page\"><img src=\"$link_image\" border=\"0\" alt=\"{$link[1]}\" /></a>\n";
		}
		else {
			$login_links .= "<input type=\"button\" class=\"myctl\" value=\"{$link[1]}\" onclick=\"location.href='$link_page';\" />"
			. "<noscript><a href=\"$link_page\">{$link[1]}</a></noscript>\n";
		}
	}
	EchoHead(80);
	echo"
	<tr class=\"mytitle\">
		<td>
			Please log in to the Control Panel
		</td>
	</tr>
	<tr class=\"mycell\">
		<td>
		Note: Usernames/Passwords MUST be alphanumeric! Otherwise, you will not be able to log in.
		</td>
	</tr>
	$resend
	<tr>
		<td>
			<form action=\"login.php\" method=\"post\">
				<table border=\"0\">
					<tr>
						<td class=\"mytext\">User: <input type=\"text\" class=\"myctl\" name=\"login_user\" />
						Password: <input type=\"password\" class=\"myctl\" name=\"login_pass\" /></td>
						
					</tr>
					<tr>
						<td>
							<center><input type=\"submit\" class=\"myctl\" value=\"login\" /></center>
							<input type=\"hidden\" name=\"action\" value=\"login\" />
						</td>
						
					</tr>
					<tr>
						<td class=\"mytext\"><center>$login_links</center></td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>
	";
	
}
elseif ($POST_action == "login") {
	// Limit failed logins per IP
	if (too_many_attempts("Failed login", 5, 15)) {
		require 'header.inc';
		redir("login.php", "Too many failed logins, please wait 15 minutes and try again.");
	}
	$access_level = authenticate($POST_login_user, md5($POST_login_pass));
	if ($access_level > 0) {
		// New session id on login to prevent session fixation
		session_regenerate_id(true);
		$_SESSION['login'] = $POST_login_user;
		$_SESSION['password'] = md5($POST_login_pass);
		$_SESSION['last_activity'] = time();
		if ($access_level > 1) {
			// Adds to the login log
			add_access_entry("Logged in as $POST_login_user");
		}
		// Straight to the start page, no "You are now logged in" page in between
		redirect_to("index.php");
	}
	else {
		add_access_entry("Failed login");
		require 'header.inc';
		redir("login.php", "The username/password you entered is incorrect");
	}
}
elseif ($GET_action == "logout") {
	$_SESSION = array();
	session_regenerate_id(true);
	// Straight back to the login page, no "You are now logged out" page in between
	redirect_to("login.php");
}
elseif ($GET_action == "resend" && $CONFIG_register_type == 1) {
	require 'header.inc';
	EchoHead(50);
	echo "
	<form action=\"login.php\" method=\"GET\">
	<tr class=\"mytitle\">
		<td colspan=2>Resend Validation Email</td>
	</tr>
	<tr class=\"myheader\">
		<td colspan=2>
			Didn't recieve a validation email? Request it again!
		</td>
	</tr>
	<tr class=\"mycell\">
		<td class=\"mytext\">Account Name: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"resend_account\" value=\"\"></td>
	</tr>
	<tr class=\"mycell\">
		<td class=\"mytext\">Email: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"resend_email\" value=\"\"></td>
	</tr>
	<tr class=\"mycell\">
		<td colspan=2>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Request Email!\">
		</td>
	</tr>
	</form>
</table>
	";
}
elseif ($GET_action == "Request Email!") {
	require 'header.inc';
	$query = query_format(REQUEST_RESEND, $GET_resend_account, $GET_resend_email);
	$result = execute_query($query, "login.php");
	if ($result->RowCount() == 0) {
		redir("login.php", "That username & email combination was not found in the database!");
	}
	else {
		$line = $result->FetchRow();
		$message = sprintf($lang['confirmemail'], $CONFIG_server_name, $line[2], $line[3], $CONFIG_server_name, $CONFIG_cp_location, $line[1], $line[2], $CONFIG_server_name);
		SendMail($line[2], $line[5], "$CONFIG_server_name Server Registration", $message);
		$msg = "
		An email has been sent to you ({$line[5]}).  Please click the link inside to activate your account.
		<br><br>
		If you do not get the email within 5 minutes, make make sure that you are using the correct email address. <br>
		Take note that hotmail accounts seem to take more time to process <br>
		or do not recieve the email at all.<br>
		";
		redir("login.php", $msg, 20);
	}
}
require 'footer.inc';
?>