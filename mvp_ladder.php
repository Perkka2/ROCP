<?php
require 'memory.php';
require 'header.inc';
echo "<table>";
check_auth($_SERVER['PHP_SELF']); // checks for required access

// Aegis doesn't log the actual MVP being killed, so this ladder cannot link to any other queries

if (($CONFIG_server_type == 2) && (!$CONFIG_db_logs)) {
	redir("index.php", $lang['mvpcantdisplay']);
}

if ($GET_id) {
	if (strlen($GET_id) != 32) {
		add_exploit_entry('Possible SQL injection attempt in mvp_ladder.php');
		redir("index.php", "Invalid Character to search!");
	}
}
if ($GET_mob_id) {
	if (strlen($GET_mob_id) > 5) {
		add_exploit_entry('Possible SQL injection attempt in mvp_ladder.php');
		redir("index.php", "Invalid Character to search!");
	}
}

$query = GET_MVP_DATE;
$result = execute_query($query, "mvp_ladder.php", 1, 0);
$line = $result->FetchRow();
if ($line[0]) {
	$start_date = convert_date($line[0]);
}
else {
	$start_date = "N/A";
}

if ($CONFIG_server_type != 2) { $logs = 'log'; } else { $logs = $CONFIG_db_logs; }

if (!$GET_id && !$GET_mob_id) {
	$query = SHOW_MVP;
}
elseif ($GET_id) {
	$query = "
	SELECT monster_id, mob_db.Name2, count(*) AS MVP 
	FROM $logs.mvplog
	LEFT JOIN `mob_db` ON mob_db.ID = $logs.mvplog.monster_id
	WHERE md5(CONCAT(mvplog.kill_char_id, '$CONFIG_passphrase')) = '$GET_id'
	GROUP BY $logs.mvplog.monster_id
	ORDER BY MVP DESC
	";
}
else {
	global $cp;
	$query = "
	SELECT kill_char_id, char.name, count(*) AS MVP 
	FROM $logs.mvplog
	LEFT JOIN `char` ON char.char_id = $logs.mvplog.kill_char_id
	LEFT JOIN $cp.ladder_ignore ON $cp.ladder_ignore.account_id = char.account_id
	WHERE $cp.ladder_ignore.account_id IS NULL AND mvplog.monster_id = '$GET_mob_id'
	GROUP BY $logs.mvplog.kill_char_id
	ORDER BY MVP DESC
	";
}
$result = execute_query($query, "mvp_ladder.php");
EchoHead(80);
echo "
<tr class=mytitle>
	<td colspan=5>$CONFIG_server_name MVP Ladder as of: $start_date</td>
</tr>
<tr class=mytitle>
	<td colspan=5>
	Note: These are based off of logs. When logs are cleared, so is this.
";
if ($CONFIG_server_type == 0) {
	echo "
	These statistics are only based off of MVP prizes, EXP rewards are not counted.
	";
}
echo "
	</td>
</tr>
";
if (!$GET_id && !$GET_mob_id) {
	echo "
<tr class=myheader>
	<td>Character Name</td>
	<td>Class</td>
	<td>Level</td>
	<td>Job Level</td>
	<td># Of MVPs</td>
</tr>
	";
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=5>None</td>
	</tr>
		";
	}
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				continue;
			}
			elseif ($display_index == 1) {
				$char_id = md5($line[0] . $CONFIG_passphrase);
				if ($CONFIG_server_type > 0) {
					$col_value = "<a href=\"mvp_ladder.php?id=$char_id\">$col_value</a>";
				}
			}
			elseif ($display_index == 2) {
				$col_value = determine_class($col_value);
			}
			echo "<td>$col_value</td>";
		}
		echo "</tr>";
	}
}
elseif ($GET_id) {
	echo "
<tr class=myheader>
	<td>Monster</td>
	<td># Of Times</td>
</tr>
	";
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				continue;
			}
			elseif ($display_index == 1) {
				$col_value = "<a href=\"mvp_ladder.php?mob_id={$line[0]}\">$col_value</a>";
			}
			echo "<td>$col_value</td>";
		}
		echo "</tr>";
	}
}
else {
	echo "
<tr class=myheader>
	<td>Player</td>
	<td># Of Times</td>
</tr>
	";
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				continue;
			}
			elseif ($display_index == 1) {
				$char_id = md5($line[0] . $CONFIG_passphrase);
				$col_value = "<a href=\"mvp_ladder.php?id=$char_id\">$col_value</a>";
			}
			echo "<td>$col_value</td>";
		}
		echo "</tr>";
	}
}
echo "</table>";
require 'footer.inc';
?>
