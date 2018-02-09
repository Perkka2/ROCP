<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if (!$GET_ip) {
	$query = VIEW_REGISTER_LOG;
}
else {
	$query = sprintf(VIEW_SORT_REGISTER_LOG, $GET_ip);
}

EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=5>Registration Log for $CONFIG_server_name</td>
	</tr>
	<tr class=myheader>
		<td>ID</td>
		<td>Account</td>
		<td>IP</td>
		<td>Time</td>
		<td>Email</td>
	</tr>
";

$result = execute_query($query, "view_register_log.php");
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=5>No actions have been taken!</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 1) {
				$col_value = "<a href=\"account_manage.php?search=$col_value\">$col_value</a>";
			}
			elseif ($display_index == 2) {
				$ip = long2ip($col_value);
				$col_value = "<a href=\"view_register_log.php?ip=$col_value\">$ip</a>";
			}
			elseif ($display_index == 3) {
				$col_value = convert_date($col_value);
			}
			echo "<td>$col_value</td>\n";
		}
		echo "</tr>";
	}
}
echo "</table>\n";
require 'footer.inc';
?>
