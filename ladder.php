<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$max_display = $CONFIG_ladder_limit; // Sets a reasonable limit to what can be selected.
$options = "<option value=1 %s>{$lang['laddersortlevel']}</option>
<option value=2 %s>{$lang['laddersortzeny']}</option>";
$sort_type = LADDER_SORT_DEFAULT;

if ($CONFIG_server_type == 0) {
	$options .= "<option value=3 %s>{$lang['laddersorthonor']}</option>";
}/*
elseif ($CONFIG_server_type == 2){
	$honor = "<option value=3>{$lang['laddersortfame']}";
}*/

if ($GET_action) {
	switch ($GET_search_type) {
		case 1:
			$sort_type = LADDER_SORT_DEFAULT;
			$options = sprintf($options,"selected","","");
			break;
		case 2:
			$sort_type = LADDER_SORT_ZENY;
			$options = sprintf($options,"","selected","");
			break;
		case 3:
			$sort_type = LADDER_SORT_HONOR;
			$options = sprintf($options,"","","selected");
			break;
	}
	if ($GET_action == $lang['ladderdisplay']) {
		$search_class = $GET_search_class;
		if (strlen($search_class) > 5) {
			add_exploit_entry($lang['ladderinject']);
			redir("index.php", $lang['ladderinvalid']);
		}
		if ($search_class == 7 or $search_class == 14 or $search_class == 4008 or $search_class == 4015) {
			$multi = true;
		}
		if ($multi) {
			switch($search_class) {
				case 7:
					$search_class2 = 13;
					break;
				case 14:
					$search_class2 = 21;
					break;
				case 4008:
					$search_class2 = 4014;
					break;
				case 4015:
					$search_class2 = 4022;
					break;				case 4037:					$search_class2 = 4044;					break;				case 4030:					$search_class2 = 4036;					break;
			}
			$query = sprintf(LADDER_SORT_MULTI_CLASS, $search_class, $search_class2,$sort_type);
		}
		else {
			$query = sprintf(LADDER_SORT_CLASS, $search_class,$sort_type);
		}
	}
	elseif ($GET_action == $lang['laddersort']) {
		$query =  sprintf(LADDER_SORT_LEVEL,$sort_type);
	}
}
else {
	$query =  sprintf(LADDER_SORT_LEVEL,$sort_type);
}
display_ladder ($query);
require 'footer.inc';

function display_ladder($input_query) {
	global $STORED_level, $CONFIG_server_type, $CONFIG_server_name, $CONFIG_ladder_limit, $lang, $options;
	// Executes the queries
	if ($input_query == LADDER_SORT_ALL) {
		$result = execute_query($input_query, "ladder.php");
	}
	else {
		$result = execute_query($input_query, "ladder.php", $CONFIG_ladder_limit, 0);
	}

	if ($_GET['search_type'] == 3 && $CONFIG_server_type == 0) {
		$honor_column = "<td>{$lang['ladderhonor']}</td>";
		$header = sprintf($lang['ladderheader2'], $CONFIG_server_name, $CONFIG_ladder_limit);
	}
	else {
		$header = sprintf($lang['ladderheader'], $CONFIG_server_name, $CONFIG_ladder_limit);
	}
	EchoHead(80);
	echo "
	<tr>
		<td class=mytitle colspan=6>$header</td>
	</tr>
	<tr class=mycell>
		<td colspan=5>
		<form action=\"\" method=\"GET\">
			{$lang['laddersorttype']}
			<select name=\"search_type\" class=\"myctl\">
				$options
			</select>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"{$lang['laddersort']}\">
			<br>
			{$lang['ladderdisplaytop']}
			<select name=\"search_class\" class=\"myctl\"5>
	";

for ($i = 0; $i < 4046; $i++) {
	// Skip the peco classes
	if ($i == 13 or $i == 21 or $i == 4014 or $i == 4022 or $i == 4044 or $i == 4036) {
		continue;
	}
	if ($CONFIG_server_type == 0 && $i == 22) {
		break;
	}
	if ($i == 24) {
		$i = 4000;
		continue;
	}
	if ($i == $_GET['search_class']) { 
		echo "\n<option value=$i selected>" . determine_class($i) . "s";
	}
	else {
		echo "\n<option value=$i>" . determine_class($i) . "s";
	}
}
echo "
			</select>
			<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"{$lang['ladderdisplay']}\">
			<br>
		</form>
		</td>
	</tr>
	<tr class=myheader>
		<td>{$lang['ladderrank']}</td>
		<td>{$lang['laddername']}</td>
		<td>{$lang['ladderclass']}</td>
		<td>{$lang['ladderlevel']}</td>
		<td>{$lang['ladderjlevel']}</td>
		<td>{$lang['ladderzeny']}</td>
		$honor_column
	</tr>
		
	";
	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=6>{$lang['laddercannotdisplay']}</td>
	</tr>
		";
	}
	else {
		$current_rank = 0;
		while ($line = $result->FetchRow()) {
			$current_rank++;
			echo "
	<tr class=mycell>
		<td>$current_rank</td>
			";
			foreach ($line as $display_index => $col_value) {
				if ($display_index == 0 || $display_index == 1) {
					continue;
				}
				elseif ($display_index == 2 && $STORED_level > 2) {
					// Shows add ladder ignore for GMs and up
					$col_value = "<a href=\"ladder_ignore.php?add={$line[0]}\">$col_value</a>";
				}
				elseif ($display_index == 3) {
					switch($col_value) {
						case 13:
							$col_value = 7;
							break;
						case 21:
							$col_value = 14;
							break;
						case 4014:
							$col_value = 4008;
							break;
						case 4022:
							$col_value = 4015;
							break;
						case 4044:
							$col_value = 4037;
							break;
						case 4036:
							$col_value = 4030;
							break;
					}
					$col_value = determine_class($col_value);  // prints out thier class
				}
				echo "\n\t\t<td>$col_value</td>";
			}
			echo "
	</tr>
			";        // ends the row
		}
	}
	echo "
</table>
	";
}
?>