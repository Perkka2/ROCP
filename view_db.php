<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if (!$GET_page) {
	$page = 1;
}
else {
	$page = $GET_page;
}
$display_size = 30;
$start = ($page * $display_size) - $display_size;

if ($GET_view == "item") {
	$clientItemNameTable = ParseIdNum2ItemDisplayNameTable("./dbtranslation/idnum2itemdisplaynametable.txt");
	if ($CONFIG_server_type == 0) {
		$item_type = array("Armor", "Arrow", "Weapon", "Card", "Event", "Guest", "Heal", "Special");
	}
	else {
		$item_type = array("Heal", "", "Usable", "Misc", "Weapon", "Armor", "Card",
		"Egg", "Pet Equip", "", "Arrow");
	}
	$na_cols = array(3, 4, 5, 6, 7, 8, 10, 11, 12, 13);

	// Aegis and Athena use different orders for classes, for some strange reason
	if ($CONFIG_server_type == 0) {
		$equip_class = array("Novice",
		"Swordman", "Magician", "Archer", "Acolyte", "Merchant", "Thief",
		"Knight", "Wizard", "Hunter", "Priest", "Blacksmith", "Assassin", "Knight (Peco)",
		"Sage", "Bard", "Dancer", "Monk", "Alchemist", "Rogue", "Crusader", "Crusader (Peco)",
		"Wedding", "Super Novice");
		$skip_class = array(13, 21);
	}
	else {
		$equip_class = array("Novice",
		"Swordman", "Magician", "Archer", "Acolyte", "Merchant", "Thief",
		"Knight", "Priest", "Wizard", "Blacksmith", "Hunter", "Assassin", "Knight (Peco)",
		"Crusader", "Monk", "Sage", "Rogue", "Alchemist", "Bard", "Dancer", "Crusader (Peco)",
		"Wedding", "Super Novice");
		$skip_class = array(13, 21);
	}

	$equip_gender = array("", "Female Only", "Male Only", "Male & Female");
	$equip_position = array("N/A" => "N/A", 1 => "Lower Head", 2 => "One Hand", 4 => "Garment", 16 => "Body",
	32 => "Shield", 34 => "Both Hands", 64 => "Foot", 136 => "Accessory", 256 => "Upper Head",
	512 => "Middle Head", 513 => "Middle & Lower Head", 768 => "Upper & Middle Head",
	769 => "Upper, Middle, Lower Head", 32768 => "Arrow");
	if (!$GET_col && !IsSet($GET_class) && !$GET_value) {
		$condition = "WHERE 1 = 1";
	}
	elseif (IsSet($GET_class) && $GET_class != "") {
		$condition = sprintf(SEARCH_CLASS, $GET_class);
	}
	elseif ($GET_value && strlen($GET_value) < 30) {
		if ($GET_col == "letter") {
			$letter = chr($GET_value);
			if ($CONFIG_server_type == 0) {
				$condition = "WHERE SUBSTRING($CONFIG_cp_db_name.dbo.item_db.Name, 1, 1) = '$letter'";
			}
			else {
				$condition = "WHERE SUBSTRING($CONFIG_cp_db_name.item_db.name_english, 1, 1) = '$letter'";
			}
		}
		else {
			if ($CONFIG_server_type == 0) {
				$condition = "WHERE [Name] LIKE '%$GET_value%'";
			}
			else {
				$condition = "WHERE name_english LIKE '%$GET_value%'";
			}
		}
	}

	else {
		if ($GET_col == "type" && strlen($GET_val) < 5) {
			if ($CONFIG_server_type == 0) {
				$condition = "WHERE [$GET_col] = $GET_val";
			}
			else {
				$condition = "WHERE `$GET_col` = $GET_val";
			}
		}
		else {
			$condition = "WHERE 1 = 1";
		}
	}
	$query = sprintf(SHOW_FULL_ITEMS, $condition);
	$result = execute_query($query, "view_db.php", $display_size, $start);
	while ($line = $result->FetchRow()) {
		foreach ($na_cols as $value) {
			if (!$line[$value]) {
				$line[$value] = "N/A";
			}
		}
		if (!$line[9]) {
			$equip_string = "
			<td colspan=3>N/A</td>
			";
		}
		else {
			$equip_jobs = sprintf("%024b", $line[9]);
			$job_string = "";
			for ($i = 0; $i < 24; $i++) {
				$bit = substr($equip_jobs, 24 - $i, 1);
				$class_index = $i - 1;
				if ($bit && !in_array($class_index, $skip_class)) {
					$job_string .= "<a href=\"view_db.php?view=item&class=$class_index\">{$equip_class[$class_index]}</a><br>";
				}
				if ($i == 7) $job_string .= "</td><td>";
				if ($i == 14) $job_string .= "</td><td>";
			}
			$equip_string = "{$equip_gender[$line[10] + 1]}</br>
			<td>$job_string</td>
			";
		}
		if ($GET_col == "item" && $GET_value) {
			$line[1] = highlight_search_term($line[1], $GET_value);
		}
		if($clientItemNameTable[$line[0]]){
			$clientItemName = $clientItemNameTable[$line[0]];
			//$moreReadableItemName = ucwords(str_replace("_", " ", preg_replace('/[0-9]+/', '', strtolower($line[0]))));
			$clientItemName = str_replace("_", " ", $clientItemName);
		}
		else{
			$clientItemName = "Name Not Found";
		}
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=3>{$clientItemName} ({$line[1]})</td>
	</tr>
	<tr class=items>

		<td>
		<img src=\"./images/items/images/{$line[0]}.png\"/ alt=\"{$clientItemName}\">
			Type: <a href=\"view_db.php?view=item&col=type&val={$line[2]}\">{$item_type[$line[2]]}</a><br>
			Buy From NPC: {$line[3]}<br>
			Weight: {$line[4]}<br>
			Dropped By: <a href=\"view_db.php?view=monster&col=item&val={$line[0]}\">Check</a><br>
		</td>
		<td>
			Attack: {$line[5]}<br>
			Defence: {$line[6]}<br>
			Range: {$line[7]}<br>
			Slots: {$line[8]}<br>
			Weapon Level: {$line[12]}<br>
			Equip Level: {$line[13]}<br>
			Location: {$equip_position[$line[11]]}<br>
		</td>
		<td>
			<table>
			Equipped By:
			<tr class=mycell>
				$equip_string
			</tr>
			</table>
		</td>
	</tr>
</table>
		";
	}
	$query = sprintf(COUNT_FULL_ITEMS, $condition);
	$result = execute_query($query, "view_db.php");
	$max_pages = intval($result->RowCount() / 30) + 1;
	for ($i = 1; $i < $max_pages; $i++) {
		echo "<a href=\"view_db.php?view=item&page=$i&col=$GET_col&value=$GET_value&class=$GET_class\">$i</a>";
		if ($i % 50 == 0) {
			echo "<br>";
		}
		else {
			echo "-";
		}
	}
	echo "<a href=\"view_db.php?view=item&page=$i&col=$GET_col&value=$GET_value&class=$GET_class\">$i</a><br />";

	for ($i = 1; $i < 27; $i++) {
		$code = $i + 64;
		$letter = chr($code);
		echo "<a href=\"view_db.php?view=item&page=1&col=letter&value=$code\">$letter</a>";
		if ($i != 26) {
			echo "-";
		}
	}

	echo "
	<form action=\"view_db.php\" method=\"GET\">
		<input type=\"hidden\" name=\"view\" value=\"item\">
		<table border=0 align=\"center\">
			<tr class=mycell>
				<td colspan=20>
					Search:
					<select name=\"col\" class=\"myctl\" size=\"1\">
						<option value=\"item\">Item Name</option>
					</select>
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
				</td>
			</tr>
		</table>
	</form>
	";
}
elseif ($GET_view == "monster") {
	$clientMobNameTable = ParseMobDefNames("./dbtranslation/mobdef.sc");
	$mobIdTable = ParseMobId2DBNameTable("./dbtranslation/NPCIdentity.lua");
	$property_type = array("Neutral", "Water", "Earth", "Fire", "Wind", "Poison",
	"Holy", "Shadow", "Ghost", "Undead");
	$size = array("Small", "Medium", "Large");
	$race = array("None", "Undead", "Brute", "Plant", "Insect", "Fish", "Demon", "Demi-Human",
	"Angel", "Dragon");
	$item_drop = array(18, 21, 24, 27, 30, 33, 36, 39);
	$item_rate = array(0, 19, 22, 25, 28, 31, 34, 37, 40);

	if (!$GET_col && !$GET_value) {
		$condition = "WHERE 1 = 1";
	}
	elseif ($GET_col == "item") {
		if ($CONFIG_server_type == 0) {
			$condition = "WHERE $GET_val IN (ID1.ID, ID2.ID, ID3.ID, ID4.ID,
			ID5.ID, ID6.ID, ID7.ID, ID8.ID)";
		}
		else {
			$condition = "WHERE $GET_val IN (Drop1id, Drop2id, Drop3id, Drop4id,
			Drop5id, Drop6id, Drop7id, Drop8id)";
		}
	}
	elseif ($GET_column == "letter") {
		$letter = chr($GET_value);
		if ($CONFIG_server_type == 0) {
			$condition = "WHERE SUBSTRING(script.dbo.monparameter.Name, 1, 1) = '$letter'";
		}
		else {
			$condition = "WHERE SUBSTRING($CONFIG_cp_db_name.mob_db.Name, 1, 1) = '$letter'";
		}
	}
	elseif ($GET_value) {
		if (strlen($GET_value) < 30) {
			if ($GET_column == "monster") {
				$condition = sprintf(SEARCH_MONSTER, $GET_value);
			}
			elseif ($GET_column == "item") {
				$search_term = $GET_value;
				if ($CONFIG_server_type > 0) {
					// Creates a general search for athena
					$query = "SELECT ID FROM $CONFIG_cp_db_name.item_db
					WHERE name_english LIKE '%$GET_value%'
					";
					$result = execute_query($query, "view_db.php");
					while ($line = $result->FetchRow()) {
						$condition_string .= $line[0] . ", ";
					}
					$condition_string .= "100000";
					$GET_value = $condition_string;
				}
				if ($GET_value) {
					$condition = sprintf(SEARCH_ITEM, $GET_value, $GET_value,
					$GET_value, $GET_value, $GET_value, $GET_value, $GET_value,
					$GET_value);
				}
				else {
					$condition = "WHERE 1 = 0";
				}
			}
		}
	}
	else {
		$valid_cols = array("race", "scale", "Element", "property");
		if (in_array($GET_col, $valid_cols) && strlen($GET_val) < 5) {
			$condition = "WHERE $GET_col = $GET_val";
		}
		else {
			$condition = "WHERE 1 = 1";
		}
	}

	if ($CONFIG_server_type > 0) {
		$query = "SELECT * FROM $CONFIG_cp_db_name.item_db";
		$result = execute_query($query, "view_db.php");
		while ($line = $result->FetchRow()) {
			// Apparently MySQL doesn't like the complex JOINs that MSSQL uses, thus
			// an entire array has to be used to store all the item numbers.
			$item[$line[0]] = $line[1];
		}
	}
	$query = sprintf(SHOW_FULL_MOBS, $condition);
	$result = execute_query($query, "view_db.php", $display_size, $start);

	echo '<table class="contentTable mobList">'
;

	while ($line = $result->FetchRow()) {
		if ($CONFIG_server_type > 0 || $CONFIG_adjust_rate) {
			// Multiplies the rate if it's athena, or aegis is using stored procedure for rates
			$line[3] *= $CONFIG_exp_rate;
			$line[4] *= $CONFIG_jexp_rate;
		}
		$col_value = "";
		foreach ($item_drop as $value) {
			if (!$line[$value]) {
				$line[$value] = "None";
			}
		}
		foreach ($item_rate as $index => $value) {
			if ($index == 0) continue;

			if ($CONFIG_server_type > 0 || $CONFIG_adjust_rate) {
				// Multiplies the rate if it's athena, or aegis is using stored procedure for rates
				$line[$value] *= $CONFIG_drop_rate;
			}

			if ($line[$value] > 10000) $line[$value] = 10000;
			if (!$line[$value]) $line[$value] = 0;
			$percent = ($line[$value] / 10000) * 100;
			$rate[$index] = "({$line[$value]} / 10000 - $percent%)";
		}
		if ($CONFIG_server_type == 0) {
			$property_col = "property";
		}
		else {
			$property_col = "Element";
		}


		//$moreReadableMobName = ucwords(str_replace("_", " ", preg_replace('/[0-9]+/', '', strtolower($line[0]))));
		if($clientMobNameTable[$line[0]]){
			$clientMobName = $clientMobNameTable[$line[0]];
			$clientMobName = ucwords(strtolower($clientMobName));
			//$imageMobName = str_replace("_", " ", strtolower($clientMobName));
		}
		else{
			$clientMobName = "Name Not Found";
		}

		$property_string = $property_type[$line[9] % 10] . " " . floor($line[9] / 20);
		if ($GET_column == "monster" && $GET_value) {
			$line[0] = highlight_search_term($line[0], $GET_value);
		}

		echo "
	<tr class=contentRowHeader>
		<td colspan=6>{$clientMobName} ({$line[0]} #{$mobIdTable[$line[0]]})</td>
	</tr>
		<tr class=contentRowSubHeader>
			<td></td>
			<td>Level: {$line[1]}</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td rowspan=4>
			<img class=\"mobImage\" src=\"./images/mobs/{$mobIdTable[$line[0]]}.gif\"/\">
		</td>
			<td>HP: {$line[2]}</td>
			<td>Attack: {$line[6]} - {$line[5]}</td>
			<td>STR:{$line[12]}</td>
			<td>VIT:{$line[14]}</td>
			<td>Property: <a href=\"view_db.php?view=monster&col=$property_col&val={$line[9]}\">$property_string</a></td>
		</tr>
		<tr>
			<td>Def: {$line[7]}</td>
			<td>EXP: {$line[3]}</td>
			<td>AGI:{$line[13]}</td>
			<td>INT:{$line[15]}</td>
			<td>Size: {$size[$line[10]]}</td>
		</tr>
		<tr>
			<td>MDef: {$line[8]}</td>
			<td>JEXP: {$line[4]}</td>
			<td>DEX:{$line[16]}</td>
			<td>LUK:{$line[17]}</td>
			<td>Race: <a href=\"view_db.php?view=monster&col=race&val={$line[11]}\">{$race[$line[11]]}</a></td>
		</tr>
		";
		for ($i = 1; $i < 9; $i++) {
			if ($CONFIG_server_type == 0) {
				$item_name = $line[15 + ($i * 3)];
			}
			else {
				$item_name = $item[$line[15 + ($i * 3)]];
			}
			if ($GET_column == "item" && $GET_value) {
				$item_name = highlight_search_term($item_name, $search_term);
			}
			if (($GET_val == $line[17 + ($i * 3)] && $GET_val > 0)) {
				$highlight_start = "<font color=green>";
				$highlight_end = "</font>";
			}
			else {
				$highlight_start = "";
				$highlight_end = "";
			}
			if ($line[15 + ($i * 3)] == "None") {
				$col_value .= "<tr><td>None<br></td><td>(None ()0 / 10000 - 0%)</td></tr>";
			}
			else {
				$col_value .= "
				<tr><td><img src=\"./images/items/icons/{$line[17 + ($i * 3)]}.png\"/></td><td><a href=\"view_db.php?view=monster&col=item&val={$line[17 + ($i * 3)]}\">$highlight_start$item_name$highlight_end</a></td><td>$highlight_start{$rate[$i]}$highlight_end</td></tr>
				";
			}
		}
		echo "<td colspan=5 class=\"mobDropsTD\"><table class=\"mobDrops\"><tr><td colspan=4 class=contentRowHeader>Drops:</td></tr>$col_value</table>
		</td>
	</tr>
		";
	}
	echo "<tr><td colspan=6></p>";
	$query = sprintf(COUNT_FULL_MOBS, $condition);
	$result = execute_query($query, "view_db.php");
	$max_pages = intval($result->RowCount() / 30) + 1;
	for ($i = 1; $i < $max_pages; $i++) {
		echo "<a href=\"view_db.php?view=monster&page=$i&column=$GET_column&value=$GET_value\">$i</a>";
		if ($i % 50 == 0) {
			echo "<br>";
		}
		else {
			echo "-";
		}
	}
	echo "<a href=\"view_db.php?view=monster&page=$i&column=$GET_column&value=$GET_value\">$i</a><br />";

	for ($i = 1; $i < 27; $i++) {
		$code = $i + 64;
		$letter = chr($code);
		echo "<a href=\"view_db.php?view=monster&page=0&column=letter&value=$code\">$letter</a>";
		if ($i != 26) {
			echo "-";
		}
	}

	echo "</td></tr></table>";
	echo "
	<form action=\"view_db.php\" method=\"GET\">
		<input type=\"hidden\" name=\"view\" value=\"monster\">
		<table border=0 align=\"center\">
			<tr class=mycell>
				<td colspan=20>
					Search:
					<select name=\"column\" class=\"myctl\" size=\"1\">
						<option value=\"monster\">Monster Name</option>
						<option value=\"item\">Item Name</option>
					</select>
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
				</td>
			</tr>
		</table>
	</form>
	";
}
else {
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>View Databases</td>
	</tr>
	<tr class=myheader>
		<td>Choose an option below:</td>
	</tr>
	<tr class=mycell>
		<td>
		<a href=\"view_db.php?view=item\">Items</a><br>
		<a href=\"view_db.php?view=monster\">Monsters & Drops</a>
		</td>
	</tr>
</table>
	";
}
require 'footer.inc';
?>
