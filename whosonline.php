<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// Checks guild war times
$time_string = date("Hi");
$agit_dates = explode(",", $CONFIG_agit_days);
$agit_starts = explode(",",$CONFIG_agit_start);
$agit_ends = explode(",",$CONFIG_agit_end);

if ($STORED_level < 2 && $CONFIG_server_type > 0) {
	foreach ($agit_dates as $day) { //first check standard days
		if (strstr($day,"-")) { //if a range is defined add the days in between to the array
			$day = explode("-",$day); 
			for ($i = $day[0]; $i<$day[1]; $i++) { $agit_dates[(count($agit_dates)+1)] = $i; }
		}
	}
	foreach ($agit_dates as $index => $day) { //check ranges (stored in seperate array)
		if (strstr($day,"-")) { //if a range is defined add the days in between to the array
			$day = explode("-",$day);
			for ($i = $day[0]; $i<=$day[1]; $i++) { $agit_dates2[$i] = $index; }
		}
	}
	
	if (in_array(date("w"),$agit_dates) || array_key_exists(date("w"),$agit_dates2)) { //checks if it is a WoE day
		if (in_array(date("w"),$agit_dates)) { $agit_index = array_search(date("w"), $agit_dates); } //index of day -> corresponds to time index
		else { $agit_index = $agit_dates2[date("w")]; } //index of day -> corresponds to time index
		
		$agit_starttime = $agit_starts[$agit_index]; if (!$agit_starttime) { $agit_starttime = $agit_starts[count($agit_starts)-1]; }
		$agit_endtime = $agit_ends[$agit_index]; if (!$agit_endtime) { $agit_endtime = $agit_ends[count($agit_ends)-1]; }
		
		if (strstr($agit_starttime,"/") && strstr($agit_endtime,"/")) { //check the alternate times to see if WoE falls in either of them
			$starts = explode("/",$agit_starttime); $ends = explode("/",$agit_endtime); 
			foreach($starts as $index => $agit_starttime)
			if (($time_string > $agit_starttime) && ($time_string < $ends[$index])) { redir("index.php", $lang['onlineguildwar']); }
		}
		elseif (($time_string > $agit_starttime) && ($time_string < $agit_endtime)) { redir("index.php", $lang['onlineguildwar']); }
	}
}

//Who's Online
if (!$GET_map) {
	if ($CONFIG_server_type == 0) {
		$query = sprintf(SHOW_ONLINE, "", ONLINE_WITH_GM, "");
		$query2 = sprintf(SHOW_ONLINE, "", ONLINE_WITHOUT_GM, "");
	}
	else {
		if ($STORED_level > 2) {
			$query = sprintf(SHOW_ONLINE, SHOW_POSITION, ONLINE_WITH_GM, "");
			$query2 = sprintf(SHOW_ONLINE, SHOW_POSITION, ONLINE_WITHOUT_GM, "");
		}
		else {
			$query = sprintf(SHOW_ONLINE, "", ONLINE_WITH_GM, "");
			$query2 = sprintf(SHOW_ONLINE, "", ONLINE_WITHOUT_GM, "");
		}
	}
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=7>{$lang['onlineusers']}</td>
	</tr>
			";
	display_online($query, $query2);
}
else {
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=7>
	";
	printf($lang['onlineusersmap'], $GET_map);
	echo "
			
		</td>
	</tr>
		";
	if (strlen($GET_map) > 15) {
		redir("index.php", $lang['onlineinvalid']);
		add_user_entry("Possible SQL injection attempt in whosonline.php");
	}
	if ($STORED_level > 2) {
		$query = sprintf(SHOW_ONLINE, SHOW_POSITION, ONLINE_WITH_GM, sprintf(CONDITION_MAP, $GET_map));
		$query2 = sprintf(SHOW_ONLINE, SHOW_POSITION, ONLINE_WITHOUT_GM, sprintf(CONDITION_MAP, $GET_map));
	}
	else {
		$query = sprintf(SHOW_ONLINE, "", ONLINE_WITH_GM, sprintf(CONDITION_MAP, $GET_map));
		$query2 = sprintf(SHOW_ONLINE, "", ONLINE_WITHOUT_GM, sprintf(CONDITION_MAP, $GET_map));
	}
	display_online($query, $query2);
}
require 'footer.inc';

function display_online($input_query, $input_query2) {
	global $CONFIG_server_type, $STORED_level, $lang;
	$result[0] = execute_query($input_query, "whosonline.php");
	$result[1] = execute_query($input_query2, "whosonline.php");
	if ($result[0]->RowCount() == 0 && $result[1]->RowCount() == 0) {
		echo "
		<tr class=mytext>
			<td>None</td>
		</tr>\n
		";
	}
	else {
		if ($CONFIG_server_type == 0) {
			echo "
		<tr class=myheader>
			<td>AID</td>
			<td>Account Name</td>
			<td>Email</td>
			<td>Gender</td>
			<td>IP</td>
		</tr>
			";
		}
		else {
			echo "
		<tr class=myheader>
			<td>{$lang['onlinename']}</td>
			<td>{$lang['onlineclass']}</td>
			<td>{$lang['onlinebase']}</td>
			<td>{$lang['onlinejob']}</td>
			";
			if ($STORED_level > 2) {
				echo "
			<td>{$lang['onlinex']}</td>
			<td>{$lang['onliney']}</td>
				";
			}
			echo "
			<td>{$lang['onlinemap']}</td>
		</tr>
			";
		}
		for ($i = 0; $i < 2; $i++) {
			while ($line = $result[$i]->FetchRow()) {
				echo "
		<tr class=mycell>
				";
				foreach ($line as $display_index => $col_value) {
					if ($CONFIG_server_type == 0) {
						if ($display_index == 3) {
							$col_value = $col_value == 1? "Male" : "Female";
						}
						elseif ($display_index == 4) {
							$col_value = convert_ip($col_value);
						}
					}
					else {
						if ($display_index == 0) { continue; }
						if (($display_index == 1) && ($STORED_level > 2) ) {
							$col_value = "<a href=whosonline_ignore.php?add=$line[0]>{$line[1]}</a>";
						}
						elseif ($display_index == 2) {
							$col_value = determine_class($line[2]);
						}
						elseif ($display_index == 5 && $STORED_level <= 2) {
							$map_name = substr($line[5], 0, strlen($line[5]) - 4);
							$col_value = "<a href=whosonline.php?map=$map_name>{$line[5]}</a>";
						}
						elseif ($display_index == 7 && $STORED_level > 2) {
							$map_name = substr($line[7], 0, strlen($line[7]) - 4);
							$col_value = "<a href=whosonline.php?map=$map_name>{$line[7]}</a>";
						}
						
					}
					if ($i == 0) {
						echo "
						<td><b>$col_value</b></td>
						";
					}
					else {
						echo "<td>$col_value</td>";
					}
				}
				echo "
		</tr>
				";
			}
		}
		
		
	}
	echo "</table>";
}

function convert_ip($input_long) {
	$hex = dechex($input_long);
	$length = strlen($hex);
	$add = 8 - $length;
	$full = "";
	for ($i = 0; $i < $add; $i++) {
		$full .= "0";
	}
	$full .= $hex;
        for ($i = 0; $i < 4; $i++) {
		$set[$i] = substr($full, $i * 2, 2);
	}
	return hexdec($set[3]) . "." . hexdec($set[2]) . "." . hexdec($set[1]) . "." . hexdec($set[0]);
}
?>