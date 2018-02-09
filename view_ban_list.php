<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($CONFIG_server_type > 0) {
	$query = "SELECT * FROM `ipbanlist`";
	$result = execute_query($query, "view_ban_list.php");
	EchoHead(80);
	echo "<table>
		<tr class=mytitle>
			<td colspan=4>Table ipbanlist</td>
		</tr>
		<tr class=myheader>
			<td>IP</td>
			<td>Ban Time</td>
			<td>Unban Time</td>
			<td>Reason</td>
		</tr>
	";
	if ($result->RowCount() == 0) {
		echo "
		<tr class=mycell>
			<td colspan=4>None</td>
		</tr>
	</table>
		";
	}
	else {
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>\n";
			foreach ($line as $col_value) {
				echo "<td>$col_value</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}
$query = SHOW_BAN_LIST;
$result = execute_query($query, "view_ban_list.php");
EchoHead(50);
echo "
	<tr class=mytitle>
		<td  colspan=2>Ban List for $CONFIG_server_name</td>
	</tr>

	<tr class=myheader>
		<td>Ban ID</td>
		<td>Account</td>
	</tr>

";
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td>No Current Bans</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		foreach ($line as $col_value) {
			echo "<td><a href=\"account_manage.php?search=$col_value\">$col_value</a></td>\n";
		}
		echo "</tr>\n";
	}
}
echo "</table>\n";
require 'footer.inc';
?>
