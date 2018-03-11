<?php

function display_aegis_item($item_id) {
	$item_name = ItemID_To_ItemName($item_id);
	$procedures = array("CartItemSearch", "CharItemSearch", "StorageItemSearch");
	$headers = array("Carts with", "Chars with", "Storages With");
	$links = array(
	"char_manage.php?option=search&char_id=",
	"char_manage.php?option=search&char_id=",
	"account_manage.php?option=itemsearch&account_id="
	);
	$hex = dechex($item_id);
	while (strlen($hex) < 4) {
		$hex = "0" . $hex;
	}
	$hex = "0x" . substr($hex, 2, 2) . substr($hex, 0, 2);
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td>Item Search for $item_name</td>
	</tr>
	<tr class=myheader>
		<td>Due to the nature of Binary Strings, this may not be 100% accurate.<br>
		However, this will flag ALL possible accounts with this item, but there may be a few
		false finds.
		</td>
	</tr>
	<tr class=mycell><td><font color=\"#ff0000\">Due to the new gzipped save format on aegis servers item search is no longer avaiable and will return empty results</font></td></tr>
	</table>
	";
	foreach ($procedures as $exec_index => $exec_proc) {
		$query = sprintf(SEARCH_ITEMS, $exec_proc, $hex);
		$result = execute_query($query, "item_functions.php");
		EchoHead(80);
		echo "
		<tr class=mytitle>
			<td>{$headers[$exec_index]} $item_name</td>
		</tr>
		";
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>";
			if ($line[2] > 0) {
				$bold_start = "<b>";
				$bold_end = "</b>";
			}
			else {
				$bold_start = "";
				$bold_end = "";
			}
			foreach ($line as $display_index => $col_value) {
				if ($display_index == 0 || $display_index == 2) {
					continue;
				}
				echo "<td><a href=\"{$links[$exec_index]}{$line[0]}\">$bold_start$col_value$bold_end</a></td>";
			}
			echo "</tr>";
		}
		echo "
		</table>
		";
	}
}

function display_item ($condition) {
	global $CONFIG_server_type, $CONFIG_cp_db_name;
	$cp_db = $CONFIG_cp_db_name;
	$header = array("Items On Server", "Items in Storage", "Items in Cart");// Header for table
	$table = array("inventory", "storage", "cart_inventory");		// Table to search
	$name_table = array("char", "login", "char");				// Table to get names
	$name_column = array("name", "userid", "name");				// Columns to get names
	$reference_table = array("char", "login", "char");			// Table to get IDs
	$reference_column = array("char_id", "account_id", "char_id");		// Columns to get IDs
	$link_string = array("Character", "Account", "Character");		// Search ____ to display in links
	$delete_type = array("delete_inv", "delete_stor", "delete_cart");	// Where to delete
	$table_header = array("Character Name", "Account Name", "Character Name"); // Header for the table
	for ($a = 0; $a < 3; $a++) {
		EchoHead(100);
		echo "
		<tr class=mytitle>
			<td colspan=8>{$header[$a]}</td>
		</tr>
		<tr class=myheader>
			<td>Delete</td>
			<td>{$table_header[$a]}</td>
			<td>Quantity</td>
			<td>Item</td>
			<td>Card 1</td>
			<td>Card 2</td>
			<td>Card 3</td>
			<td>Card 4</td>
		</tr>
		";

		// Searches for the item in the specified table (GMs/Admins)
		$search_query =
		array ("
		SELECT {$table[$a]}.id, {$name_table[$a]}.{$name_column[$a]}, {$table[$a]}.{$reference_column[$a]}, amount, nameid, name0.name_japanese, refine,
		name1.name_japanese, card0, name2.name_japanese, card1, name3.name_japanese, card2, name4.name_japanese, card3
		FROM `{$table[$a]}`, `{$reference_table[$a]}`
		LEFT JOIN $cp_db.privilege ON {$reference_table[$a]}.account_id = $cp_db.privilege.account_id
		LEFT JOIN $cp_db.item_db AS name0 ON {$table[$a]}.nameid = name0.ID
		LEFT JOIN $cp_db.item_db AS name1 ON {$table[$a]}.card0 = name1.ID
		LEFT JOIN $cp_db.item_db AS name2 ON {$table[$a]}.card1 = name2.ID
		LEFT JOIN $cp_db.item_db AS name3 ON {$table[$a]}.card2 = name3.ID
		LEFT JOIN $cp_db.item_db AS name4 ON {$table[$a]}.card3 = name4.ID
		WHERE $condition
		AND ($cp_db.privilege.account_id = {$reference_table[$a]}.account_id)
		AND {$reference_table[$a]}.{$reference_column[$a]} = {$table[$a]}.{$reference_column[$a]}
		",
		// Searches for the item in in the specified table (Everyone else)
		"
		SELECT {$table[$a]}.id, {$name_table[$a]}.{$name_column[$a]}, {$table[$a]}.{$reference_column[$a]}, amount, nameid, name0.name_japanese, refine,
		name1.name_japanese, card0, name2.name_japanese, card1, name3.name_japanese, card2, name4.name_japanese, card3
		FROM `{$table[$a]}`, `{$reference_table[$a]}`
		LEFT JOIN $cp_db.privilege ON {$reference_table[$a]}.account_id = $cp_db.privilege.account_id
		LEFT JOIN $cp_db.item_db AS name0 ON {$table[$a]}.nameid = name0.ID
		LEFT JOIN $cp_db.item_db AS name1 ON {$table[$a]}.card0 = name1.ID
		LEFT JOIN $cp_db.item_db AS name2 ON {$table[$a]}.card1 = name2.ID
		LEFT JOIN $cp_db.item_db AS name3 ON {$table[$a]}.card2 = name3.ID
		LEFT JOIN $cp_db.item_db AS name4 ON {$table[$a]}.card3 = name4.ID
		WHERE $condition
		AND $cp_db.privilege.account_id IS NULL
		AND {$reference_table[$a]}.{$reference_column[$a]} = {$table[$a]}.{$reference_column[$a]}
		"
		);

		for ($i = 0; $i < 2; $i++) {
			$result = execute_query($search_query[$i], "item_functions.php");
			while ($line = $result->FetchRow()) {
				if ($i == 0) {
					$bold_start = "<b>";
					$bold_end = "</b>";
				}
				else {
					$bold_start = "";
					$bold_end = "";
				}
				echo "<tr class=mycell>\n";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 0) {
						$col_value = "<a href=\"item_manage.php?{$delete_type[$a]}=$col_value\">Delete</a>";
					}
					elseif ($display_index == 1) {
						$col_value = "<a href=item_manage.php?s_action=Search+{$link_string[$a]}&{$reference_column[$a]}={$line[2]}>{$line[1]}</a>";
					}
					elseif ($display_index == 2) {
						continue;
					}
					elseif ($display_index == 3) {
					}
					elseif ($display_index == 4) {
						continue;
					}
					elseif ($display_index == 5) {
						$display = convert_equip($line[5], $line[6], $line[8], $line[10]);
						if ($display == "") {
							$display = "Unknown Item {$line[4]}";
						}
						$col_value = "<a href=item_manage.php?item={$line[4]}>$display</a>";
					}
					elseif ($display_index == 5) {
						continue;
					}
					else {
						continue;
					}
					echo "<td>$bold_start$col_value$bold_end</td>\n";
				}
				echo "<td>$bold_start<a href=item_manage.php?item={$line[8]}>{$line[7]}</a>$bold_end</td>";
				echo "<td>$bold_start<a href=item_manage.php?item={$line[10]}>{$line[9]}</a>$bold_end</td>";
				echo "<td>$bold_start<a href=item_manage.php?item={$line[12]}>{$line[11]}</a>$bold_end</td>";
				echo "<td>$bold_start<a href=item_manage.php?item={$line[14]}>{$line[13]}</a>$bold_end</td>";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	}
}

function display_source_id_items ($source_type, $name_id) {
	global $CONFIG_server_type, $CONFIG_cp_db_name;
	$cp_db = $CONFIG_cp_db_name;
	if ($CONFIG_server_type == 0) {
		if ($source_type == "AID") {
			$table[0] = "storeitem";
			$column[0] = "storedItem";
			$source_name = AccountID_To_UserID($name_id);
			$header[0] = "Account $source_name Storage";
			$stop = 1;
		}
		elseif ($source_type == "GID") {
			$table[0] = "item";
			$table[1] = "cartItem";
			$column[0] = "equipItem";
			$column[1] = "cartitem";
			$source_name = CharID_To_CharName($name_id);
			$header[0] = "$source_name's Inventory:";
			$header[1] = "$source_name's Cart Items:";
			$stop = 2;
		}
		for ($i = 0; $i < $stop; $i++) {
			$query = sprintf(SHOW_SOURCE_ITEMS, $column[$i], $table[$i], $source_type, $name_id);
			$result = execute_query($query, "item_functions.php");
			EchoHead(100);
			echo "
		<tr class=mytitle>
			<td colspan=8>{$header[$i]}</td>
		</tr>
		<tr class=myheader>
			<td>Quantity</td>
			<td>Item</td>
			<td>Card 1</td>
			<td>Card 2</td>
			<td>Card 3</td>
			<td>Card 4</td>
		</tr>
			";
			$line = $result->FetchRow();
			$itemBinaryString = $line[0];
			$itemBinaryString = hex2bin(substr(bin2hex($itemBinaryString), 4));
			$item_data = bin2hex(gzuncompress($itemBinaryString));
			//$item_data = substr(bin2hex($line[0]), 4);

			while (strlen($item_data) > 0) {
				$hasUuid = 0;
				echo "<tr class=mycell>";
				$item_code = hexdec(substr($item_data,2,2) . substr($item_data,0,2));
				$item_name = ItemID_To_ItemName($item_code);
				$full_code = "";
				if (IsEquip($item_code)) {
					echo "<td>1</td>";
					//$full_code .= substr($item_data,0,38); //oldfullcode
					$full_code .= substr($item_data,0,50);
					//$refined = hexdec(substr($item_data,20,2)); //old refinepos
					$refined = hexdec(substr($item_data,14,2));
					//$card_id[1] = hexdec(substr($item_data,24,2) . substr($item_data,22,2));
					//$card_id[2] = hexdec(substr($item_data,28,2) . substr($item_data,26,2));
					//$card_id[3] = hexdec(substr($item_data,32,2) . substr($item_data,30,2));
					//$card_id[4] = hexdec(substr($item_data,36,2) . substr($item_data,34,2));
					$card_id[0] = hexdec(substr($full_code,20,2) . substr($full_code,18,2));
					$card_id[1] = hexdec(substr($full_code,24,2) . substr($full_code,22,2));
					$card_id[2] = hexdec(substr($full_code,28,2) . substr($full_code,26,2));
					$card_id[3] = hexdec(substr($full_code,32,2) . substr($full_code,30,2));
					for ($i = 0; $i < 5; $i++) {
						$card[$i] = ItemID_To_ItemName($card_id[$i]);
					}
					
					
					//$item_data = substr($item_data,38); //olditemdata					
					$item_settings = hexdec(substr($item_data, 4,2)); //get item flags
					if($item_settings & (1 << 7-1)){$hasUuid = 1;}
					$item_data = substr($item_data, ($hasUuid) ? 50 : 34);

					if ($equipped > 0) {
						$color = "#AA0000";
					}
					if ($refined > 0) {
						$refined = "+" . $refined;
					}
					else {
						$refined = "";
					}

					echo "<td><a href=\"item_manage.php?item=$item_code\">$refined $item_name</a></td>";
					foreach ($card as $card_index => $col_value) {
						if ($col_value) {
							echo "<td><a href=\"item_manage.php?item={$card_id[$card_index]}\">$col_value</a></td>";
						}
						elseif ($card_index == 4) {
						}
						else {
							echo "<td></td>";
						}
					}
					//echo "<td>$full_code</td>";
				}
				elseif(IsArrow($item_code)) {
					$full_code .= substr($item_data,0,14);
					$quantity = hexdec(substr($item_data,8,2) . substr($item_data,6,2));
					echo "<td>$quantity</td>";
					echo "<td><a href=\"item_manage.php?item=$item_code\">$item_name</a>";

					//$item_data = substr($item_data,14); //olditemdata
					$item_data = substr($item_data,18);
					//echo "<td></td><td></td><td></td><td></td><td>$full_code</td>";
				}
				elseif(IsQuest($item_code)) {
					$full_code .= substr($item_data,0,8);
					$quantity = hexdec(substr($item_data,6,2) . substr($item_data,4,2));
					echo "<td>$quantity</td>";
					echo "<td><a href=\"item_manage.php?item=$item_code\">$item_name</a>";

					//$item_data = substr($item_data,14); //olditemdata
					$item_data = substr($item_data,8);
					//echo "<td></td><td></td><td></td><td></td><td>$full_code</td>";
				}
				else {
					$full_code .= substr($item_data,0,10);
					$quantity = hexdec(substr($item_data,8,2) . substr($item_data,6,2));
					echo "<td>$quantity</td>";
					$item_data = substr($item_data,26);
					//$item_data = substr($item_data,10); //olditemdata
					echo "<td><a href=\"item_manage.php?item=$item_code\">$item_name</a>";
					//echo "<td></td><td></td><td></td><td></td><td>$full_code</td>";
				}
				echo "</tr>\n";
			}
			echo "</table>";
		}
	}
	else {
		if ($source_type == "account_id") {
			$account_name = AccountID_To_UserID($name_id);
			$header[0] = "Account $account_name Storage";
			$table[0] = "storage";
			$delete[0] = "delete_stor";
			$stop = 1;
		}
		elseif ($source_type = "char_id") {
			$char_name = CharID_To_CharName($name_id);
			$header[0] = "$char_name's Inventory:";
			$header[1] = "$char_name's Cart Items:";
			$table[0] = "inventory";
			$table[1] = "cart_inventory";
			$delete[0] = "delete_inv";
			$delete[1] = "delete_cart";
			$stop = 2;
		}

		for ($i = 0; $i < $stop; $i++) {
			// Selects the items in character's inventory
			$query = "
			SELECT {$table[$i]}.id, amount, nameid, name0.name_japanese, refine,
			name1.name_japanese, card0, name2.name_japanese, card1, name3.name_japanese, card2, name4.name_japanese, card3
			FROM `{$table[$i]}`
			LEFT JOIN $cp_db.item_db AS name0 ON {$table[$i]}.nameid = name0.ID
			LEFT JOIN $cp_db.item_db AS name1 ON {$table[$i]}.card0 = name1.ID
			LEFT JOIN $cp_db.item_db AS name2 ON {$table[$i]}.card1 = name2.ID
			LEFT JOIN $cp_db.item_db AS name3 ON {$table[$i]}.card2 = name3.ID
			LEFT JOIN $cp_db.item_db AS name4 ON {$table[$i]}.card3 = name4.ID
			WHERE $source_type = $name_id
			";

			$result = execute_query($query, "item_functions.php");
			/* Printing results in HTML */
			EchoHead(100);
			echo "
		<tr class=mytitle>
			<td colspan=8>{$header[$i]}</td>
		</tr>
		<tr class=myheader>
			<td>Delete</td>
			<td>Quantity</td>
			<td>Item</td>
			<td>Card 1</td>
			<td>Card 2</td>
			<td>Card 3</td>
			<td>Card 4</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>\n";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 0) {
						$col_value = "<a href=\"item_manage.php?{$delete[$i]}=$col_value\">Delete</a>";
					}
					elseif ($display_index == 1) {
					}
					elseif ($display_index == 2) {
						// converts the item in nameid
						$display = convert_equip($line[3], $line[4], $line[6], $line[8]);
						if ($display == "") {
							$display = "Unknown Item {$line[2]}";
						}
						$col_value = "<a href=item_manage.php?item_id={$line[2]}>$display</a>";
					}
					elseif ($display_index == 4) {
						continue;
					}
					else {
						continue;
					}
					echo "<td>$col_value</td>\n";
				}
				echo "
				<td><a href=item_manage.php?item_id={$line[6]}>{$line[5]}</a></td>
				<td><a href=item_manage.php?item_id={$line[8]}>{$line[7]}</a></td>
				<td><a href=item_manage.php?item_id={$line[10]}>{$line[9]}</a></td>
				<td><a href=item_manage.php?item_id={$line[12]}>{$line[11]}</a></td>
			</tr>\n";
			}
			echo "</table>\n";
		}
	}
}

function convert_equip ($weapon_name, $refine, $card0, $card1) {
	// This is the function that is used to convert the different numbers
	// Into Upgrade Level (+5), VS Level (Very Very Strong), and element (Fire)
	// Never figured out how to obtain the name of the forger, but for admin purposes,
	// that is not really that useful.
	// First, determine upgrade level
	$refine_expression = $refine;
	// Since VS/Elemental Armors CANNOT have cards in them, a 255 in Card0 signifies that
	// the following weapon is VS/Elemental, since card 255 does not exist
	if ($card0 != 255) {
		// Card0 is not 255, therefore, cannot be a VS/Elemental Weapon
		if ($refine > 0) {
			// Refine level is > 0
			$final_expression = "+" . $refine . " ";		// add in upgrade
		}
		return $final_expression .= $weapon_name;	//return the standard item name
	}

	// determine if there are any VVS in them
	$star_crumb_level = intval($card1 / 1280);		// Athena uses card1 \ 1280 for level of star crumbs
	if ($star_crumb_level > 0) {
		for ($i = 1; $i < $star_crumb_level + 1; $i++) {
			$star_crumb_expression .= "Very ";	// Adds the appropriate # of 'Very's'
		}
		$star_crumb_expression .= "Strong ";	// Adds the "Strong"
	}
	// determine element
	$element_type = $card1 % 1280;					// Athena uses card1 MOD 1280 for element
	if ($element_type > 0) {
		// Weapon is elemental
		switch ($element_type) {
			case 1:
			$element_expression = "Ice ";
			break;
			case 2:
			$element_expression = "Earth ";
			break;
			case 3:
			$element_expression = "Fire ";
			break;
			case 4:
			$element_expression = "Wind ";
			break;
		}
	}
	$equip_name = $weapon_name;		// The Name of the Actual Equip is obtained
	// without any modifiers
	if ($refine > 0) {
		$final_expression = "+" . $refine . " ";		// add in upgrade
	}
	if ($star_crumb_level > 0) {
		$final_expression .= $star_crumb_expression;		// Add V
	}
	if ($element_type > 0) {
		$final_expression .= $element_expression;		// Add Element
	}
	$final_expression .= $equip_name;	// Add Weapon Name
	return $final_expression;		// Returns the final expression (+5 Very Very Strong Fire Damascus)
}

function IsEquip($ID) {
	$db = array('armor','weapon','bothhand','bow','armorMB','armorTB','armorTM','armorTMB','gun');

	foreach ($db as $query_table) {
		$query = sprintf(IS_IN_ITEMDB, $query_table, $ID);
		$result = execute_query($query, "item_functions.php");
		if ($result->RowCount() > 0) {
			return true;
		}
	}
	return false;
}

function IsQuest($ID) {

	$db = array('guest');

	foreach ($db as $query_table) {
		$query = sprintf(IS_IN_ITEMDB, $query_table, $ID);
		$result = execute_query($query, "item_functions.php");
		if ($result->RowCount() > 0) {
			return true;
		}
	}
	return false;
}

function IsArrow($ID) {

	$db = array('arrow','cannonball','ammo');

	foreach ($db as $query_table) {
		$query = sprintf(IS_IN_ITEMDB, $query_table, $ID);
		$result = execute_query($query, "item_functions.php");
		if ($result->RowCount() > 0) {
			return true;
		}
	}
	return false;
}

?>
