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

echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">{$lang['serverinfo']}</div>";

// The numbers of the server as tiles
$tiles = array(
	array(number_format((float)GetAccountCount()), "Accounts"),
	array(number_format((float)GetCharacterCount()), "Characters"),
	array(number_format((float)GetGuildCount()), "Guilds"),
	array(number_format((float)GetUserCount()), "Players online"),
	array(number_format((float)GetZenyCount()), "Zeny in the game"),
	array(html_escape("$CONFIG_exp_rate / $CONFIG_jexp_rate / $CONFIG_drop_rate"), "Rates: EXP / Job / Drop"),
	array(html_escape($server_type), "Server")
);
echo "<div class=\"infoTiles\">";
foreach ($tiles as $tile) {
	echo "<div class=\"infoTile\"><div class=\"infoNumber\">{$tile[0]}</div><div class=\"infoLabel\">{$tile[1]}</div></div>";
}
echo "</div>";

// Links, only the ones that are set
$sites = array(
	array($CONFIG_website, "Website"),
	array($CONFIG_forums_location, "Forums"),
	array($CONFIG_patch_location, "Patch"),
	array($CONFIG_irc_channel, "IRC"),
	array($CONFIG_discord_url, "Discord")
);
$shown = array();
foreach ($sites as $site) { // (not $link, that is the database connection)
	if (trim($site[0]) != "" && !in_array(strtolower(trim($site[0])), array("http://", "https://", "irc://"))) {
		$shown[] = "<a class=\"infoLink\" href=\"" . html_escape(trim($site[0])) . "\">{$site[1]}</a>";
	}
}
if (count($shown) > 0) {
	echo "<div class=\"infoLinks\">" . implode(" ", $shown) . "</div>";
}
echo "</div>";

// War of Emperium
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">{$lang['guildwars']}</div>
	<div class=\"newsItem\">" . woe_schedule(false) . "</div></div>";

// The classes: how many characters each has and the highest one. The mounted classes (Peco) count
// with their class.
$mounted = array(13 => 7, 21 => 14, 4014 => 4008, 4022 => 4015, 4044 => 4037, 4036 => 4030);
$class_count = array();
$result = execute_query(GET_CLASS_COUNT, "server_info.php");
while ($line = $result->FetchRow()) {
	$job = isset($mounted[$line[0]]) ? $mounted[$line[0]] : (int)$line[0];
	if ($CONFIG_server_type == 0 && $job == 22) {
		continue; // the wedding outfit is not a class
	}
	$class_count[$job] = (isset($class_count[$job]) ? $class_count[$job] : 0) + $line[1];
}
ksort($class_count);
$top = array();
if (defined('CLASS_TOP_CHARS')) {
	// one query for all classes
	$result = execute_query(CLASS_TOP_CHARS, "server_info.php");
	while ($line = $result->FetchRow()) {
		$job = isset($mounted[$line[0]]) ? $mounted[$line[0]] : (int)$line[0];
		if (!isset($top[$job]) || $line[2] > $top[$job][1]) {
			$top[$job] = array($line[1], $line[2]);
		}
	}
}
else {
	foreach ($class_count as $job => $count) {
		$mounted_job = array_search($job, $mounted);
		$query = query_format(CLASS_BREAKDOWN, $job, $mounted_job !== false ? $mounted_job : $job);
		$result = execute_query($query, "server_info.php", 1, 0);
		if ($line = $result->FetchRow()) {
			$top[$job] = array($line[0], $line[1]);
		}
	}
}
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Classes</div><div class=\"classCards\">";
foreach ($class_count as $job => $count) {
	$best = isset($top[$job])
		? "Highest: " . html_escape($top[$job][0]) . ", Lv. " . (int)$top[$job][1]
		: "Highest: none";
	echo "
		<div class=\"classCard\">
			" . (file_exists("images/classes/$job.png")
				? "<img class=\"classPicture\" src=\"images/classes/$job.png\" alt=\"\" loading=\"lazy\">"
				: "<span class=\"classPicture\"></span>") . "
			<div>
				<div class=\"charName\">" . html_escape(determine_class($job)) . "</div>
				<div>" . number_format((float)$count) . " " . ($count == 1 ? "character" : "characters") . "</div>
				<div class=\"charClass\">$best</div>
			</div>
		</div>";
}
if (count($class_count) == 0) {
	echo "<div class=\"newsItem\">No characters yet.</div>";
}
echo "</div></div>";

// Rules
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">";
printf($lang['rulesofserver'], html_escape($CONFIG_server_name));
echo "</div><div class=\"newsItem\">" . nl2br(file_get_contents("rules.txt")) . "</div></div>";

// The GMs and admins, by their characters
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">";
printf($lang['serveradmins'], html_escape($CONFIG_server_name));
echo "</div><div class=\"staffList\">";
$result = execute_query(SHOW_ADMIN, "server_info.php");
$staff = 0;
while ($line = $result->FetchRow()) {
	$role = ($line[1] == 4) ? "Admin" : "GM";
	echo "<span class=\"staffMember\"><b>" . html_escape($line[0]) . "</b> <span class=\"staffRole\">$role</span></span>";
	$staff++;
}
if ($staff == 0) {
	echo "<div class=\"newsItem\">None yet.</div>";
}
echo "</div></div>
</div>
";

require 'footer.inc';
?>
