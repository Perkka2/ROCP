<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// determines what page to visit if nothing is being searched

if (!$GET_page) {
	$page = 1;
}
else {
	$page = $GET_page;
}
$size = $CONFIG_results_per_page;
$start = ($page * $size) - $size;
if ($GET_option == "infoguild") {
	ShowGuildInfo($GET_guild_id);
}
// Searches for character if requested
if ($GET_search != "") {
	$search_guild = $GET_search;
	$query = sprintf(GUILD_SEARCH, $search_guild);
	display_guild_table($query, true, $search_guild);
	$query = DISPLAY_GUILD_MANAGE;
	display_guild_table($query);
	$display_table = true;
}
elseif ($GET_option == "deleteguild") {
	$delete_guild = GuildID_To_GuildName($GET_guild_id);
	echo "Are you sure you want to delete \"$delete_guild\"?<p>";
	echo "
	<form action=\"guild_manage.php\" method=\"GET\">
	<input type=\"hidden\" name=\"deleteguild\" class=\"myctl\" value=\"$GET_guild_id\">
	<input type=\"submit\" name=\"delete\" class=\"myctl\" value=\"Delete\">
	</form>
	";
}
elseif ($POST_finishedit == "Edit This Guild!") {
	// Compare every value with the database value
	$edit_variables = array("Guild ID", "Guild Name", "Master", "Guild Level", "Guild EXP");
	$query = sprintf(GUILD_SHOW_EDIT, $POST_var[0]);
	$result = execute_query($query, "guild_manage.php");
	$line = $result->FetchRow();
	foreach ($line as $index => $col_value) {
		if ($col_value != $POST_var[$index]) {
			$log_message = "Changed {$edit_variables[$index]} of {$line[1]}: $col_value to {$POST_var[$index]}";
			add_admin_entry($log_message);
		}
	}
	
	$query = sprintf(UPDATE_GUILD, $POST_var[1], $POST_var[3], $POST_var[4], $POST_var[0]);
	$result = execute_query($query, "guild_manage.php");
	redir("guild_manage.php", "Guild Updated! Bringing you to Guild Management");
}
elseif ($POST_finishedit == "Edit This Castle!") {
	$query = sprintf(UPDATE_GUILD_CASTLE, $POST_guild_id, $POST_economy, $POST_defense, 
	$POST_visibleC, $POST_visibleG0, $POST_visibleG1, $POST_visibleG2, $POST_visibleG3, 
	$POST_visibleG4, $POST_visibleG5, $POST_visibleG6, $POST_visibleG7, $POST_editcastle
	);
	$result = execute_query($query, "guild_manage.php");
	add_admin_entry("Edited Castle Information for Castle $POST_editcastle");
	redir("guild_manage.php", "Castle Updated! Bringing you to Guild Management");
}
elseif ($GET_delete == "Delete") {
	$guild_name = GuildID_To_GuildName($GET_deleteguild);
	clear_guild($GET_deleteguild);
	add_admin_entry("Deleted Guild $guild_name");
	redir("guild_manage.php", "Guild $guild_name Deleted!");
}
elseif ($GET_option == "editguild") {
	$query = sprintf(GUILD_SHOW_EDIT, $GET_guild_id);
	display_edit_table($query);
}
elseif ($GET_option == "editcastle") {
	$query = sprintf(CASTLE_SHOW_EDIT, $GET_castle_id);
	display_edit_castle($query, $GET_castle_id);
}
elseif ($GET_option == "emptycastle") {
	$query = sprintf(EMPTY_GUILD_CASTLE, $GET_castle_id);
	$result = execute_query($query, "guild_manage.php");
	add_admin_entry("Cleared Castle $GET_castle_id");
	redir("guild_manage.php", "Castle Cleared! Bringing you to Guild Management");
}
else {
	$query = DISPLAY_GUILD_MANAGE;
	display_guild_table($query);
	$display_table = true;
}

// Gets # of characters on server
$number_of_guilds = GetGuildCount();
$max_pages = intval($number_of_guilds / $CONFIG_results_per_page) + 1;
if ($display_table) {
	if ($max_pages > 1) {
		for ($i = 1; $i < $max_pages; $i++) {
			echo "<a href=\"guild_manage.php?page=$i&search=$GET_search\">$i</a>-";
		}
		echo "<a href=\"guild_manage.php?page=$i&search=$GET_search\">$i</a>";
	}
	echo "
	<form action=\"guild_manage.php\" method=\"GET\">
		<table border=0 align=\"center\">
			<tr>
				<td class=\"mytext\" align=\"left\">
					Search:
					<input type=\"text\" name=\"search\" class=\"myctl\">
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
				</td>
			</tr>
		</table>
		<table border=0>
			<tr>

			</tr>
		</table>
	</form>
	";
}
require 'footer.inc';

function display_guild_table($input_query, $skip_castle = false, $search_term = "") {
	global $CONFIG_server_type, $CONFIG_passphrase;
	EchoHead(100);
	echo "
	<tr class=mytitle>
		<td colspan=10>Guild Table</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Guild ID</td>
		<td>Guild Name</td>
		<td>Guild Master</td>
		<td>Guild Level</td>
	";
	if ($CONFIG_server_type > 0) {
		echo "
		<td>Online Members</td>
		";
	}
	echo "
		<td>Total Members</td>
		<td>Max Slots</td>
		<td>Average Level</td>
		<td>Total EXP</td>
	</tr>
	";
	
	$result = execute_query($input_query, "guild_manage.php");
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=10>No Guild Matching was found!</td>
	</tr>
		";
	}
	
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		echo "
	<td>
			<form action=\"guild_manage.php\" method=\"GET\">
				<select class=\"myctl\" name=\"option\">
					<option value=editguild>Edit
					<option value=infoguild>Information
					<option value=deleteguild>Delete
				</select>
				<input type=\"submit\" value=\"Go\" class=\"myctl\">
				<input type=\"hidden\" name=\"guild_id\" value=\"{$line[0]}\">
			</form>
	</td>
		";
		foreach ($line as $display_index => $col_value) {
			if ($search_term) {
				$col_value = highlight_search_term($col_value, $search_term);
			}
			if ($display_index == 1) {
				$display_guild_id = md5($line[0] . $CONFIG_passphrase);
				$col_value = "<a href=\"guild_standings.php?guild=$display_guild_id\">$col_value</a>";
			}
			elseif ($display_index == 2) {
				$col_value = "<a href=\"char_manage.php?search=$col_value\">$col_value</a>";
			}
			elseif ($display_index == 4) {
				$col_value = "{$line[4]}\n";
			}
			elseif ($display_index == 5) {
				$col_value = "{$line[5]}\n";
			}
			echo "<td>$col_value</td>\n";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	
	if ($skip_castle) {
		return 0;
	}
	
	// Display Castle Status
	$query = DISPLAY_GUILD_CASTLES;
	$result = execute_query($query, "guild_manage.php");
	EchoHead(100);
	echo "
		<tr class=mytitle>
			<td colspan=14>Guild Castles</td>
		</tr>
		<tr class=myheader>
			<td>Options</td>
			<td>Castle</td>
			<td>Guild</td>
			<td>Economy</td>
			<td>Defense</td>
			<td>Kafra</td>
			<td colspan=8>Guardians</td>
	
		</tr>
	";
	if ($result->RowCount() == 0) {
		echo "
		<tr class=mycell>
			<td colspan=14>No castles have been taken yet!</td>
		</tr>
	</table>";
	}
	else {
		while ($line = $result->FetchRow()) {
			if ($CONFIG_server_type == 0) {
				$castle_id = $line[0];
			}
			else {
				$castle_id = $line[0];
			}
			$display_emblem_id = $line[1];
			echo "<tr class=mycell>\n";
			echo "
			<td>
			<form action=\"guild_manage.php\" method=\"GET\">
				<select class=\"myctl\" name=\"option\">
					<option value=editcastle>Edit
					<option value=emptycastle>Empty
				</select>
				<input type=\"submit\" value=\"Go\" class=\"myctl\">
				<input type=\"hidden\" name=\"castle_id\" value=\"$castle_id\">
			</form>
			</td>
			";
			foreach ($line as $display_index => $col_value) {
				if ($CONFIG_server_type > 0 && $display_index == 0) {
					$col_value = determine_castle($col_value);
				}
				elseif ($display_index == 1) {
					if (!$col_value) {
						$col_value = "None";
					}
					else {
						$col_value = $line[1];
					}
				}
				elseif ($display_index > 3 and $display_index <= 13) {
					$col_value = $col_value == 1? "<font color=#00ff00>On</font>" : "<font color=#ff0000>Off</font>";
				}
				echo "<td>$col_value</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
	
}

function display_edit_table($input_query) {
	$result = execute_query($input_query);
	EchoHead(50);
	if ($result->RowCount() == 0) {
		echo "No Guild Matching was found!";
		return 0;
	}
	$line = $result->FetchRow();
	$guild_id = $line[0];
	$name = $line[1];
	$master = $line[2];
	$guild_lv = $line[3];
	$exp = $line[4];
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td colspan=2>Editing Guild: {$line[1]}</td>
	</tr>
	<form action=\"guild_manage.php\" method=\"POST\">
		<tr class=mycell>
			<td class=myheader>Guild ID</td>
			<td>$guild_id</td>
			<input type=\"hidden\" name=\"var[0]\" class=\"myctl\" value=\"$guild_id\">
		</tr>
		<tr class=mycell>
			<td class=myheader>Guild Name</td>
			<td><input type=\"text\" name=\"var[1]\" class=\"myctl\" value=\"$name\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Guild Master</td>
			<td>$master</td>
			<input type=\"hidden\" name=\"var[2]\" class=\"myctl\" value=\"$master\">
		</tr>
		<tr class=mycell>
			<td class=myheader>Guild Level</td>
			<td><input type=\"text\" name=\"var[3]\" class=\"myctl\" value=\"$guild_lv\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>EXP</td>
			<td><input type=\"text\" name=\"var[4]\" class=\"myctl\" value=\"$exp\"></td>
		</tr>
		<tr class=mycell>
			<td colspan=2>
				<input type=\"submit\" name=\"finishedit\" class=\"myctl\" value=\"Edit This Guild!\">
			</td>
		</tr>
	</form>
</table>
	";
}

function display_edit_castle($input_query, $edit_castle) {
	global $CONFIG_server_type;
	$result = execute_query($input_query, "guild_manage.php");
	EchoHead(50);
	if ($result->RowCount() == 0) {
		redir("guild_manage.php", "No Castle Matching was found!");
		return 0;
	}
	$line = $result->FetchRow();
	if ($CONFIG_server_type > 0) {
		$edit_castle = determine_castle($edit_castle);
	}
	$castle_id = $line[0];
	$guild_id = $line[1];
	$economy = $line[2];
	$defense = $line[3];
	$visibleC = $line[4];
	for ($i = 0; $i < 8; $i++) {
		$visibleG[$i] = $line[5 + $i];
	}
	echo "
	<tr class=mytitle>
		<td colspan=2>Editing Castle: $edit_castle</td>
	</tr>
	<form action=\"guild_manage.php\" method=\"POST\">
			<tr class=mycell>
				<td>Guild ID</td>
				<td><input type=\"text\" name=\"guild_id\" class=\"myctl\" value=\"$guild_id\"></td>
			</tr>
			<tr class=mycell>
				<td>Economy</td>
				<td><input type=\"text\" name=\"economy\" class=\"myctl\" value=\"$economy\"></td>
			</tr>
			<tr class=mycell>
				<td>Defense Level</td>
				<td><input type=\"text\" name=\"defense\" class=\"myctl\" value=\"$defense\"></td>
			</tr>
			<tr class=mycell>
				<td>Kafra</td>
				<td>
	";
	if ($visibleC == 0) {
		echo "
					<input type=\"radio\" name=\"visibleC\" class=\"myctl\" value=1> On
					<input type=\"radio\" name=\"visibleC\" class=\"myctl\" value=0 checked> Off
		";
	}
	else {
		echo "
					<input type=\"radio\" name=\"visibleC\" class=\"myctl\" value=1 checked> On
					<input type=\"radio\" name=\"visibleC\" class=\"myctl\" value=0> Off
		";
	}
					
	echo "
				</td>
			</tr>
	";
	
	for ($i = 0; $i < 8; $i++) {
		echo "
			<tr class=mycell>
				<td>Guardian{$i}</td>
				<td>
		";
		if ($visibleG[$i] == 0) {
			echo "
			<input type=\"radio\" name=\"visibleG{$i}\" class=\"myctl\" value=1> On
			<input type=\"radio\" name=\"visibleG{$i}\" class=\"myctl\" value=0 checked> Off
			";
		}
		else {
			echo "
			<input type=\"radio\" name=\"visibleG{$i}\" class=\"myctl\" value=1 checked> On
			<input type=\"radio\" name=\"visibleG{$i}\" class=\"myctl\" value=0> Off
			";
		}
		echo "
				</td>
			</tr>
		";
	}
	echo "
		<tr class=mycell>
			<td colspan=2>
				<input type=\"submit\" name=\"finishedit\" class=\"myctl\" value=\"Edit This Castle!\">
				<input type=\"hidden\" name=\"editcastle\" class=\"myctl\" value=\"$edit_castle\">		
			</td>
		</tr>
	</form>
	";
}

?>