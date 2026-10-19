<?php
// Accessed when the user goes to the home page
require 'memory.php';	// calls memory functions
require 'header.inc';	// brings in header
check_auth($_SERVER['PHP_SELF']); // checks for required access
echo "<table class=\"contentTable equipment\">";
$query = query_format(GET_ALL_ITEMS);
$result = execute_query_union($query, "equipment.php");
$itemTable = ParseAllItems($result);
$clientItemNameTable = cached_parse_table('itemdisplayname', "./dbtranslation/idnum2itemdisplaynametable.txt", 'ParseIdNum2ItemDisplayNameTable');
$itemdesctable = cached_parse_table('itemdesc', 'dbtranslation/idnum2itemdesctable.txt', 'ParseIdNum2ItemDescTable');
$garmentSpriteIdTable = cached_parse_table('garmentspriteid', "./dbtranslation/garmentspriteidtable.txt", 'ParseIdNum2ItemDisplayNameTable');
$mapNameTable = cached_parse_table('mapname', "./dbtranslation/mapnametable.txt", 'ParseMapNameTable');
?>

<?php
	echo "
	<tr class=\"contentRowHeader\">
		<td>Characters</td>
	</tr>
	";



	$query = query_format(CHECK_SEX_AID, $STORED_id);
	$result = execute_query($query, "equipment.php");
	if ($result->RowCount() != 0) {
		$line = $result->FetchRow();
		if($line[0] == 0){$sex = 'F';}
		else{$sex = 'M';}
	}

	$query = query_format(GET_CHARACTER_FROM_USER, $STORED_id);
	$result = execute_query($query, "equipment.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "You do not have any characters");
	}
	if ($GET_action) {
		// only the hairstyles the class of that character has (the Doram have 6)
		$job = -1;
		while ($line = $result->FetchRow()) {
			if ($line[0] == $GET_char) {
				$job = $line[2];
			}
		}
		if ($job == -1 || (int)$GET_Style < 1 || (int)$GET_Style > hairstyle_count($job)) {
			redir("equipment.php", "That hairstyle does not exist for this character!");
		}
		$query = query_format(UPDATE_HAIR, $GET_Style, $GET_char, $STORED_id);
		$result = execute_query($query, "hairstyle.php");
		if ($link->Affected_Rows() > 0) {
			redir("equipment.php", "Hair Style Change successful!");
		}
		else {
			redir("equipment.php", "Something went wrong with the hairstyle change!");
		}
	}
	
	while ($line = $result->FetchRow()) {
		$line = array_map('html_escape', $line); // values from the database are shown as text
		$characterItems = GetCharacterItems($clientItemNameTable,$line[0],$itemTable);
		// What is worn is drawn on the character picture. The game saves it in charinfo when the character
		// logs out (accessory = lower, accessory2 = top, accessory3 = mid headgear), the worn items in the
		// inventory come first so the picture also matches when that was not saved.
		$worn = array('top' => (int)$line[10], 'mid' => (int)$line[11], 'bottom' => (int)$line[9], 'weapon' => (int)$line[12], 'shield' => (int)$line[13], 'robe' => 0);
		foreach ($characterItems as $item) {
			if ($item['equipped'] != 1) {
				continue;
			}
			$loca = (int)$itemTable[$item['id']]['loca'];
			$class = (int)$itemTable[$item['id']]['class'];
			if (in_array($item['type'], array('armor','armorMB','armorTB','armorTM','armorTMB'))) {
				// A headgear covering more than one place is drawn in the highest one
				if ($loca & 256) { $worn['top'] = $class; }
				else if ($loca & 512) { $worn['mid'] = $class; }
				else if ($loca & 1) { $worn['bottom'] = $class; }
				if ($loca & 32) { $worn['shield'] = $class; }
				if (($loca & 4) && isset($garmentSpriteIdTable[$item['id']])) { $worn['robe'] = (int)$garmentSpriteIdTable[$item['id']]; }
			}
			else if (in_array($item['type'], array('weapon','bothhand','bow','gun')) && ($loca & 2)) {
				$worn['weapon'] = $class;
			}
		}
		echo "<tr class=\"contentRowHeader charlist\">\n";
				echo "<td><b>$line[1]</b>$line[0]</td></tr>\n
								<tr class=\"contentRowHeader charlist\">\n
									<td><div>Base Lvl:<b> $line[3]</b></div>\n
										<div>Class: <b>" . determine_class($line[2])."</b></div>\n
										<div>Job Lvl:<b> $line[4]</b></div>\n
										<div>Zeny:<b> $line[5]</b></div>\n
									</td>\n
								</tr>\n
								<tr class=\"contentRowHeader charlist charmap\">\n
									<td><div class=\"charMap\">Map:<b> <a href=\"maplist.php?zone=0&amp;map=" . urlencode(str_replace('.gat', '', $line[15])) . "\">"
											. (isset($mapNameTable[$line[15]]) ? html_escape($mapNameTable[$line[15]]) : $line[15]) . "</a></b> ($line[16], $line[17])</div>\n
									</td>\n
								</tr>\n
							<tr valign=top><td><div class=\"charstats\">"
							/*<img src=\"./images/classes/{$line[2]}.png\"/ \">*/ ."
							<img id=\"$line[0]\" src=\"" . character_picture_url($sex, $line[2], $line[7], $line[8], $line[6], $worn) . "\"/ \">
							<br/>\n
					<table><form name=\"Hair\" method=\"GET\">
							<tr><td><select name=\"Style\" class=\"myctl\" size=\"1\" onchange=\"document.getElementById('$line[0]').src = this.options[this.selectedIndex].getAttribute('data-value');\">
					";
					$max = hairstyle_count($line[2]) + 1;
					for ($b = 1; $b < $max; $b++) {
						if ($b == $line[7]) {
							echo "<option value='{$b}' data-value=\"ROChargenPHP/index.php/generate/body={$sex}-{$line[2]}-0-{$line[6]}/hair=" . hair_view($sex, $b, $line[2]) . "-{$line[8]}-0/hats=0-0-0/equip=0-0-0/option=1/actdir=0-0-5\" selected>Style $b</option>";
						}
						else {
							echo "<option value='{$b}' data-value=\"ROChargenPHP/index.php/generate/body={$sex}-{$line[2]}-0-{$line[6]}/hair=" . hair_view($sex, $b, $line[2]) . "-{$line[8]}-0/hats=0-0-0/equip=0-0-0/option=1/actdir=0-0-5\">Style $b</option>";
						}
						echo "\n";
					}
					echo "</select></td>
							<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Change Hairstyle\">
							<input type=\"hidden\" class=\"myctl\" name=\"char\" value=\"$line[0]\">
							</td></tr>
							</table>
						</form>
									</div><table class=\"charitems\">\n";
				$wearable = filterItems($characterItems, array('armor','weapon','bothhand','bow','armorMB','armorTB','armorTM','armorTMB','gun','ThrowWeapon'));
				$consumables = filterItems($characterItems, array('heal'));
				$misc = filterItems($characterItems, array('event','card','arrow','ammo','cannonball','CashPointItem','special'));
				$quests = filterItems($characterItems, array('guest'));
				//array_sort_by_column($characterItems, 'table');
				echo "<tr class=\"charitems\"><td class=\"equipped\" rowspan=5>";
				for($i = 0; $i < count($characterItems); $i++) {
					if($characterItems[$i]['equipped'] == 1){
						$equippedClass = " equipped";
						echo item_tile($characterItems[$i], $itemdesctable);
					}
					//if($characterItems[$i]['equipped'] == 1){
						//foreach ($characterItems[$i] as $key => $val) {
						//		if ($val){echo " " . $key . " => " . $val;}
						//}
					//}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Item</td><td>";
				for($i = 0; $i < count($consumables); $i++) {
					if($consumables[$i]['equipped'] == 0){
						$equippedClass = "";
						echo item_tile($consumables[$i], $itemdesctable);
					}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Gear</td><td>";
				for($i = 0; $i < count($wearable); $i++) {
					if($wearable[$i]['equipped'] == 0){
						$equippedClass = "";
						echo item_tile($wearable[$i], $itemdesctable);
					}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Misc</td><td>";
					for($i = 0; $i < count($misc); $i++) {
						if($misc[$i]['equipped'] == 0){
							$equippedClass = "";
							echo item_tile($misc[$i], $itemdesctable);
						}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Quest</td><td>";
					for($i = 0; $i < count($quests); $i++) {
						if($quests[$i]['equipped'] == 0){
							$equippedClass = "";
							echo item_tile($quests[$i], $itemdesctable);
						}
				}
				echo "</table>\n";

		echo "</tr>\n";
	}



	echo "</table>\n";
echo "</table>";
require 'footer.inc';   // displays the header
?>
