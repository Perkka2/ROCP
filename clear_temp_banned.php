<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if (!$GET_action) {
	echo "If you have many users who are banned from hammering the server, and you wish to clear these bans, use this.";
	echo "<p>";
	$query = "SELECT * FROM `ipbanlist` WHERE reason LIKE '%Password error ban:%'";
	$result = execute_query($query, "clear_temp_banned.php");
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=5>Banned for Hammering the Server</td>
	</tr>
	<tr class=myheader>
		<td>Ban ID</td>
		<td>IP</td>
		<td>Ban Time</td>
		<td>Unban Time</td>
		<td>Reason</td>
	</tr>
	";
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=5>None</td>
	</tr>
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
		echo "<form action=\"\" method=\"GET\">";
		echo "<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Clear Bans\"><br>\n";
		echo "</form>";
	}
}
else {
	$query = "DELETE FROM `ipbanlist` WHERE reason LIKE '%Password error ban:'";
	$result = execute_query($query, "clear_temp_banned.php");
	if ($link->Affected_Rows() > 0) {
		add_admin_entry("Cleared Temp Bans");
		redir("index.php", "Cleared Temp Bans!");
	}
}
require 'footer.inc';
?>