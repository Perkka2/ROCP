<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
switch ($CONFIG_server_type) {
	case 0:
		$server_type = "Aegis";
		break;
	case 1: case 2:
		$server_type = "Athena";
		break;
	case 3:
		$server_type = "Freya";
		break;
}
// Get SQL version
$sql_info = $link->ServerInfo();
EchoHead(50);
echo "
	<tr class=mytitle>
		<td>{$lang['serverinfo']}</td>
	</tr>
		<tr class=mycell>
			<td>{$lang['server_type']} $server_type</td>
		</tr>
";
if ($CONFIG_website) {
	echo "
		<tr class=mycell>
			<td>{$lang['website']} <a href=\"$CONFIG_website\">$CONFIG_website</a></td>
		</tr>
	";
}
if ($CONFIG_forums_location) {
	echo "
		<tr class=mycell>
			<td>{$lang['forums']} <a href=\"$CONFIG_forums_location\">$CONFIG_forums_location</a></td>
		</tr>
	";
}
if ($CONFIG_patch_location) {
	echo "
		<tr class=mycell>
			<td>{$lang['patch']} <a href=\"$CONFIG_patch_location\">$CONFIG_patch_location</a></td>
		</tr>
	";
}
if ($CONFIG_irc_channel) {
	echo "
		<tr class=mycell>
			<td>{$lang['irc']} <a href=\"$CONFIG_irc_channel\">$CONFIG_irc_channel</a></td>
		</tr>
	";
}
echo "
	   	<tr class=mycell>
		   	<td>
				{$lang['totalacc']} " . GetAccountCount() . "
		   	</td>
	   	</tr>
	   	<tr class=mycell>
		   	<td>
		   		{$lang['totalchar']} " . GetCharacterCount() . "
		   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
		   		{$lang['totalguild']} " . GetGuildCount() . "
		   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
		   		{$lang['totalzeny']} " . GetZenyCount() . " zeny
		   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
		   		{$lang['serverrate']} $CONFIG_exp_rate/$CONFIG_jexp_rate/$CONFIG_drop_rate
		   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
";

	$agit_days = explode(",", $CONFIG_agit_days); //every agitday
	$days = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"); //every day name
	$agit_starts = explode(",",$CONFIG_agit_start);
	$agit_ends = explode(",",$CONFIG_agit_end);
	$agit_offset = $CONFIG_agit_offset;

	for ($day_off = 0; $agit_offset >= 2400; $day_off++) { $agit_offset -= 2400; }
	$hour_off = $agit_offset;

	if ((count($agit_starts) == 1) && (count($agit_ends) == 1)) {
		echo "{$lang['guildwars']}: " . gettime($CONFIG_agit_start,$CONFIG_agit_end,$hour_off);
		foreach ($agit_days as $index => $value) {
		if (strstr($value,"-")) { $day = explode("-",$value); $display_days[$value] =  "{$days[$day[0]]} - {$days[$day[1]]}"; }
		else { $display_days[$value] = $days[$value + $day_off]; } }
		$display_days = implode(", ", $display_days);
		echo " ($display_days)";
	}
	else {
		echo "{$lang['guildwars']}:<br>";
		foreach ($agit_days as $index => $value) {
			$agit_starttime = $agit_starts[$index]; if (!$agit_starttime) { $agit_starttime = $agit_starts[count($agit_starts)-1]; }
			$agit_endtime = $agit_ends[$index]; if (!$agit_endtime) { $agit_endtime = $agit_ends[count($agit_ends)-1]; }
			if (strstr($value,"-")) { $day = explode("-",$value); echo "{$days[$day[0]]} to {$days[$day[1]]} - " . gettime($agit_starttime,$agit_endtime,$hour_off);  echo "<br>"; }
			elseif ((!$agit_starts[$index+1]) && (!$agit_ends[$index+1]) && ($index<(count($agit_days)-1))) { echo "{$days[$value + $day_off]}, "; }
			else {
				echo $days[$value + $day_off] . " - " .  gettime($agit_starttime,$agit_endtime,$hour_off);
				if ($index != count($agit_days)) { echo "<br>"; }
			}
		}
	}
echo "   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
		   		PHP: Version " . phpversion() . "
		   	</td>
	   	</tr>
		<tr class=mycell>
		   	<td>
		   		SQL: {$sql_info['version']}
		   	</td>
	   	</tr>
	<tr class=mytitle>
		<td>
";
printf($lang['rulesofserver'], $CONFIG_server_name);
$server_rules = nl2br(file_get_contents("rules.txt"));
echo "
		</td>
	</tr>
	<tr class=mycell>
		<td>$server_rules</td>
	</tr>
</table>
";

$query = GET_CLASS_COUNT;
$result = execute_query($query, "server_info.php");

$class_max = $CONFIG_server_type == 0? 23 : 4046;

for ($i = 0; $i < $class_max; $i++) {
	if ($i == 24) {
		$i = 4000;
	}
	$total_class_count[$i] = 0;
}
while ($line = $result->FetchRow()) {

	// Adds the peco class to the original class count
	if ($line[0] == 13) {
		$line[0] = 7;
	}
	elseif ($line[0] == 21) {
		$line[0] = 14;
	}
	elseif ($line[0] == 4014) {
		$line[0] = 4008;
	}
	elseif ($line[0] == 4022) {
		$line[0] = 4015;
	}
	elseif ($line[0] == 4044) {
		$line[0] = 4037;
	}
	elseif ($line[0] == 4036) {
		$line[0] = 4030;
	}
	$total_class_count[$line[0]] += $line[1];

}

EchoHead(50);
echo "
	<tr class=mytitle>
		<td colspan=4>Breakdown of Each Class</td>
	</tr>
	<tr class=myheader>
		<td>Class</td>
		<td>Total</td>
		<td>Highest Level</td>
		<td>Level</td>
	</tr>
";
for ($i = 0; $i < $class_max; $i++) {
	switch($i) {
		case 7:
			$i2 = 13;
			break;
		case 14:
			$i2 = 21;
			break;
		case 4008:
			$i2 = 4014;
			break;
		case 4015:
			$i2 = 4022;
			break;
		case 4037:
			$i2 = 4044;
			break;
		case 4030:
			$i2 = 4036;
			break;
		default:
			$i2 = $i;
			break;
	}
	if ($i == 24) {
		$i = 4001;
	}
	if ($i == 13 or $i == 21 or $i == 4014 or $i == 4022 or $i == 4044 or $i == 4036) {
		continue;
	}
	if ($CONFIG_server_type == 0 && $i == 22) {
		continue;
	}
	$query = sprintf(CLASS_BREAKDOWN, $i, $i2);
	$result = execute_query($query, "server_info.php", 1, 0);
	$class = determine_class($i);
	if ($result->RowCount() > 0) {
		$line = $result->FetchRow();
		$char_name = $line[0];
		$level = $line[1];
		echo "
	<tr class=mycell>
		<td>$class</td>
		<td>{$total_class_count[$i]}</td>
		<td>$char_name</td>
		<td>$level</td>
	</tr>
		";
	}
	else {
		echo "
	<tr class=mycell>
		<td>$class</td>
		<td>0</td>
		<td>None</td>
		<td>None</td>
	</tr>
		";
	}
}
echo "</table>";

EchoHead(50);
echo "
	<tr class=mytitle>
		<td>
";
printf($lang['serveradmins'], $CONFIG_server_name);
echo "
		</td>
	</tr>
	<tr class=myheader>
		<td>{$lang['serverchars']}</td>
	</tr>
";

$query = SHOW_ADMIN;
$result = execute_query($query, "server_info.php");
while ($line = $result->FetchRow()) {
	if ($line[1] == 4) {
		$bold_start = "<b>";
		$bold_end = "</b>";
	}
	else {
		$bold_start = "";
		$bold_end = "";
	}
	echo "
	<tr class=mycell>
		<td>$bold_start{$line[0]}$bold_end</td>
	</tr>
	";
}

echo "
</table>
";

require 'footer.inc';
?>
