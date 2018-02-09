<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if ($GET_auth) {
	$query = sprintf(AUTH_PENDING, $GET_auth);
	$result = execute_query($query, "pending.php");
	$line = $result->FetchRow();
	// Adds account, skips the encryption
	add_account($line[2], $line[3], $line[4], $line[5], $CONFIG_default_level, true);
	$query = sprintf(DEL_PENDING, $GET_auth);
	$result = execute_query($query, "pending.php");
	add_admin_entry("Accepted Pending Registration for {$line[2]}");
	redir("pending.php", "Pending Account Accepted!");
}
elseif ($GET_del) {
	$query = sprintf(DEL_PENDING, $GET_del);
	$result = execute_query($query, "pending.php");
	add_admin_entry("Removed Pending Registration");
	redir("pending.php", "Pending Account Removed!");
}
elseif ($GET_action == "authall") {
	$query = VIEW_PENDING;
	$result = execute_query($query, "pending.php");
	while ($line = $result->FetchRow()) {
		// Adds account, skips the encryption
		add_account($line[2], $line[3], $line[4], $line[5], $CONFIG_default_level, true);
	}
	$query = DEL_ALL_PENDING;
	$result = execute_query($query, "pending.php");
	add_admin_entry("Accepted All Pending Registration");
	redir("pending.php", "All Pending Accounts Accepted!");
}
elseif ($GET_action == "delall") {
	$query = DEL_ALL_PENDING;
	$result = execute_query($query, "pending.php");
	add_admin_entry("Removed All Pending Registrations");
	redir("pending.php", "All Pending Accounts Removed!");
}
EchoHead(100);
echo "
	<tr class=mytitle>
		<td colspan=8>Pending Registration</td>
	</tr>
	<tr class=myheader>
		<td>Action</td>
		<td>Date</td>
		<td>Auth Code</td>
		<td>Account Name</td>
		<td>Password</td>
		<td>Gender</td>
		<td>Email</td>
		<td>IP</td>
	</tr>
";
$query = VIEW_PENDING;
$result = execute_query($query, "pending.php");
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=8>There are no pending registrations!</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "
		<tr class=mycell>
			<td>
			<a href=\"pending.php?auth={$line[1]}\">Auth</a> 
			- 
			<a href=\"pending.php?del={$line[1]}\">Delete</a>
			</td>
		";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				$col_value = convert_date($col_value);
			}
			echo "<td>$col_value</td>";
		}
		echo "</tr>";
	}
	echo "
	<tr class=mycell>
		<td colspan=8>
			<a href=\"pending.php?action=authall\">Auth All</a>
			- 
			<a href=\"pending.php?action=delall\">Delete All</a>
		</td>
	</tr>
	";
}
echo "
</table>
";
require 'footer.inc';
?>