<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$categories = array('Towns', 'Fields', 'Dungeons', 'Indoors', 'Castles', 'PvP & battle', 'Jobs & quests', 'Events', 'Other');
$search = isset($GET_q) ? trim((string)$GET_q) : '';
// map=<file> shows exactly that map (used by the links on the inventory page)
$exact = isset($GET_map) ? strtolower(str_replace('.gat', '', trim((string)$GET_map))) : '';
if ($exact != '') {
	$search = $exact;
}
$category = (isset($GET_type) && in_array($GET_type, $categories)) ? $GET_type : '';
// A search looks through every zone server, otherwise the first one is shown
$zone = isset($GET_zone) ? (int)$GET_zone : ($search != '' ? 0 : 1);

$maps = array();
$zoneCount = array();
if ($CONFIG_server_type == 0) {
	$mapName = cached_parse_table('mapname', "./dbtranslation/mapnametable.txt", 'ParseMapNameTable');
	$query = query_format(GET_ALL_MAPS);
	$result = execute_query($query, "maplist.php");
	$zsids = array();
	while ($line = $result->FetchRow()) {
		// Zone servers are numbered in ZSID order, the same order as their ports in the header status
		if (!isset($zsids[$line[0]])) {
			$zsids[$line[0]] = count($zsids) + 1;
			$zoneCount[$zsids[$line[0]]] = 0;
		}
		$number = $zsids[$line[0]];
		$zoneCount[$number]++;
		$file = str_replace(".gat", "", $line[1]);
		$name = isset($mapName[$line[1]]) ? $mapName[$line[1]] : '';
		$maps[] = array(
			'zone' => $number,
			'file' => $file,
			'gat' => $line[1],
			'name' => $name,
			'category' => map_category($line[1], $line[3]),
			'image' => file_exists("images/maps/$file.png")
		);
	}
}

// Zone server status from the header (only known when Check Server is on)
function zone_status($number) {
	global $CONFIG_check_server, $CONFIG_maintenance, $map;
	if (!$CONFIG_check_server) {
		return "";
	}
	if ($CONFIG_maintenance) {
		return " <font color=\"#ff9900\">Maint</font>";
	}
	if (!empty($map) && ($map & (1 << ($number - 1)))) {
		return " <font color=\"#00ff00\">Online</font>";
	}
	return " <font color=\"#ff0000\">Offline</font>";
}

$searchHtml = html_escape($search);
echo "<table class=\"contentTable maplist\">
	<tr class=\"contentRowHeader maplist\"><td colspan=\"2\">
		<form action=\"maplist.php\" method=\"GET\" class=\"mapFilter\">
			<input type=\"hidden\" name=\"zone\" value=\"$zone\">
			<input type=\"text\" name=\"q\" value=\"$searchHtml\" class=\"myctl\" placeholder=\"Search maps\">
			<select name=\"type\" class=\"myctl\" size=\"1\">
				<option value=\"\">All types</option>";
foreach ($categories as $name) {
	$selected = ($name == $category) ? " selected" : "";
	echo "<option value=\"" . html_escape($name) . "\"$selected>" . html_escape($name) . "</option>";
}
echo "		</select>
			<input type=\"submit\" class=\"myctl\" value=\"Search\">
		</form>
	</td></tr>
	<tr><td colspan=\"2\" class=\"mapZones\">";
$link = "&amp;q=" . urlencode($search) . "&amp;type=" . urlencode($category);
$selected = ($zone == 0) ? " class=\"selected\"" : "";
echo "<a href=\"maplist.php?zone=0$link\" data-zone=\"0\"$selected>All zone servers</a> (" . count($maps) . ")";
foreach ($zoneCount as $number => $count) {
	$selected = ($zone == $number) ? " class=\"selected\"" : "";
	echo " &nbsp; <span class=\"mapZone\"><a href=\"maplist.php?zone=$number$link\" data-zone=\"$number\"$selected>Zone Server $number</a>" . zone_status($number) . " ($count)</span>";
}
echo "</td></tr>
	<tr class=\"contentRowHeader maplist\"><td>Map</td><td>Map Name</td></tr>";

$shown = 0;
foreach ($maps as $m) {
	$text = strtolower($m['name'] . ' ' . $m['file']);
	$visible = ($zone == 0 || $zone == $m['zone'])
		&& ($category == '' || $category == $m['category'])
		&& ($search == '' || strpos($text, strtolower($search)) !== false)
		&& ($exact == '' || $exact == strtolower($m['file']));
	if ($visible) {
		$shown++;
	}
	$file = html_escape($m['file']);
	$picture = $m['image']
		? "<img src=\"images/maps/$file.png\" loading=\"lazy\" alt=\"\">"
		: "<div class=\"noMapImage\">$file</div>";
	echo "<tr class=\"mapRow\" data-zone=\"{$m['zone']}\" data-type=\"" . html_escape($m['category']) . "\" data-search=\"" . html_escape($text) . "\""
		. ($visible ? "" : " style=\"display:none\"") . ">
		<td>$picture</td>
		<td><b>" . ($m['name'] != '' ? html_escape($m['name']) : $file) . "</b><br>
			" . html_escape($m['gat']) . " &middot; " . html_escape($m['category']) . " &middot; Zone Server {$m['zone']}</td></tr>";
}
echo "<tr class=\"noMaps\"" . ($shown ? " style=\"display:none\"" : "") . "><td colspan=\"2\">No maps found</td></tr>";
echo "</table>";
?>
<script type="text/javascript">
// Filters the list while typing, without reloading the page
(function () {
	// Old browsers (IE 7 and older) have no querySelector, the Search button still works there
	if (!document.querySelector || !document.querySelectorAll) return;
	var form = document.querySelector('.mapFilter');
	if (!form) return;
	var rows = document.querySelectorAll('tr.mapRow');
	var zones = document.querySelectorAll('.mapZones a');
	var none = document.querySelector('tr.noMaps');
	var zone = form.elements['zone'];
	function filter() {
		var q = form.elements['q'].value.toLowerCase().replace(/^\s+|\s+$/g, '');
		var type = form.elements['type'].value;
		var shown = 0;
		for (var i = 0; i < rows.length; i++) {
			var r = rows[i];
			var show = (zone.value == '0' || r.getAttribute('data-zone') == zone.value)
				&& (type == '' || r.getAttribute('data-type') == type)
				&& (q == '' || r.getAttribute('data-search').indexOf(q) != -1);
			r.style.display = show ? '' : 'none';
			if (show) shown++;
		}
		none.style.display = shown ? 'none' : '';
		for (var j = 0; j < zones.length; j++) {
			zones[j].className = zones[j].getAttribute('data-zone') == zone.value ? 'selected' : '';
		}
	}
	function setZone(value) {
		zone.value = value;
		filter();
	}
	form.elements['q'].oninput = function () {
		// searching looks through every zone server
		if (this.value != '' && zone.value != '0') zone.value = '0';
		filter();
	};
	form.elements['type'].onchange = filter;
	form.onsubmit = function () {
		// browsers without oninput (IE 8) search when the button is pressed
		if (form.elements['q'].value != '') zone.value = '0';
		filter();
		return false;
	};
	for (var k = 0; k < zones.length; k++) {
		zones[k].onclick = function () { setZone(this.getAttribute('data-zone')); return false; };
	}
})();
</script>
<?php
require 'footer.inc';
?>
