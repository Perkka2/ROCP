<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$query = SHOW_OIGNORED;
$result = execute_query($query, "whosonline_ignore.php");
if ($GET_del > 0) {
	$query = sprintf(DEL_OIGNORED, $GET_del);
	$result = execute_query($query, "whosonline_ignore.php");
	add_admin_entry("Removed Account $GET_del to Who\'s Online Ignored List");
	redir("whosonline_ignore.php", "Removed account $GET_del from Who's Online ignore!");
}
elseif ($GET_add > 0) {
	$query = sprintf(ADD_OIGNORED, $GET_add);
	$result = execute_query($query, "whosonline_ignore.php");
	add_admin_entry("Added Account $GET_add to Who\'s Online Ignored List");
	redir("whosonline_ignore.php", "Added account $GET_add to Who's Online ignore!");
}
elseif ($GET_account_name) {
	$id = UserID_To_AccountID($GET_account_name);
	if ($id) {
		$query = sprintf(ADD_OIGNORED, $id);
		$result = execute_query($query, "whosonline_ignore.php");
		add_admin_entry("Added Account $id to Who\'s Online Ignored List");
		redir("whosonline_ignore.php", "Added account $id to Who's Online ignore!");
	}
	else {
		redir("whosonline_ignore.php", "Account $GET_account_name doesn't exist!");
	}
}
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=3>Ignored From Who's Online</td>
	</tr>
	<tr class=myheader>
		<td>Account ID</td>
		<td>Account Name</td>
		<td>Remove</td>
	</tr>
";
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=3>No accounts are ignored from Who's Online!</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "
		<tr class=mycell>
			<td>{$line[0]}</td>
			<td><a href=\"account_manage.php?search={$line[1]}\">{$line[1]}</a></td>
			<td><a href=\"whosonline_ignore.php?del={$line[0]}\">Remove</a></td>
		</tr>";
	}
}
echo "
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr class=mycell>
		<td colspan=3>
		<form action=\"whosonline_ignore.php\" method=\"GET\">
		Add Account:
		<input type=\"text\" name=\"account_name\" class=\"myctl\">
		<input type=\"submit\" name=\"addignore\" class=\"myctl\" value=\"Add\">
		</form>
		</td>
	</tr>
</table>
";

require 'footer.inc';
?>