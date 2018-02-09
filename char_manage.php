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
if ($GET_option == "search") {
	require 'item_functions.php';
	$source_id_type = $CONFIG_server_type == 0? "GID" : "char_id";
	display_source_id_items($source_id_type, $GET_char_id);
	require 'footer.inc';
	exit();
}
// Searches for character if requested
if ($GET_search != "") {
	$query = sprintf(CHAR_COUNT, sprintf(CHAR_COUNT_CONDITION_NAME, $GET_search));
	$result = execute_query($query, "char_manage.php");
	$line = $result->FetchRow();
	$total_results = $line[0];
	$query = sprintf(CHAR_LIST, sprintf(CHAR_SEARCH, $GET_search));
	display_char_table($query, $total_results, "search", $GET_search, false, $size, $start, true);
	$query = sprintf(CHAR_COUNT, "");
	$result = execute_query($query, "char_manage.php");
	$line = $result->FetchRow();
	$total_results = $line[0];
	$query = sprintf(CHAR_LIST, CHAR_SHOW_LIST);
	echo "<p>";
	display_char_table($query, $total_results, "none", "none", false, $size, $start);
}
elseif ($GET_option == "deletechar") {
	$name = CharID_To_CharName($GET_char_id);
	echo "Are you sure you want to delete $name?<p>";
	echo "
	<form action=\"char_manage.php\" method=\"GET\">
	<input type=\"hidden\" name=\"delchar_id\" class=\"myctl\" value=\"$GET_char_id\">
	<input type=\"submit\" name=\"delete\" class=\"myctl\" value=\"Delete\">
	</form>
	";
}
elseif ($POST_finishedit == "Edit This Character!") {
	// collects the binary values for the various states
	$effect_value = 0;
	$body_value = 0;
	$health_value = 0;
	foreach ($_POST as $index => $value) {
		if (strpos($index, "effect_") === 0) {
			$effect_value += $value;
		}
		elseif (strpos($index, "body_") === 0) {
			$body_value += $value;
		}
		elseif (strpos($index, "health_") === 0) {
			$health_value += $value;
		}
	}
	
	// Compare every value with the database value
	$edit_variables = array("Character ID", "Account ID", "Char Slot", "Name", "Class", 
	"Base Level", "Job Level", "Zeny", "STR", "AGI", "VIT", "INT", "DEX", "LUK", "Max HP",
	"Max SP", "Stat Points", "Skill Points", "Position", "Po-X", "Po-Y", "Save Position",
	"Sv-X", "Sv-Y");
	$query = sprintf(CHAR_SHOW_EDIT, $POST_var[0]);
	$result = execute_query($query, "account_manage.php");
	$line = $result->FetchRow();
	foreach ($line as $index => $col_value) {
		if ($index == 24) {
			// Handle indexes past 24 separately
			break;
		}
		if ($col_value != $POST_var[$index]) {
			$log_message = "Changed {$edit_variables[$index]} of {$line[3]}: $col_value to {$POST_var[$index]}";
			add_admin_entry($log_message);
		}
	}
	if ($line[25] != $effect_value) {
		$original_value = bindec($line[25]);
		$log_message = "Changed Effect Value of {$line[3]}: $original_value to $effect_value";
		add_admin_entry($log_message);
	}
	if ($CONFIG_server_type == 0) {
		if ($line[26] != $body_value) {
			$original_value = bindec($line[26]);
			$log_message = "Changed Body Value of {$line[3]}: $original_value to $body_value";
			add_admin_entry($log_message);
		}
		if ($line[27] != $health_value) {
			$original_value = bindec($line[27]);
			$log_message = "Changed Health Value of {$line[3]}: $original_value to $health_value";
			add_admin_entry($log_message);
		}
	}
	
	if ($CONFIG_server_type == 0) {
		$query = sprintf(CHAR_EDIT, $POST_var[2], $POST_var[3], $POST_var[4], $POST_var[5],
		$POST_var[6], $POST_var[7], $POST_var[8], $POST_var[9], $POST_var[10], $POST_var[11], 
		$POST_var[12], $POST_var[13], $POST_var[14], $POST_var[15], $POST_var[16], $POST_var[17],
		$POST_var[18], $POST_var[19], $POST_var[20], $POST_var[21], $POST_var[22], $POST_var[23],
		$effect_value, $body_value, $health_value, $POST_var[0]);
	}
	else {
		$query = sprintf(CHAR_EDIT, $POST_var[2], $POST_var[3], $POST_var[4], $POST_var[5],
		$POST_var[6], $POST_var[7], $POST_var[8], $POST_var[9], $POST_var[10], $POST_var[11], 
		$POST_var[12], $POST_var[13], $POST_var[14], $POST_var[15], $POST_var[16], $POST_var[17],
		$POST_var[18], $POST_var[19], $POST_var[20], $POST_var[21], $POST_var[22], $POST_var[23],
		$effect_value, $POST_var[0]);
	}
	$result = execute_query($query, "char_manage.php");
	redir("char_manage.php","Character Updated! Bringing you to Character Management");
}
elseif ($GET_delete == "Delete") {
	$char_name = CharID_To_CharName($GET_delchar_id);
	clear_character($GET_delchar_id);
	add_admin_entry("Deleted Character $char_name");
	redir("char_manage.php", "Deleted Character $char_name");
}
elseif ($GET_option == "editchar" && $GET_char_id != "") {
	$query = sprintf(CHAR_SHOW_EDIT, $GET_char_id);
	display_edit_table($query);
}
elseif ($GET_class != "") {
	$query = sprintf(CHAR_COUNT, sprintf(CHAR_COUNT_CONDITION_CLASS, $GET_class));
	$result = execute_query($query, "char_manage.php");
	$line = $result->FetchRow();
	$total_results = $line[0];
	$query = sprintf(CHAR_LIST, sprintf(CHAR_SORT_CLASS, $GET_class));
	display_char_table($query, $total_results, "class", $GET_class, true, $size, $start);
}
elseif ($GET_sort != "") {
	$query = sprintf(CHAR_COUNT, "");
	$result = execute_query($query, "char_manage.php");
	$line = $result->FetchRow();
	$total_results = $line[0];
	$query = sprintf(CHAR_LIST, sprintf(CHAR_SORT, $GET_sort));
	display_char_table($query, $total_results, "sort", $GET_sort, true, $size, $start);
}
else {
	$query = sprintf(CHAR_COUNT, "");
	$result = execute_query($query, "char_manage.php");
	$line = $result->FetchRow();
	$total_results = $line[0];
	$query = sprintf(CHAR_LIST, CHAR_SHOW_LIST);
	display_char_table($query, $total_results, "none", "none", true, $size, $start);
}

require 'footer.inc';

function display_char_table(
$input_query, $number_of_results, $search_index = "none", $search_value = "none",
$display_search = true, $size = 0, $start = 0, $highlight = false
) 
{
	global $CONFIG_admin_colour, $CONFIG_gm_colour, $CONFIG_game_gm_colour,
	$CONFIG_results_per_page, $CONFIG_server_type;
	EchoHead(100);
	if ($CONFIG_server_type == 0) {
		echo "
	<tr class=mytitle>
		<td colspan=17>Character Table</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Character ID</td>
		<td>Account Name</td>
		<td>Slot</td>
		<td>Name</td>
		<td>Class</td>
		<td><a href=\"char_manage.php?sort=clevel\">Base Level</a></td>
		<td><a href=\"char_manage.php?sort=joblevel\">Job Level</a></td>
		<td><a href=\"char_manage.php?sort=money\">Zeny</a></td>
		<td><a href=\"char_manage.php?sort=STR\">STR</a></td>
		<td><a href=\"char_manage.php?sort=AGI\">AGI</a></td>
		<td><a href=\"char_manage.php?sort=VIT\">VIT</a></td>
		<td><a href=\"char_manage.php?sort=INT\">INT</a></td>
		<td><a href=\"char_manage.php?sort=DEX\">DEX</a></td>
		<td><a href=\"char_manage.php?sort=LUK\">LUK</a></td>
		<td><a href=\"char_manage.php?sort=maxhp\">HP</a></td>
		<td><a href=\"char_manage.php?sort=maxsp\">SP</a></td>
	</tr>
		";
	}
	else {
		echo "
	<tr class=mytitle>
		<td colspan=17>Character Table</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Character ID</td>
		<td>Account Name</td>
		<td>Slot</td>
		<td>Name</td>
		<td>Class</td>
		<td><a href=\"char_manage.php?sort=base_level\">Base Level</a></td>
		<td><a href=\"char_manage.php?sort=job_level\">Job Level</a></td>
		<td><a href=\"char_manage.php?sort=zeny\">Zeny</a></td>
		<td><a href=\"char_manage.php?sort=str\">STR</a></td>
		<td><a href=\"char_manage.php?sort=agi\">AGI</a></td>
		<td><a href=\"char_manage.php?sort=vit\">VIT</a></td>
		<td><a href=\"char_manage.php?sort=int\">INT</a></td>
		<td><a href=\"char_manage.php?sort=dex\">DEX</a></td>
		<td><a href=\"char_manage.php?sort=luk\">LUK</a></td>
		<td><a href=\"char_manage.php?sort=max_hp\">HP</a></td>
		<td><a href=\"char_manage.php?sort=max_sp\">SP</a></td>
	</tr>
		";
	}
	
	$result = execute_query($input_query, "char_manage.php", $size, $start);
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=17>None</td>
	</tr>
		";
		return 0;
	}
		
	while ($line = $result->FetchRow()) {
		$account = $line[1];
		$access_type = $line[17];
		$bold_start = "";
		$bold_end = "";
		if ($CONFIG_server_type > 0) {
			if ($line[18] == 1) {
				$bold_start = "<b>";
				$bold_end = "</b>";
			}
		}
		
		echo "<tr class=mycell>\n";
		echo "
	<td>
		<form action=\"char_manage.php\" method=\"GET\">
			<select class=\"myctl\" name=\"option\">
				<option value=editchar>Edit
				<option value=deletechar>Delete
				<option value=search>Items
			</select>
			<input type=\"submit\" value=\"Go\" class=\"myctl\">
			<input type=\"hidden\" name=\"char_id\" value=\"{$line[0]}\">
		</form>
	</td>
		";
		$account_name = $line[2];
		foreach ($line as $display_index => $col_value) {
			$col_value = htmlspecialchars($col_value);
			if ($highlight) {
				$col_value = highlight_search_term($col_value, $_GET['search']);
			}
			if ($display_index == 1) {
				$account_name = htmlspecialchars($account_name);
				$col_value = "<a href=\"account_manage.php?search={$line[2]}\" title=\"AccountID: {$line[1]}\">{$line[2]}</a>";
			}
			elseif ($display_index == 2) {
				continue;
			}
			elseif ($display_index == 5) {
				$class_name = determine_class($col_value);
				$col_value = "<a href=\"char_manage.php?class=$col_value\">$class_name</a>";
			}
			elseif ($display_index == 17) {
				continue;
			}
			elseif ($CONFIG_server_type > 0 && $display_index == 18) {
				continue;
			}
				
			if ($access_type > 1) {
				if ($access_type == 4) {
					$col_value = "<font color=#$CONFIG_admin_colour>$col_value</font>";
				}
				elseif ($access_type == 3) {
					$col_value = "<font color=#$CONFIG_gm_colour>$col_value</font>";
				}
				elseif ($access_type == 2) {
					$col_value = "<font color=#$CONFIG_game_gm_colour>$col_value</font>";
				}
			}
			echo "<td>$bold_start$col_value$bold_end</td>\n";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	// Gets # of results
	$max_pages = intval($number_of_results / $CONFIG_results_per_page) + 1;
	$this_page = $_SERVER['PHP_SELF'];
	if ($max_pages > 1) {
		for ($i = 1; $i < $max_pages; $i++) {
			echo "<a href=\"$this_page?$search_index=$search_value&page=$i\">$i</a>";
			if ($i % 50 == 0) {
				echo "<br>";
			}
			else {
				echo "-";
			}
		}
		echo "<a href=\"$this_page?$search_index=$search_value&page=$i\">$i</a>";
	}
	if ($display_search) {
		echo "
		<form action=\"char_manage.php\" method=\"GET\">
			<table border=0 align=\"center\">
				<tr>
					<td class=\"mytext\" align=\"left\">Search: </td>
					<td><input type=\"text\" name=\"search\" class=\"myctl\"></td>
					<td align=\"left\"><input type=\"submit\" class=\"myctl\" value=\"Search\"></td>
				</tr>
			</table>
		</form>
		";
	}
}

function display_edit_table($input_query) {
	global $CONFIG_server_type;
	$result = execute_query($input_query, "char_manage.php");
	if ($result->RowCount() == 0) {
		echo "No Character Matching was found!";
		return 0;
	}
	$edit_headers = array("Base Level", "Job Level", "Zeny", "STR", "AGI", "VIT", 
	"INT", "DEX", "LUK", "Max HP", "Max SP", "Stat Points", "Skill Points");
	$line = $result->FetchRow();
	$char_id = $line[0];
	$account_id = $line[1];
	$char_num = $line[2];
	$name = $line[3];
	$class = $line[4];
	$blevel = $line[5];
	$jlevel = $line[6];
	$zeny = $line[7];
	$str = $line[8];
	$agi = $line[9];
	$vit = $line[10];
	$int = $line[11];
	$dex = $line[12];
	$luk = $line[13];
	$max_hp = $line[14];
	$max_sp = $line[15];
	$status_point = $line[16];
	$skill_point = $line[17];
	$last_map = $line[18];
	$last_x = $line[19];
	$last_y = $line[20];
	$save_map = $line[21];
	$save_x = $line[22];
	$save_y = $line[23];
	if ($CONFIG_server_type > 0) {
		$online = $line[24] == 1? "<font color=\"green\">Online</font>" : "<font color=\"red\">Offline</font>";
		$online_form = "
		<tr class=mycell>
			<td class=myheader>Online Status</td>
			<td>$online</td>
		</tr>
		";
	}
	$effectstate = $line[25];
	if ($CONFIG_server_type == 0) {
		$effect = array("Sight", "Hide", "Cloak", "Normal Cart", "Falcon", "Peco", "GM Hide",
		"Wooden Cart", "Flower Cart", "Panda Cart", "Final Cart", "Green Head", "Wedding",
		"Ruwach");
		$stop = 14;
	}
	else {
		$effect = array("Sight", "Hide", "Cloak", "Normal Cart", "Falcon", "Peco", "GM Hide",
		"Wooden Cart", "Flower Cart", "Panda Cart", "Final Cart", "Green Head", "Wedding");
		$stop = 13;
	}
	
	$effect_edit = "
	<tr class=myheader>
			<td colspan=2>Effect
			</td>
		</tr>
		<tr class=mycell>
			<td colspan=2>
	";
	for ($i = 0; $i < $stop; $i++) {
		$bit = substr($effectstate, $stop - $i - 1, 1);
		$power = pow(2, $i);
		if ($bit == 0) {
			$effect_edit .= "{$effect[$i]} <input type=\"checkbox\" name=\"effect_$i\" value=$power>\n";
		}
		else {
			$effect_edit .= "{$effect[$i]} <input type=\"checkbox\" name=\"effect_$i\" value=$power checked>\n";
		}
		if ($i == 6) $effect_edit .= "<br>";
	}
	$effect_edit .= "
			</td>
		</tr>
	";
	
	if ($CONFIG_server_type == 0) {
		$bodystate = $line[26];
		$body = array("Stone", "Frozen", "Sleep");
		
		$body_edit = "
		<tr class=myheader>
				<td colspan=2>Body
				</td>
			</tr>
			<tr class=mycell>
				<td colspan=2>
		";
		for ($i = 0; $i < 3; $i++) {
			$bit = substr($bodystate, 2 - $i, 1);
			$power = pow(2, $i);
			if ($bit == 0) {
				$body_edit .= "{$body[$i]} <input type=\"checkbox\" name=\"body_$i\" value=$power>\n";
			}
			else {
				$body_edit .= "{$body[$i]} <input type=\"checkbox\" name=\"body_$i\" value=$power checked>\n";
			}
		}
		$body_edit .= "
				</td>
			</tr>
		";
		
		$healthstate = $line[27];
		$health = array("Poison", "Curse", "Silence", "???", "Blind");
		
		$health_edit = "
		<tr class=myheader>
				<td colspan=2>Health
				</td>
			</tr>
			<tr class=mycell>
				<td colspan=2>
		";
		for ($i = 0; $i < 5; $i++) {
			$bit = substr($healthstate, 4 - $i, 1);
			$power = pow(2, $i);
			if ($bit == 0) {
				$health_edit .= "{$health[$i]} <input type=\"checkbox\" name=\"health_$i\" value=$power value=2>\n";
			}
			else {
				$health_edit .= "{$health[$i]} <input type=\"checkbox\" name=\"health_$i\" value=$power value=2 checked>\n";
			}
		}
		$health_edit .= "
				</td>
			</tr>
		";
		
	}
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td colspan=2>Editing Character: $name</td>
	</tr>
	<form action=\"char_manage.php\" method=\"POST\">
		<tr class=mycell>
			<td class=myheader>Character ID</td>
			<td>$char_id</td>
			<input type=\"hidden\" name=\"var[0]\" class=\"myctl\" value=\"$char_id\">
		</tr>
		<tr class=mycell>
			<td class=myheader>Account ID</td>
			<td>$account_id</td>
			<input type=\"hidden\" name=\"var[1]\" class=\"myctl\" value=\"$account_id\">
		</tr>
		<tr class=mycell>
			<td class=myheader>Char Slot</td>
			<td><input type=\"text\" name=\"var[2]\" class=\"myctl\" value=\"$char_num\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Name</td>
			<td><input type=\"text\" name=\"var[3]\" class=\"myctl\" value=\"$name\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Class</td>
				<td>
					<select class=\"myctl\" name=\"var[4]\">
	";
	for ($i = 0; $i < 4023; $i++) {
		if ($i == 13 or $i == 21 or $i == 4014 or $i == 4022) {
			continue;
		}
		if ($i == 24) {
			$i = 4000;
			continue;
		}
		$display_class = determine_class($i);
		if ($i == $class) {
			echo "<option value=\"$i\" selected>$display_class";
		}
		else {
			echo "<option value=\"$i\">$display_class";
		}
		echo "\n";
	}
	
	echo "
					</select>
				</td>
			</td>
		</tr>
	";
	for ($i = 5; $i < 18; $i++) {
		echo "
		<tr class=mycell>
			<td class=myheader>{$edit_headers[$i - 5]}</td>
			<td><input type=\"text\" name=\"var[$i]\" class=\"myctl\" value=\"{$line[$i]}\"></td>
		</tr>
		";
	}
	echo "
		<tr class=mycell>
			<td class=myheader>Position</td>
			<td><input type=\"text\" name=\"var[18]\" class=\"myctl\" value=\"$last_map\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Co-ordinates</td>
			<td width = \"20\">
			X: <input type=\"text\" name=\"var[19]\" class=\"myctl\" value=\"$last_x\" size=4>
			Y: <input type=\"text\" name=\"var[20]\" class=\"myctl\" value=\"$last_y\" size=4>
			</td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Save Position</td>
			<td><input type=\"text\" name=\"var[21]\" class=\"myctl\" value=\"$save_map\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Co-ordinates</td>
			<td width = \"20\">
			X: <input type=\"text\" name=\"var[22]\" class=\"myctl\" value=\"$save_x\" size=4>
			Y: <input type=\"text\" name=\"var[23]\" class=\"myctl\" value=\"$save_y\" size=4>
			</td>
		</tr>
		$online_form
		$effect_edit
		$body_edit
		$health_edit
		<tr class=mycell>
			<td colspan=2>
				<input type=\"submit\" name=\"finishedit\" class=\"myctl\" value=\"Edit This Character!\">
			</td>
		</tr>
	</form>
</table>
	";
}
?>