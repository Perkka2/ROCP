<?php
// Accessed when the user goes to the home page
require 'memory.php';	// calls memory functions
require 'header.inc';	// brings in header
check_auth($_SERVER['PHP_SELF']); // checks for required access
echo "<table class=\"contentTable equipment\">";
$query = sprintf(GET_ALL_ITEMS);
$result = execute_query_union($query, "equipment.php");
$itemTable = ParseAllItems($result);
$clientItemNameTable = ParseIdNum2ItemDisplayNameTable("./dbtranslation/idnum2itemdisplaynametable.txt");
$itemdesctable = ParseIdNum2ItemDescTable('dbtranslation/idnum2itemdesctable.txt');
$headgearId = array(
	"F" => array( 2,2,4,7,1,5,3,6,12,10,9,11,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27),
	"M" => array( 2,2,1,7,5,4,3,6,8,9,10,12,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27)
);
?>

<?php
	echo "
	<tr class=\"contentRowHeader\">
		<td>Characters</td>
	</tr>
	";



	$query = sprintf(CHECK_SEX_AID, $STORED_id);
	$result = execute_query($query, "equipment.php");
	if ($result->RowCount() != 0) {
		$line = $result->FetchRow();
		if($line[0] == 0){$sex = 'F';}
		else{$sex = 'M';}
	}

	$query = sprintf(GET_CHARACTER_FROM_USER, $STORED_id);
	$result = execute_query($query, "equipment.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "You do not have any characters");
	}
	if ($GET_action) {
		$query = sprintf(UPDATE_HAIR, $GET_Style, $GET_char, $STORED_id);
		$result = execute_query($query, "hairstyle.php");
		if ($link->Affected_Rows() > 0) {
			redir("equipment.php", "Hair Style Change successful!");
		}
		else {
			redir("equipment.php", "Something went wrong with the hairstyle change!");
		}
	}
	
	while ($line = $result->FetchRow()) {
		echo "<tr class=\"contentRowHeader charlist\">\n";
				echo "<td><b>$line[1]</b>$line[0]</td></tr>\n
								<tr class=\"contentRowHeader charlist\">\n
									<td><div>Base Lvl:<b> $line[3]</b></div>\n
										<div>Class: <b>" . determine_class($line[2])."</b></div>\n
										<div>Job Lvl:<b> $line[4]</b></div>\n
										<div>Zeny:<b> $line[5]</b></div>\n
									</td>\n
								</tr>\n
							<tr valign=top><td><div class=\"charstats\">"
							/*<img src=\"./images/classes/{$line[2]}.png\"/ \">*/ ."
							<img id=\"$line[0]\" src=\"/ROChargenPHP/index.php/generate/body={$sex}-{$line[2]}-{$line[6]}-0/hair={$headgearId[$sex][$line[7]]}-{$line[8]}-0/hats={$line[9]}-{$line[10]}-{$line[11]}/equip={$line[12]}-{$line[13]}-{$line[14]}/option=1/actdir=0-0-5\"/ \">
							<br/>\n
					<table><form name=\"Hair\" method=\"GET\">
							<tr><td><select name=\"Style\" class=\"myctl\" size=\"1\" onchange=\"document.getElementById('$line[0]').src = this.options[this.selectedIndex].getAttribute('data-value');\">
					";
					$max = 28;
					for ($b = 1; $b < $max; $b++) {
						if ($b == $line[7]) {
							echo "<option value='{$b}' data-value=\"ROChargenPHP/index.php/generate/body={$sex}-{$line[2]}-{$line[6]}-0/hair={$headgearId[$sex][$b]}-{$line[8]}-0/hats=0-0-0/equip=0-0-0/option=1/actdir=0-0-5\" selected>Style $b</option>";
						}
						else {
							echo "<option value='{$b}' data-value=\"ROChargenPHP/index.php/generate/body={$sex}-{$line[2]}-{$line[6]}-0/hair={$headgearId[$sex][$b]}-{$line[8]}-0/hats=0-0-0/equip=0-0-0/option=1/actdir=0-0-5\">Style $b</option>";
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
				$characterItems = GetCharacterItems($clientItemNameTable,$line[0],$itemTable);
				$wearable = filterItems($characterItems, array('armor','weapon','bothhand','bow','armorMB','armorTB','armorTM','armorTMB','gun','ThrowWeapon'));
				$consumables = filterItems($characterItems, array('heal'));
				$misc = filterItems($characterItems, array('event','card','arrow','cannonball','CashPointItem','special'));
				$quests = filterItems($characterItems, array('guest'));
				//array_sort_by_column($characterItems, 'table');
				echo "<tr class=\"charitems\"><td class=\"equipped\" rowspan=5>";
				for($i = 0; $i < count($characterItems); $i++) {
					if($characterItems[$i]['equipped'] == 1){
						$equippedClass = " equipped";
						echo "<div class=\"equipmentList$equippedClass\"><span class=\"tooltipcontainer\">
										<div class=\"tooltiptext$equippedClass\">
											<div class=\"tooltipleft\">".($characterItems[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/images/". $characterItems[$i]['id'] .".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>") ."</div>
											<div class=\"tooltipright\"><b>" . $characterItems[$i]['name'] . "</b> (". $characterItems[$i]['db_name'] . ")<br/>" . GetTooltipText($characterItems[$i]) . htmldescription($itemdesctable[$characterItems[$i]['id']]) . "</div></div></span>
										<div class=\"amountInfo\">".$characterItems[$i]['amount']."</div>".($characterItems[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/icons/". $characterItems[$i]['id'] .".png\"/ \">") ."
									</div>\n";
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
						echo "<div class=\"equipmentList$equippedClass\"><span class=\"tooltipcontainer\">
										<div class=\"tooltiptext$equippedClass\">
											<div class=\"tooltipleft\">".($consumables[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/images/". $consumables[$i]['id'] .".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>") ."</div>
											<div class=\"tooltipright\"><b>" . $consumables[$i]['name'] . "</b> (". $consumables[$i]['db_name'] . ")<br/>" . GetTooltipText($consumables[$i]) . htmldescription($itemdesctable[$consumables[$i]['id']]) . "</div></div></span>
										<div class=\"amountInfo\">".$consumables[$i]['amount']."</div>".($consumables[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/icons/". $consumables[$i]['id'] .".png\"/ \">") ."
									</div>\n";
					}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Gear</td><td>";
				for($i = 0; $i < count($wearable); $i++) {
					if($wearable[$i]['equipped'] == 0){
						$equippedClass = "";
						echo "<div class=\"equipmentList$equippedClass\"><span class=\"tooltipcontainer\">
										<div class=\"tooltiptext$equippedClass\">
											<div class=\"tooltipleft\">".($wearable[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/images/". $wearable[$i]['id'] .".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>") ."</div>
											<div class=\"tooltipright\"><b>" . $wearable[$i]['name'] . "</b> (". $wearable[$i]['db_name'] . ")<br/>" . GetTooltipText($wearable[$i]) . htmldescription($itemdesctable[$wearable[$i]['id']]) . "</div></div></span>
										<div class=\"amountInfo\">".$wearable[$i]['amount']."</div>".($wearable[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/icons/". $wearable[$i]['id'] .".png\"/ \">") ."
									</div>\n";
					}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Misc</td><td>";
					for($i = 0; $i < count($misc); $i++) {
						if($misc[$i]['equipped'] == 0){
							$equippedClass = "";
							echo "<div class=\"equipmentList$equippedClass\"><span class=\"tooltipcontainer\">
											<div class=\"tooltiptext$equippedClass\">
												<div class=\"tooltipleft\">".($misc[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/images/". $misc[$i]['id'] .".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>") ."</div>
												<div class=\"tooltipright\"><b>" . $misc[$i]['name'] . "</b> (". $misc[$i]['db_name'] . ")<br/>" . GetTooltipText($misc[$i]) . htmldescription($itemdesctable[$misc[$i]['id']]) . "</div></div></span>
											<div class=\"amountInfo\">".$misc[$i]['amount']."</div>".($misc[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/icons/". $misc[$i]['id'] .".png\"/ \">") ."
										</div>\n";
						}
				}
				echo "</td></tr><tr class=\"charitems\"><td>Quest</td><td>";
					for($i = 0; $i < count($quests); $i++) {
						if($quests[$i]['equipped'] == 0){
							$equippedClass = "";
							echo "<div class=\"equipmentList$equippedClass\"><span class=\"tooltipcontainer\">
											<div class=\"tooltiptext$equippedClass\">
												<div class=\"tooltipleft\">".($quests[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/images/". $quests[$i]['id'] .".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/>") ."</div>
												<div class=\"tooltipright\"><b>" . $quests[$i]['name'] . "</b> (". $quests[$i]['db_name'] . ")<br/>" . GetTooltipText($quests[$i]) . htmldescription($itemdesctable[$quests[$i]['id']]) . "</div></div></span>
											<div class=\"amountInfo\">".$quests[$i]['amount']."</div>".($quests[$i]['type'] == 'guest' ? '' : "<img class=\"equipmentImage\" src=\"./images/items/icons/". $quests[$i]['id'] .".png\"/ \">") ."
										</div>\n";
						}
				}
				echo "</table>\n";

		echo "</tr>\n";
	}



	echo "</table>\n";
echo "</table>";
require 'footer.inc';   // displays the header
?>
