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

	echo "
	<form action=\"view_mobs.php\" method=\"GET\">
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
	echo "<tr><td colspan=6></p>";
	$clientMobNameTable = ParseMobDefNames("./dbtranslation/mobdef.sc");
	//$mobIdTable = ParseNPCIdentityTable("./dbtranslation/NPCIdentity.lua");
	$mobIdTable = ParseMobNameDefTable("./dbtranslation/mobname.def");
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
					$result = execute_query($query, "view_mobs.php");
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
		$result = execute_query($query, "view_mobs.php");
		while ($line = $result->FetchRow()) {
			// Apparently MySQL doesn't like the complex JOINs that MSSQL uses, thus
			// an entire array has to be used to store all the item numbers.
			$item[$line[0]] = $line[1];
		}
	}
	echo "<table class=\"pageing\"><tr>";
	$query = sprintf(COUNT_FULL_MOBS, $condition);
	$result = execute_query($query, "view_mobs.php");
	$max_pages = intval($result->RowCount() / 30) + 1;
	for ($i = 1; $i < $max_pages; $i++) {
		echo "<td><a href=\"view_mobs.php?view=monster&page=$i&column=$GET_column&value=$GET_value\">$i</a></td>";
		if ($i % 26 == 0) {
			echo "</tr><tr>";
		}
	}
	echo "<td><a href=\"view_mobs.php?view=monster&page=$i&column=$GET_column&value=$GET_value\">$i</a></td></tr><tr>";

	for ($i = 1; $i < 27; $i++) {
		$code = $i + 64;
		$letter = chr($code);
		echo "<td><a href=\"view_mobs.php?view=monster&page=0&column=letter&value=$code\">$letter</a></td>";
	}
	echo "</tr></table><br/>";

	$query = sprintf(SHOW_FULL_MOBS, $condition);
	$result = execute_query($query, "view_mobs.php", $display_size, $start);

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
			//$rate[$index] = "({$line[$value]} / 10000 - $percent%)";
			$rate[$index] = "($percent%)";
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
		$mobID = $mobIdTable[$line[0]];
		$property_string = $property_type[$line[9] % 10] . " " . floor($line[9] / 20);
		if ($GET_column == "monster" && $GET_value) {
			$line[0] = highlight_search_term($line[0], $GET_value);
		}

		echo "
	<tr class=contentRowHeader>
		<td colspan=6>{$clientMobName} ({$line[0]} #{$mobID})</td>
	</tr>
		<tr class=contentRowSubHeader>
			<td></td>
			<td>Level: {$line[1]}</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td rowspan=13>
			<img class=\"mobImage\" src=\"/ROChargenPHP/index.php/monster/{$mobID}\"/\">
			<img class=\"mobImage\" src=\"./images/mobs/{$mobID}.gif\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>
		</td>
			<td>HP: {$line[2]}</td>
			<td>Attack: {$line[6]} - {$line[5]}</td>
			<td>STR:{$line[12]}</td>
			<td>VIT:{$line[14]}</td>
			<td>Property: <a href=\"view_mobs.php?view=monster&col=$property_col&val={$line[9]}\">$property_string</a></td>
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
			<td>Race: <a href=\"view_mobs.php?view=monster&col=race&val={$line[11]}\">{$race[$line[11]]}</a></td>
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
				$col_value .= "<tr><td>None(0%)</td></tr>";
			}
			else {
				$col_value .= "
			<tr><td colspan=5 class=\"mobDrops\"><img src=\"./images/items/icons/{$line[17 + ($i * 3)]}.png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/><a href=\"view_mobs.php?view=monster&col=item&val={$line[17 + ($i * 3)]}\">$highlight_start$item_name$highlight_end</a> $highlight_start{$rate[$i]}$highlight_end</td></tr>
				";
			}
		}
		echo "<tr><td colspan=5 class=\"contentRowHeader drops\">Drops:</td></tr>
				<td></td>$col_value
		</td>
	</tr>
		";
	}

	echo "</td></tr></table>";
require 'footer.inc';
?>
