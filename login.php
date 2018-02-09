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
				<table align=\"center\" border=\"0\">
					<tr>
						<td class=\"mytext\">User:</td>
						<td><input type=\"text\" class=\"myctl\" name=\"login_user\" /></td>
					</tr>
					<tr>
						<td class=\"mytext\">Password:</td>
						<td><input type=\"password\" class=\"myctl\" name=\"login_pass\" /></td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<table align=\"center\" border=\"0\">
								<tr>
									<td colspan=\"2\">
										<center><input type=\"submit\" class=\"myctl\" value=\"login\" /></center>
										<input type=\"hidden\" name=\"action\" value=\"login\" />
									</td>
								</tr>
								<tr>
									<td class=\"mytext\">
										<a href=\"register.php\"><img src=\"skin/$STORED_skin/images/register.gif\" border=\"0\" alt=\"Register!\" /></a>
										&nbsp;&nbsp;
										<a href=\"lost_pass.php\"><img src=\"skin/$STORED_skin/images/lost.gif\" border=\"0\" alt=\"Lost Password?\" /></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>
	";
	
}
elseif ($POST_action == "login") {
	$access_level = authenticate($POST_login_user, md5($POST_login_pass));
	if ($access_level > 0) {
		if ($CONFIG_save_type == 1) {
			setcookie("login", $_POST['login_user'], time()+60*60*24*30);
			setcookie("password", md5($_POST['login_pass']), time()+60*60*24*30);
			setcookie("skin", "default", time()+60*60*24*30);
		}
		else {
			$_SESSION['login'] = $_POST['user'];
			$_SESSION['password'] = md5($_POST['pass']);
		}
		require 'header.inc';
		if ($access_level > 1) {
			// Adds to the login log
			add_access_entry("Logged in as $POST_login_user");
		}
		redir("index.php", "You are now logged in");
	}
	else {
		require 'header.inc';
		redir("login.php", "The username/password you entered is incorrect");
	}
}
elseif ($GET_action == "logout") {
	if ($CONFIG_save_type == 1) {
		setcookie("login");
		setcookie("password");
		setcookie("mysql");
		setcookie("skin");
	}
	else {
		session_unset();
	}
	$STORED_level = 0;
	require 'header.inc';
	redir("login.php", "You are now logged out");
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
	$query = sprintf(REQUEST_RESEND, $GET_resend_account, $GET_resend_email);
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