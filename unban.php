<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$GET_action && !$POST_action) {
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td>Unban Players/Accounts</td>
	</tr>
	<tr class=myheader>
		<td>
			Unban a player! Enter the character name, and you can ban the account that it came from, or just ban the account.
		</td>
	</tr>

	<tr class=myheader>
		<td>
			There will be a confirmation screen if you make a mistake.
		</td>
	</tr>
	<tr class=mycell>
		<td>
			<form action=\"unban.php\" method=\"GET\">
				Character Name: <input type=\"text\" class=\"myctl\" name=\"character_name\">\n
				<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Unban Character\"><br>\n
				Account Name: <input type=\"text\" class=\"myctl\" name=\"account_name\">\n
				<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Unban Account\"><br>\n
			</form>
		</td>
	</tr>
</table>
	";
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>Ban List</td>
	</tr>
	";
	$query = SHOW_BAN_LIST;
	$result = execute_query($query, "unban.php");
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td>There are no banned accounts!</td>
	</tr>
		";
	}
	else {
		while ($line = $result->FetchRow()) {
			echo "
	<tr class=mycell>
		<td>{$line[0]}</td>
	</tr>
			";
		}
	}
	echo "
</table>
	";
}
elseif ($POST_action == "Unban This Account!") {
	$ban_id = UserID_To_AccountID($POST_account_name);
	$reason = $POST_reason;
	$query = sprintf(UNBAN_ACCOUNT, $ban_id);
	$result = execute_query($query, "unban.php");
	add_admin_entry('Unbanned ' . $POST_account_name, $reason);
	add_unban_entry($POST_account_name, $reason);
	redir("index.php", $POST_account_name . " Unbanned!");
}
else {
	if ($GET_action == "Unban Character") {
		if ($GET_character_name != "") {
			$unban_account = account_of_character($GET_character_name);
		}
	}
	elseif ($GET_action == "Unban Account") {
		if ($GET_account_name != "") {
			$unban_account = $GET_account_name;
		}
	}
	if ($unban_account != "") {
		display_unban($unban_account);
	}
}
require 'footer.inc';
?>
