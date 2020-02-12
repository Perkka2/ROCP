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

	$clientItemNameTable = ParseIdNum2ItemDisplayNameTable("./dbtranslation/idnum2itemdisplaynametable.txt");
	if ($CONFIG_server_type == 0) {
		$item_type = array("Armor", "Arrow", "Weapon", "Card", "Event", "Guest", "Heal", "Special", "ThrowWeapon");
	}
	else {
		$item_type = array("Heal", "", "Usable", "Misc", "Weapon", "Armor", "Card",
		"Egg", "Pet Equip", "", "Arrow");
	}
	$na_cols = array(3, 4, 5, 6, 7, 8, 10, 11, 12, 13);

			echo "
			<form action=\"view_items.php\" method=\"GET\">
				<input type=\"hidden\" name=\"view\" value=\"item\">
				<table border=0 align=\"center\">
					<tr class=mycell>
						<td colspan=20>
							Search:
							<select name=\"type\" class=\"myctl\" size=\"1\">
								<option value=\"all\">All Items</option>
								<option value=\"zero\">{$item_type['0']}</option>
								<option value=\"1\">{$item_type['1']}</option>
								<option value=\"2\">{$item_type['2']}</option>
								<option value=\"3\">{$item_type['3']}</option>
								<option value=\"6\">{$item_type['6']}</option>
								<option value=\"8\">{$item_type['8']}</option>
							</select>
							<input type=\"hidden\" name=\"col\" value=\"item\">
							<input type=\"text\" name=\"value\" class=\"myctl\">
							<input type=\"submit\" class=\"myctl\" value=\"Search\">
						</td>
					</tr>
				</table>
			</form>
			";
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
				$condition = "WHERE ([Name] LIKE '%$GET_value%' OR [ID] LIKE '$GET_value')";
			}
			else {
				$condition = "WHERE (name_english LIKE '%$GET_value%')";
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
	if(IsSet($GET_type) && $GET_type != 'all'){
		if($GET_type == 'zero'){$GET_type = '0';}
		if ($CONFIG_server_type == 0) {
			$condition .= " AND ([type] = $GET_type)";
		}
		else {
			$condition .= " AND `type` = $GET_type";
		}
	}


	echo "<br/><table class=\"pageing\"><tr>";
	$query = sprintf(COUNT_FULL_ITEMS, $condition);
	$result = execute_query($query, "view_items.php");
	$max_pages = intval($result->RowCount() / 30) + 1;
	for ($i = 1; $i < $max_pages; $i++) {
		echo "<td><a href=\"view_items.php?view=item&page=$i&col=$GET_col&value=$GET_value&class=$GET_class\">$i</a></td>";
		if ($i % 26 == 0) {
			echo "</tr><tr>";
		}
	}
	echo "<td><a href=\"view_items.php?view=item&page=$i&col=$GET_col&value=$GET_value&class=$GET_class\">$i</a></td></tr><tr>";

	for ($i = 1; $i < 27; $i++) {
		$code = $i + 64;
		$letter = chr($code);
		echo "<td><a href=\"view_items.php?view=item&page=1&col=letter&value=$code\">$letter</a></td>";
	}
	echo "</tr></table><br/>";

	$query = sprintf(SHOW_FULL_ITEMS, $condition);
	$result = execute_query($query, "view_items.php", $display_size, $start);
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
					$job_string .= "<a href=\"view_items.php?view=item&class=$class_index\">{$equip_class[$class_index]}</a><br>";
				}
				if ($i == 7) $job_string .= "</td><td>";
				if ($i == 14) $job_string .= "</td><td>";
			}
			$equip_string = "{$equip_gender[$line[10] + 1]}</br>
			<td>$job_string</td>
			";
		}
		if($clientItemNameTable[$line[0]]){
			$clientItemName = $clientItemNameTable[$line[0]];
			//$moreReadableItemName = ucwords(str_replace("_", " ", preg_replace('/[0-9]+/', '', strtolower($line[0]))));
			$clientItemName = str_replace("_", " ", $clientItemName);
		}
		else{
			$clientItemName = "Name Not Found";
		}
		$itemTitle = "{$clientItemName} ({$line[1]} #{$line[0]})";
		if ($GET_col == "item" && $GET_value) {
			$itemTitle = highlight_search_term($itemTitle, $GET_value);
		}

		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=3>{$itemTitle}</td>
	</tr>
	<tr class=items>

		<td>
		<img src=\"./images/items/images/{$line[0]}.png\"  onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\" />
			Type: <a href=\"view_items.php?view=item&col=type&val={$line[2]}\">{$item_type[$line[2]]}</a><br>
			Buy From NPC: {$line[3]}<br>
			Weight: {$line[4]}<br>
			Dropped By: <a href=\"view_items.php?view=monster&col=item&val={$line[0]}\">Check</a><br>
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
require 'footer.inc';
?>
