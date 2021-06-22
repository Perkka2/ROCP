<?php
// Accessed when the user goes to the home page
require 'memory.php';	// calls memory functions
require 'header.inc';	// brings in header
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($STORED_login == 'CP') {
	redir("privileges.php", "Setting Up Administrator Account");
}
else {
	$welcome = sprintf($lang['welcome'], $CONFIG_panel_name, $CONFIG_server_name);
	echo "
<table align=\"center\" class=\"dashed\" cellpadding='0' cellspacing='0' width='600'>
	<tr class=mycell>
		<td>$welcome $welcomept2</td>
	</tr>
	";
	if ($CONFIG_server_type > 0) {
		// Backups are for athena only

		// Open the backup text file
		$file = fopen("last_backup.txt", "a+");
		$data = fread($file, 2000);
		if (!$data) {
			// File is empty, reset the counter
			fputs($file, 1);
			$data = 1;
		}
		// Determine how long it's been since last update
		$difference = time() - $data;
		// Time duration to display
		$days = floor($difference / 86400);
		$hours = floor(($difference - ($days * 86400)) / 3600);
		$minutes = floor(($difference - ($days * 86400) - ($hours * 3600)) / 60);
		$seconds = floor(($difference - ($days * 86400) - ($hours * 3600) - ($minutes * 60)));
		if ($difference > 60 * 60 * ($CONFIG_backup_interval + 6)) {
			// Over six hours past due backup time
			$bold_tag = "<b><span style='color: red'>";
			$bold_close = "</span></b>";
		}
		elseif ($difference > 60 * 60 * $CONFIG_backup_interval) {
			// Longer than the backup duration
			$bold_tag = "<b>";
			$bold_close = "</b>";
		}
		else {
			// Less than the backup duration
			$bold_tag = "";
			$bold_close = "";
		}
		if ($data == "1") {
			$last_string = $lang['nobackup'];
		}
		else {
			$last_string = sprintf($lang['backup'], $days, $hours, $minutes, $seconds);
		}
		echo "
	<tr class=\"mycell\">
		<td>
			{$bold_tag}$last_string{$bold_close}
		</td>
	</tr>
		";
		if ($STORED_level > 2) {
			// Gives GMs/Admins a chance to backup the server
			echo "
	<tr class=\"mycell\">
		<td>
			<a href=\"backup_server.php?action=backup\">{$lang['backupnow']}</a>
		</td>
	</tr>
			";
		}
	}
	echo "
</table>
	";

	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=3>{$lang['userheader']}</td>
	</tr>
	<tr class=myheader>
		<td width=25%>{$lang['poster']}</td>
		<td width=50%>{$lang['message']}</td>
		<td width=25%>{$lang['date']}</td>
	</tr>
	";
	$query = USER_ANNOUNCE;
	$result = execute_query($query, "home.php", $CONFIG_max_announce);
	while ($line = $result->FetchRow()) {
		echo "
	<tr class=mycell>
		";
		foreach ($line as $display_index => $col_value) {
			$col_value = del_escape($col_value);
			if ($display_index == 2) {
				$col_value = convert_date($col_value);
			}
			switch ($display_index) {
				case 0:
					$width = "25%";
					break;
				case 1:
					$width = "50%";
					break;
				case 2:
					$width = "25%";
					break;
			}
			echo "<td width=$width>$col_value</td>";
		}
		echo "
	</tr>
		";
	}
	echo "
	<tr>
		<td colspan=3 class=mycell>
			<a href='view_announcement.php?viewtype=1'>{$lang['viewmore']}&nbsp;</a>
		</td>
	</tr>
</table>
	";

	if ($STORED_level > 1) {
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=3>{$lang['gmheader']}</td>
	</tr>
	<tr class=myheader>
		<td width=25%>{$lang['poster']}</td>
		<td width=50%>{$lang['message']}</td>
		<td width=25%>{$lang['date']}</td>
	</tr>
		";
		$query = GM_ANNOUNCE;
		$result = execute_query($query, "home.php", $CONFIG_max_announce);
		while ($line = $result->FetchRow()) {
			echo "
		<tr class=mycell>
			";
			foreach ($line as $display_index => $col_value) {
				$col_value = del_escape($col_value);
				if ($display_index == 2) {
					$col_value = convert_date($col_value);
				}
				switch ($display_index) {
					case 0:
						$width = "25%";
						break;
					case 1:
						$width = "50%";
						break;
					case 2:
						$width = "25%";
						break;
				}
				echo "<td width=$width>$col_value</td>";
			}
			echo "
		</tr>
			";
		}
		echo "
	<tr>
		<td colspan=3 class=mycell>
			<a href='view_announcement.php?viewtype=2'>{$lang['viewmore']}&nbsp;</a>
		</td>
	</tr>
</table>
		";
	}

	if ($STORED_level > 3) {
		EchoHead(80);
		echo "
	<tr>
		<td colspan=3 class=mytitle>{$lang['adminheader']}</td>
	</tr>
	<tr class=myheader>
		<td width=25%>{$lang['poster']}</td>
		<td width=50%>{$lang['message']}</td>
		<td width=25%>{$lang['date']}</td>
	</tr>
		";
		$query = ADMIN_ANNOUNCE;
		$result = execute_query($query, "home.php", $CONFIG_max_announce);
		while ($line = $result->FetchRow()) {
			echo "
		<tr class=mycell>
			";
			foreach ($line as $display_index => $col_value) {
				$col_value = del_escape($col_value);
				if ($display_index == 2) {
					$col_value = convert_date($col_value);
				}
				switch ($display_index) {
					case 0:
						$width = "25%";
						break;
					case 1:
						$width = "50%";
						break;
					case 2:
						$width = "25%";
						break;
				}
				echo "<td width=$width>$col_value</td>";
			}
			echo "
		</tr>
			";
		}
		echo "
	<tr>
		<td colspan=3 class=mycell>
			<a href='view_announcement.php?viewtype=3'>{$lang['viewmore']}&nbsp;</a>
		</td>
	</tr>
</table>
		";
	}
	$mapName = ParseMapNameTable("./dbtranslation/mapnametable.txt");
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=6>
			{$lang['yourchars']}
		</td>
	</tr>
	<tr class=myheader>
		<td>{$lang['yourname']}</td>
		<td>{$lang['yourclass']}</td>
		<td>{$lang['yourbase']}</td>
		<td>{$lang['yourjob']}</td>
		<td>{$lang['yourzeny']}</td>
		<td>{$lang['yourmap']}</td>
	</tr>
	";

	$query = sprintf(HOME_CHARS, $STORED_id);
	$result = execute_query($query, 'home.php');
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=5>You have no characters!</td>
	</tr>
		";
	}
	else {
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>";
			foreach ($line as $display_index => $col_value) {
				if ($display_index == 1) {
					$col_value = determine_class($col_value);
				}
				if ($display_index == 5) {
					$col_value = $mapName[$col_value];
				}
				echo "<td>$col_value</td>";
			}
			echo "</tr>";
		}
	}
	echo "</table>";
}

require 'footer.inc';   // displays the header
?>
