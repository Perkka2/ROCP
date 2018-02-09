<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$GET_action) {
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>Clear Account</td>
	</tr>
	<tr class=myheader>
		<td>
	Clear an account! Enter the account name, and it will delete all data associated with it.
	There will be a confirmation screen if you make a mistake.
		</td>
	</tr>
	";
	
	$query = SHOW_BAN_LIST;
	$result = execute_query($query, "clear_banned.php");
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td>There are no banned accounts!</td>
	</tr>
		";
	}
	else {
		echo "
	<tr class=myheader>
		<td>The following accounts are banned:</td>
	</tr>
		";
		
		while ($line = $result->FetchRow()) {
			echo "
	<tr class=mycell>
		<td>{$line[0]}</td>
	</tr>
			";
		}
		echo "
	<tr class=mycell>
		<td>
		<form action=\"clear_banned.php\" method=\"GET\">
		Account Name: <input type=\"text\" class=\"myctl\" name=\"account_name\">
		<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Clear Account\"><br>
		</form>
		</td>
	</tr>
		";
	}
	echo "
</table>
	";
}
elseif ($GET_action == "Clear Account") {
	$clear_id = UserID_To_AccountID($GET_account_name);
	$query = sprintf(CHECK_IF_UNBANNED, $clear_id);
	$result = execute_query($query, "clear_banned.php");
	if ($result->RowCount() == 0) {
		redir("clear_banned.php", "$account_search is not banned!");
	}
	else {
		echo "
		You are going to clear $GET_account_name.<br>
		<form action=\"clear_banned.php\" method=\"GET\">
		There will be no confirmation screen, be absolutely sure that you are going to clear this account.<p>
		<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Clear This Account!\"><br>
		<input type=\"hidden\" class=\"myctl\" name=\"clear_account\" value=\"$clear_id\"><br>
		</form>
		";
	}
}
elseif ($GET_action == "Clear This Account!") {
	$account_name = AccountID_To_UserID($GET_clear_account);
	clear_account($GET_clear_account);
	add_admin_entry("Deleted Account $account_name");
	redir("clear_all_banned.php", "Deleted Banned Account $account_name");
}
require 'footer.inc';
?>