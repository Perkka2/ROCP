<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$GET_action && !$POST_action) {
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td>Ban Players/Accounts</td>
	</tr>
	<tr class=myheader>
		<td>
			Ban a player! Enter the character name, and you can ban the account that it came from, or just ban the account.
		</td>
	</tr>

	<tr class=myheader>
		<td>
			There will be a confirmation screen if you make a mistake.
		</td>
	</tr>
	<tr class=mycell>
		<td>
			<form action=\"ban.php\" method=\"GET\">
				Character Name: <input type=\"text\" class=\"myctl\" name=\"character_name\">\n
				<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Ban Character\"><br>\n
				Account Name: <input type=\"text\" class=\"myctl\" name=\"account_name\">\n
				<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Ban Account\"><br>\n
			</form>
		</td>
	</tr>
</table>
	";
}
elseif ($POST_action == "Ban This Account!") {
	$ban_id = UserID_To_AccountID($POST_account_name);
	$reason = $POST_reason;
	$query = sprintf(BAN_ACCOUNT, $ban_id);
	$result = execute_query($query, "ban.php");
	add_admin_entry('Banned ' . $POST_account_name, $reason);
	add_ban_entry($POST_account_name, $reason);
	redir("index.php", $POST_account_name . " Banned!");
}
else {
	if ($GET_action == "Ban Character") {
		if ($GET_character_name != "") {
			$ban_account = account_of_character($GET_character_name);
		}
	}
	elseif ($GET_action == "Ban Account") {
		if ($GET_account_name != "") {
			$ban_account = $GET_account_name;
		}
	}
	if ($ban_account != "") {
		display_ban($ban_account);
	}
}
require 'footer.inc';
?>
