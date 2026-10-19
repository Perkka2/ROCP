<?php
// Accessed when the user goes to the home page
require 'memory.php';	// calls memory functions
require 'header.inc';	// brings in header
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($STORED_login == 'CP') {
	redir("privileges.php", "Setting Up Administrator Account");
}

$welcome = sprintf($lang['welcome'], $CONFIG_panel_name, $CONFIG_server_name);
echo "<div class=\"homePage\">
	<div class=\"homeWelcome\">" . html_escape($welcome) . "</div>
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
	echo "<div class=\"homeWelcome\">{$bold_tag}$last_string{$bold_close}";
	if ($STORED_level > 2) {
		// Gives GMs/Admins a chance to backup the server
		echo " &middot; <a href=\"backup_server.php?action=backup\">{$lang['backupnow']}</a>";
	}
	echo "</div>\n";
}

// The server at a glance
$online = (int)GetUserCount();
echo "
	<div class=\"homeGlance\">
		<div class=\"glanceItem\"><b>$online</b> " . ($online == 1 ? "player" : "players") . " online</div>
		<div class=\"glanceItem\">" . woe_schedule() . "</div>
	</div>
";

// The newest announcements; GMs and admins also see theirs
show_announcements(USER_ANNOUNCE, $lang['userheader'], 1);
if ($STORED_level > 1) {
	show_announcements(GM_ANNOUNCE, $lang['gmheader'], 2);
}
if ($STORED_level > 3) {
	show_announcements(ADMIN_ANNOUNCE, $lang['adminheader'], 3);
}

// The characters of this account, as cards with their picture
$mapName = cached_parse_table('mapname', "./dbtranslation/mapnametable.txt", 'ParseMapNameTable');
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">{$lang['yourchars']}</div><div class=\"charCards\">";
$characters = array();
if (defined('GET_CHARACTER_FROM_USER')) {
	// Aegis: everything the picture needs (what is worn is saved in charinfo when the character logs out:
	// accessory = lower, accessory2 = top, accessory3 = mid headgear)
	$result = execute_query(query_format(CHECK_SEX_AID, $STORED_id), "index.php");
	$line = $result->FetchRow();
	$sex = ($line && $line[0] == 0) ? "F" : "M";
	$result = execute_query(query_format(GET_CHARACTER_FROM_USER, $STORED_id), "index.php");
	while ($line = $result->FetchRow()) {
		$characters[] = array(
			'name' => $line[1], 'job' => $line[2], 'clevel' => $line[3], 'joblevel' => $line[4], 'money' => $line[5],
			'map' => $line[15], 'x' => $line[16], 'y' => $line[17],
			'picture' => character_picture_url($sex, $line[2], $line[7], $line[8], $line[6], array(
				'top' => $line[10], 'mid' => $line[11], 'bottom' => $line[9],
				'weapon' => $line[12], 'shield' => $line[13], 'robe' => 0))
		);
	}
}
else {
	$result = execute_query(query_format(HOME_CHARS, $STORED_id), "index.php");
	while ($line = $result->FetchRow()) {
		$characters[] = array('name' => $line[0], 'job' => $line[1], 'clevel' => $line[2], 'joblevel' => $line[3],
			'money' => $line[4], 'map' => $line[5], 'x' => '', 'y' => '', 'picture' => '');
	}
}
if (count($characters) == 0) {
	echo "<div class=\"newsItem\">You have no characters yet.</div>";
}
foreach ($characters as $c) {
	$map = isset($mapName[$c['map']]) ? $mapName[$c['map']] : str_replace(".gat", "", $c['map']);
	$position = ($c['x'] !== '') ? " (" . (int)$c['x'] . ", " . (int)$c['y'] . ")" : "";
	echo "
		<div class=\"charCard\">"
			. ($c['picture'] ? "<a href=\"equipment.php\"><img class=\"charPicture\" src=\"{$c['picture']}\" alt=\"\"></a>" : "") . "
			<div class=\"charInfo\">
				<div class=\"charName\">" . html_escape($c['name']) . "</div>
				<div class=\"charClass\">" . html_escape(determine_class($c['job'])) . "</div>
				<div>Base Lv. <b>" . (int)$c['clevel'] . "</b> &middot; Job Lv. <b>" . (int)$c['joblevel'] . "</b></div>
				<div><b>" . number_format((float)$c['money']) . "</b> zeny</div>
				<div><a href=\"maplist.php?zone=0&amp;map=" . urlencode(str_replace(".gat", "", $c['map'])) . "\">" . html_escape($map) . "</a>$position</div>
			</div>
		</div>";
}
echo "</div></div>
</div>
";

require 'footer.inc';   // displays the header
?>
