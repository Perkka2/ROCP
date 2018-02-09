<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$query = SHOW_IGNORED;
$result = execute_query($query, "ladder_ignore.php");
if ($GET_del > 0) {
	$query = sprintf(DEL_IGNORED, $GET_del);
	$result = execute_query($query, "ladder_ignore.php");
	add_admin_entry("Removed Account $GET_del to Ladder Ignored List");
	redir("ladder_ignore.php", "Removed account $GET_del from ladder ignore!");
}
elseif ($GET_add > 0) {
	$query = sprintf(ADD_IGNORED, $GET_add);
	$result = execute_query($query, "ladder_ignore.php");
	add_admin_entry("Added Account $GET_add to Ladder Ignored List");
	redir("ladder_ignore.php", "Added account $GET_add to ladder ignore!");
}
elseif ($GET_account_name) {
	$id = UserID_To_AccountID($GET_account_name);
	if ($id) {
		$query = sprintf(ADD_IGNORED, $id);
		$result = execute_query($query, "ladder_ignore.php");
		add_admin_entry("Added Account $id to Ladder Ignored List");
		redir("ladder_ignore.php", "Added account $id to ladder ignore!");
	}
	else {
		redir("ladder_ignore.php", "Account $GET_account_name doesn't exist!");
	}
}
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=3>Ignored From Ladder</td>
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
		<td colspan=3>No accounts are ignored from ladder!</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "
		<tr class=mycell>
			<td>{$line[0]}</td>
			<td><a href=\"account_manage.php?search={$line[1]}\">{$line[1]}</a></td>
			<td><a href=\"ladder_ignore.php?del={$line[0]}\">Remove</a></td>
		</tr>";
	}
}
echo "
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr class=mycell>
		<td colspan=3>
		<form action=\"ladder_ignore.php\" method=\"GET\">
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