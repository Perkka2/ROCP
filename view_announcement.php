<?php
require 'memory.php';
require 'header.inc';
switch ($GET_viewtype) {
	case 1:
	$table = "user_announce";
	break;
	case 2:
	$table = "gm_announce";
	break;
	case 3:
	$table = "admin_announce";
	break;
}

$announce_string = privilege_string($GET_viewtype);

if ($GET_viewtype > $STORED_level) {
	redir("index.php", "You cannot view these announcements!");
}
else {
	// Displays User announcement options
	EchoHead(80);
	echo "
		<tr class=mytitle>
			<td colspan=4>$announce_string Announcements</td>
		</tr>
		<tr class=myheader>
			<td>Post ID</td>
			<td>Date</td>
			<td width=50%>Message</td>
			<td>Poster</td>
		</tr>
	";
	$query = sprintf(VIEW_ANNOUNCE, $table);
	$result = execute_query($query, "view_announcement.php");
	while ($line = $result->FetchRow()) {
		echo "
	<tr class=mycell>
		";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 1) {
				$col_value = convert_date($col_value);
			}
			echo "
		<td>
			$col_value 
		</td>
			";
		}
		echo "
   	</tr>
		";
	}
	echo "
</table>
	";
}
require 'footer.inc';
?>