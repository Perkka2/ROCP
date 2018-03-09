<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if ($POST_action == "Register Account!") {
	$register_user = $POST_user;        //obtains data
	$register_password = $POST_pass;
	$register_gender = $POST_gender;
	$register_code = $POST_code;
	$register_email = $POST_email;
	$register_level = $POST_level;
	// checks lengths of username and password
	if (strlen($register_user) < 4 or strlen($register_user) > 24) {
		display_error("Account Name must be between 4 and 24 letters.");
	}
	elseif (strlen($register_password) < 4 or strlen($register_password) > 24) {
		display_error("Password has to be between 4 and 24 letters.");
	}
	elseif (!is_numeric($register_level) && $CONFIG_server_type != 0) {
		display_error("Level must be numeric");
	}
	elseif (strlen($register_email) < 6 or strlen($register_email) > 60) {
		display_error("Your email must be between 6 and 60 letters.");
	}
	elseif ($CONFIG_server_type != 0) {
		//check level of GM
		if ($register_level > get_gmlevel($STORED_id)) {
		display_error("GM level cannot exceed your own");
		}
	}
	else {
			$query = sprintf(CHECK_DUPE_ACCOUNT, $register_user);	// searches if account already exists
			$result = execute_query($query, "register.php");
			if ($result->RowCount() > 0) {
				redir("add_account.php", "Account Already Exists, please choose another one.");
			}
			else{
			add_account($register_user, $register_password, $register_gender, $register_email, $register_level);
			add_admin_entry("Registered $register_user");
			redir("add_account.php", "Account $register_user Added!");
		}
	}
}
else {
	EchoHead(50);
	echo "
	<form action=\"add_account.php\" method=\"POST\">
	<tr class=mytitle>
		<td colspan=2>GM Add Account</td>
	</tr>
	<tr class=mycell>
		<td>Account Name: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"user\"></td>
	</tr>

	<tr class=mycell>
		<td>Password: </td>
		<td><input type=\"password\" class=\"myctl\" name=\"pass\"></td>
	</tr>";
if ($CONFIG_server_type != 0) {
	echo "	;<tr class=mycell>
		<td>GM Level: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"level\"></td>
	</tr>
";}

		echo "<tr class=mycell>
		<td>Gender: </td>
		<td>
			<select name=\"gender\" class=\"myctl\">
			<option value=M>Male
			<option value=F>Female
			</select>
		</td>
	</tr>
	<tr class=mycell>
		<td>Email: </td>
		<td><input type=\"text\" class=\"myctl\" name=\"email\"></td>
	</tr>

	<tr class=mycell>
		<td colspan=2>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Register Account!\">
		</td>
	</tr>
	</form>
</table>
	";
}
require 'footer.inc';

function display_error($error_message) {
	global $STORED_skin, $start_time, $STORED_level, $queries, $logged_in;
	require 'config.php';
	require 'extract.inc';
	redir("add_account.php", $error_message);
	require 'footer.inc';
	exit();
}
?>
