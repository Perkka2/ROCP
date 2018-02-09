<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($GET_add != "") {
	$level_string = privilege_string($GET_level);
	$insert_id = UserID_To_AccountID($GET_account_name);
	if ($insert_id > 0) {
		$query = sprintf(CHECK_PREV_PRIVILEGE, $GET_account_name);
		$result = execute_query($query, "privileges.php");
		if ($result->RowCount() > 0) {
			redir("privileges.php", "That account is already in the privilege table!");
		}
		else {
			$query = sprintf(ADD_PRIVILEGE, $insert_id, $GET_level);
			$result = execute_query($query, "privileges.php");
			add_admin_entry("Added $GET_account_name to $level_string Privileges");	
			redir("privileges.php", "Added $GET_account_name to $level_string Privileges!");
		}
	}
	else {
		redir("privileges.php", "$GET_account_name is not a valid account!");
	}
}
elseif ($GET_del != "") {
	$level_string = privilege_string($GET_type);
	$del_name = AccountID_To_UserID($GET_del);
	if ($del_name != "") {
		if ($GET_type == 4) {
			$query = CHECK_LAST_ADMIN;
			$result = execute_query($query, "privileges.php");
			if ($result->RowCount() == 1) {
				redir("privileges.php", "You cannot delete your last admin!");
			}
			else {
				$query = sprintf(DEL_PRIVILEGE, $GET_del);
				$result = execute_query($query, "privileges.php");
				add_admin_entry("Removed $del_name from $level_string Privileges");
				redir("privileges.php", "$del_name Removed from $level_string Privileges!");
			}
		}
		else {
			$query = sprintf(DEL_PRIVILEGE, $GET_del);
			$result = execute_query($query, "privileges.php");
			add_admin_entry("Removed $del_name from $level_string Privileges");
			redir("privileges.php", "$del_name Removed from $level_string Privileges!");
		}
	}
	else {
		redir("privileges.php", "$GET_del is not a valid account!");
	}
}
elseif ($GET_saveedit != "") {
	$level_string = privilege_string($GET_level);
	$edit_name = AccountID_To_UserID($GET_account_id);
	if ($edit_name != "") {
		$query = sprintf(UPDATE_PRIVILEGE, $GET_level, $GET_account_id);
		$result = execute_query($query, "privileges.php");
		if ($link->Affected_Rows() > 0) {
			add_admin_entry("$edit_name Changed to $level_string");
			redir("privileges.php", "$edit_name Changed to $level_string");
		}
		else {
			redir("privileges.php", "No changes made, most likely due to no change in access level.");
		}
	}
	else {
		redir("privileges.php", "$GET_account_id is not a valid account!");
	}
}

EchoHead(50);
echo "
	<tr class=mytitle>
		<td>About Privileges</td>
	</td>
	<tr class=mycell>
		<td>
		Game GM - Same privileges as user. Highlighted as GM in some queries.<br>
		GM - Access to a few additional pages, changing user passwords, etc.<br>
		Admin - Full Access to all pages.
		</td>
	</tr>
</table>
";

EchoHead(50);

if (!$GET_edit) {
	echo "
	<tr class=mytitle>
		<td colspan=4>Add Account</td>
	</tr>
	<tr class=myheader>
		<form action=\"privileges.php\" method=\"GET\">
		<td width=30%>Account Name:</td>
		<td width=20%>
			<select class=\"myctl\" name=\"level\">
	";
	for ($i = 2; $i <= 4; $i++) {
		echo "
				<option value=\"$i\">" . privilege_string($i)
		;
	}
	echo "
			</select>
		</td>
		<td width=30%><input type=\"text\" name=\"account_name\" class=\"myctl\"></td>
		<td width=20%><input type=\"submit\" name=\"add\" class=\"myctl\" value=\"Add\"></td>
		</form>
	</tr>
	</table>
	";
}
else {
	$query = sprintf(PRIVILEGE_EDIT, $GET_edit);
	$result = execute_query($query, "privileges.php");
	$line = $result->FetchRow();
	$select[$line[1]] = " selected";
	echo "
	<tr class=mytitle>
		<td colspan=4>Edit Account</td>
	</tr>
	<tr class=myheader>
		<form action=\"privileges.php\" method=\"GET\">
		<td width=30%>Account Name:</td>
		<td width=20%>
			<select class=\"myctl\" name=\"level\">
	";
	
	for ($i = 2; $i <= 4; $i++) {
		echo "
				<option{$select[$i]} value=\"$i\">" . privilege_string($i) . "</option>"
		;
	}
	
	echo "
			</select>
		</td>
		<td width=30%>{$line[0]}</td>
		<td width=20%><input type=\"submit\" name=\"saveedit\" class=\"myctl\" value=\"Update\"></td>
		<input type=\"hidden\" name=\"account_id\" class=\"myctl\" value=\"{$line[2]}\">
		</form>
	</tr>
</table>
	";
}

echo "
<p>
<table cellspacing=0 align=\"center\" width=80%>
<tr>
	<td>
";
	EchoHead(100);
echo "
	<tr class=mytitle>
		<td colspan=3>Current Page Privileges</td>
	</tr>
	<tr class=myheader>
		<td>Page</td>
		<td>Access Level</td>
	</tr>
";
foreach ($access as $index => $data) {
	$display_string = privilege_string($data);
	echo "
	<tr class=mycell>
		<td><a href=\"$index\">$index</a></td>
		<td>$display_string</td>
	</tr>
	";
}
echo "
	</table>
	</td>
	<td valign=top>
";
	EchoHead(100);
	echo "
	<tr class=mytitle>
		<td colspan=4>Account Privileges</td>
	</tr>
	<tr class=myheader>
		<td>Account</td>
		<td>Access Level</td>
		<td colspan=2>Action</td>
	</tr>
	";
	$query = GET_PRIVILEGE_LIST;
	$result = execute_query($query, "privileges.php");
	while ($line = $result->FetchRow()) {
		$access_string = privilege_string($line[1]);
		echo "
		<tr class=mycell>
			<td>{$line[0]}</td>
			<td>$access_string</td>
			<td><a href=\"privileges.php?edit={$line[2]}\">Edit</td>
			<td><a href=\"privileges.php?del={$line[2]}&type={$line[1]}\">Delete</td>
		</tr>
		";
	}
	
echo "
	</table>
	</td>
</table>
";
require 'footer.inc';
?>