<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if ($GET_guild != "") {
	ShowGuildInfo($GET_guild);
}

// Displays Guild Ladder
$query = SHOW_GUILD_LADDER;
$result = execute_query($query, "guild_standings.php", $CONFIG_display_guild_limit, 0);
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=7>$CONFIG_server_name Guild Ladder</td>
	</tr>
	<tr class=myheader>
		<td>Emblem</td>
		<td>Guild</td>
		<td>Master</td>
		<td>Guild Level</td>
		<td>Members</td>
		<td>Average Level</td>
		<td>Total EXP</td>
	</tr>
";
if ($result->RowCount() == 0) {
	echo "<tr class=mycell><td colspan=7>No guilds exist yet!</td></tr>";
}
else {
	while ($line = $result->FetchRow()) {
		$display_emblem_id = md5($line[0] . $CONFIG_passphrase);
		echo "
		<tr class=mycell>
		";
		if ($CONFIG_load_guild_emblem)  {
			$emblem_location = "emblem/emblem.php?id=$display_emblem_id";
		}
		else {
		$emblem_location = "emblem/$display_emblem_id.bmp";
			if (!file_exists($emblem_location)) {
				$emblem_location = "emblem/none.gif";
			}
		}
		echo "
			<td>
				<img src=\"$emblem_location\">
			</td>
		";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				continue;
			}
			elseif ($display_index == 1) {
				$display_emblem_id = md5($line[0] . $CONFIG_passphrase);
				$col_value = "<a href=\"guild_standings.php?guild=$display_emblem_id\">$col_value</a>";
			}
			elseif ($display_index == 4) {
				$col_value = "{$line[4]}/{$line[5]}";
			}
			elseif ($display_index == 5) {
				continue;
			}
			echo "
			<td>$col_value</td>";
		}
		echo "
		</tr>";
	}
}
echo "
</table>
<p>";
// Display Castle Status
$query = SHOW_GUILD_CASTLES;
$result = execute_query($query, "guild_standings.php");
EchoHead(80);
echo "
	<tr class=mytitle>
		<td colspan=3>$CONFIG_server_name Guild Castle Standings</td>
	</tr>
	<tr class=myheader>
		<td>Castle</td>
		<td>Guild</td>
		<td>Emblem</td>
	</tr>
";
if ($result->RowCount() == 0) {
	echo "
	<tr class=mycell>
		<td colspan=3>No castles have been taken yet!</td>
	</tr>
</table>";
}
else {
	while ($line = $result->FetchRow()) {
		$display_emblem_id = md5($line[1] . $CONFIG_passphrase);
		echo "<tr class=mycell>\n";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				if ($CONFIG_server_type != 0) {
					$col_value = determine_castle($col_value);
				}
			}
			elseif ($display_index == 1) {
				if ($col_value == 0) {
					$col_value = "None";
				}
				else {
					$col_value = $line[2];
				}
			}
			elseif ($display_index == 2) {
				continue;
			}
			echo "<td>$col_value</td>\n";
		}
		$emblem_location = "emblem/$display_emblem_id.bmp";
		if (!file_exists($emblem_location)) {
			$emblem_location = "emblem/none.bmp";
		}
		echo "
			<td>
				<img src=\"$emblem_location\">
			</td>
		";
		echo "</tr>\n";
	}
	echo "</table>\n";
}

require 'footer.inc';

?>