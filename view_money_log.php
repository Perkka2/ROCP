<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$query = VIEW_MONEY_LOG;
$result = execute_query($query, "view_money_log.php");
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=5>Money Transfer Log for $CONFIG_server_name</td>
	</tr>
	<tr class=myheader>
		<td>Action #</td>
		<td>Time/Date</td>
		<td>From</td>
		<td>To</td>
		<td>Action</td>
	</tr>
";
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=5>No actions have been taken!</td>
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
			elseif ($display_index == 2 or $display_index == 3) {
				$char_id = $col_value;
				if ($char_name == "") {
					$display = $char_id;
				}
				else {
					$display = $char_name;
				}
				$col_value = $display;
			}
			echo "<td>$col_value</td>\n";
		}
		echo "</tr>\n";
	}
}
echo "</table>\n";
require 'footer.inc';
?>
