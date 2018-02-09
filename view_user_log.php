<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$query = VIEW_USER_LOG;
$result = execute_query($query, "view_user_log.php");
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=4>User Log for $CONFIG_server_name</td>
	</tr>
	<tr class=myheader>
		<td>Action #</td>
		<td>Time/Date</td>
		<td>User</td>
		<td>Action</td>
	</tr>
";
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=4>No actions have been taken!</td>
	</tr>
	";
}
else {
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 1) {
				$col_value = convert_date($col_value);
			}
			echo "<td>$col_value</td>\n";
		}
		echo "</tr>\n";
	}
}
echo "</table>\n";
require 'footer.inc';
?>
