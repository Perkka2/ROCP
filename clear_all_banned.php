<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if (!$GET_action) {
	
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>Clear All Banned Accounta</td>
	</tr>
	<tr class=myheader>
		<td>
	This will allow you to permanently delete all accounts that are banned.
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
		<form action=\"clear_all_banned.php\" method=\"GET\">
		<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Clear Information\">
		</form>
		</td>
	</tr>
		";
	}
	echo "
</table>
	";
}
else {
	$query = SHOW_BAN_LIST;
	$result = execute_query($query, "clear_all_banned.php");
	while ($line = $result->FetchRow()) {
		clear_account($line[0]);
		add_admin_entry("Deleted Account {$line[1]}");
		redir("clear_all_banned.php", "Deleted Banned Account {$line[1]}");
	}
}
require 'footer.inc';
?>