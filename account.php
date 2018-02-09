<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (is_online($STORED_login)) {
	redir("index.php", "You cannot edit your account options while logged on. Please log off and try again.");
}
if (!$POST_action) {
	EchoHead(80);
	echo "
	<form action=\"\" method=\"POST\">
	<tr class=mytitle>
		<td colspan=3>Change Account Options</td>
	</tr>
	<tr class=myheader>
		<td colspan=3>
			Here, you can change your password, as well as your gender and email.
		</td>
	</tr>
	<tr>
		<tr class=mycell>
			<td>Old Password:</td>
			<td><input type=\"password\" class=\"myctl\" name=\"old_pass\"></td>
		</tr>
		<tr class=mycell>
			<td>New Password:</td>
			<td><input type=\"password\" class=\"myctl\" name=\"new_pass\" size=20></td>
			<td height=3>
				<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Password\">
			</td>
		</tr>
		<tr class=mycell>
			<td>Re-enter your new password:</td>
			<td><input type=\"password\" class=\"myctl\" name=\"new_pass2\" size=20></td>
		</tr>
	</tr>
	<tr class=mycell>
		<td>Enter your new email:</td>
		<td><input type=\"text\" size=24 class=\"myctl\" name=\"new_email\"></td>
		<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Change Email\"></td>
	</tr>
	";
	
	if ($CONFIG_sex_change) {
		echo "<tr class=mycell>";
		// Determine if there is bard/dancer in account
		$query = sprintf(CHECK_BARD, $STORED_id);
		$result = execute_query($query, "account.php");
		if ($result->RowCount() == 0) {
			$disable_gender = false;
		}
		else {
			$disable_gender = true;
		}
		$query = sprintf(CHECK_SEX, $STORED_id);
		$result = execute_query($query, "account.php");
		$line = $result->FetchRow();
		$current_gender = $line[0];
		if ($CONFIG_server_type == 0) {
			$current_gender = $current_gender == 1? "M" : "F";
		}
		if ($disable_gender) {
			$disabled_class = $current_gender == "M"? "Bard" : "Dancer";
			echo "
			<td colspan=3>You cannot change genders, because you have a $disabled_class!</td>
			";
		}
		else {
			echo "
				<td>Gender:</td>
				<td>
					<select name=\"gender\" class=\"myctl\">
			";
			if ($current_gender == 'M') {
				echo "<option value=M selected>Male";
				echo "<option value=F>Female";
			}
			else {
				echo "<option value=M>Male";
				echo "<option value=F selected>Female";
			}
			echo "
					</select>
				</td>
				<td>
					<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Gender\"><br>
				</td>
			";
		}
		echo "</tr>";
	}
	echo "
	</form>
</table>
	";
}
elseif ($POST_action == "Change Password") {
	$old_password = $POST_old_pass;
	$new_password = $POST_new_pass;
	$new_password2 = $POST_new_pass2;
	// Confirm Old Password
	if ($CONFIG_use_md5) {
		$query = sprintf(CHECK_OLD_MD5_PASS, $STORED_login, $old_password);
	}
	else {
		$query = sprintf(CHECK_OLD_PASS, $STORED_login, $old_password);
	}
	$result = execute_query($query, "account.php");
	if ($result->RowCount() == 0) {
		redir("account.php", "Your old password was not entered correctly!");
	}
	else {
		//Checks that new password is repeated correctly
		if ($new_password == $new_password2) {
			//Checks length of password
			if (strlen($new_password) < 4) {
				redir("account.php", "Password has to be 4 letters or more.");
			}
			else {
				//All checks have been passed, updating new password
				if ($CONFIG_use_md5) {
					$query = sprintf(UPDATE_NEW_MD5_PASS, $new_password, $STORED_id);
				}
				else {
					$query = sprintf(UPDATE_NEW_PASS, $new_password, $STORED_id);
				}
				$result = execute_query($query, "account.php");
				if ($link->Affected_Rows() > 0) {
					add_user_entry("Changed Password");
					redir("account.php", "Password Change Successful! Your new password is <b>$new_password</b>");
				}
				else {
					redir("account.php", "Password Change Failed.");
				}
			}
		}
		else {
			redir("account.php", "You have not repeated your new password correctly!");
		}
	}
}
elseif ($POST_action == "Change Gender") {
	// Checks that you do not have bard/dancer on account
	$query = sprintf(CHECK_BARD, $STORED_id);
	$result = execute_query($query, "account.php");
	
	if ($CONFIG_server_type == 0) {
		$log_gender = $POST_gender;
		$POST_gender = $POST_gender == "F"? 0 : 1;
	}
	else {
		$log_gender = $POST_gender;
	}
		
	if ($result->RowCount() == 0) {
		$query = sprintf(UPDATE_SEX, $POST_gender, $STORED_id);
		$result = execute_query($query, "account.php");
		add_user_entry("Changed Account to $log_gender");
		redir("index.php", "Account Updated! Bringing you to Home Page");
	}
	else {
		redir("index.php", "You cannot change gender if you have a Bard/Dancer!");
	}
}
elseif ($POST_action == "Change Email") {
	$query = sprintf(UPDATE_EMAIL, $POST_new_email, $STORED_id);
	$result = execute_query($query, "account.php");
	add_user_entry("Changed Email to $POST_new_email");
	redir("index.php", "Email Changed! Bringing you to Home Page");
}
require 'footer.inc';
?>