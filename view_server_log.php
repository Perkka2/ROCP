<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
//Aegis
if ($CONFIG_server_type == 0) {
	EchoHead(80);
	echo "
	<tr class=mytitle>
			<td>$CONFIG_server_name Server Logs</td>
		</tr>
		<tr class=mycell>
			<td>
				<a href=\"view_server_log.php?view=compositionLog\">compositionLog</a><br>
				<a href=\"view_server_log.php?view=itemLog\">itemLog</a><br>
				<a href=\"view_server_log.php?view=makingLog\">makingLog</a><br>
				<a href=\"view_server_log.php?view=refiningLog\">refiningLog</a><br>
			</td>
		</tr>
	</table>
	";
	if ($GET_view) {
		if (!$GET_page) {
			$GET_page = 1;
		}
		$offset = ($GET_page * $CONFIG_results_per_page) - $CONFIG_results_per_page;
		EchoHead(100);
		if (!$GET_column) {
			$GET_column = 1;
			$GET_value = 1;
		}
		
		if ($GET_column == "nameid") {
			if ($GET_view == "compositionLog") {
				$condition = "$GET_value IN(slot1, slot2, slot3, slot4)";
			}
			elseif ($GET_view == "itemLog") {
				$condition = "$GET_value IN(ItemID, slot1, slot2, slot3, slot4)";
			}
			elseif ($GET_view == "makingLog") {
				$condition = "$GET_value IN(ItemID, meterial1, meterial2, meterial3)";
			}
		}
		else {
			$condition = "$GET_column = '$GET_value'";
		}
		if ($GET_act == "gm") {
			if ($GET_view == "itemLog") {
				$condition .= "
				AND (
				((src.AID IS NOT NULL) AND (des.AID IS NULL))
				OR
				((src.AID IS NULL) AND (des.AID IS NOT NULL))
				)
				";
			}
			else {
				$condition .= "
				AND src.AID IS NOT NULL
				";
			}
		}
		if (!$GET_act) {
			$pages_query = "SELECT count(*) FROM ItemLog.dbo.$GET_view WHERE $condition";
		}
		else {
			if ($GET_view == "itemLog") {
				$pages_query = "SELECT count(*) 
				FROM ItemLog.dbo.$GET_view 
				LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.srcAccountID
				LEFT JOIN cp.dbo.privilege AS des ON des.AID = ItemLog.dbo.$GET_view.desAccountID
				WHERE $condition
				";
			}
			else {
				$pages_query = "SELECT count(*) 
				FROM ItemLog.dbo.$GET_view 
				LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.accountID
				WHERE $condition
				";
			}
		}
		switch($GET_view) {
			case "compositionLog":
				if (!$GET_act) {
					$query = "SELECT date, accountID, accountName, characterID, characterName,
					mapName, addedCardID, Name1.Name, slot1, Name2.Name, slot2, Name3.Name, slot3, 
					Name4.Name, slot4
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name1 ON Name1.ID = ItemLog.dbo.$GET_view.slot1
					LEFT JOIN cp.dbo.item_db AS Name2 ON Name2.ID = ItemLog.dbo.$GET_view.slot2
					LEFT JOIN cp.dbo.item_db AS Name3 ON Name3.ID = ItemLog.dbo.$GET_view.slot3
					LEFT JOIN cp.dbo.item_db AS Name4 ON Name4.ID = ItemLog.dbo.$GET_view.slot4
					WHERE $condition
					ORDER BY date
					";
				}
				else {
					$query = "SELECT date, accountID, accountName, characterID, characterName,
					mapName, addedCardID, Name1.Name, slot1, Name2.Name, slot2, Name3.Name, slot3, 
					Name4.Name, slot4
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name1 ON Name1.ID = ItemLog.dbo.$GET_view.slot1
					LEFT JOIN cp.dbo.item_db AS Name2 ON Name2.ID = ItemLog.dbo.$GET_view.slot2
					LEFT JOIN cp.dbo.item_db AS Name3 ON Name3.ID = ItemLog.dbo.$GET_view.slot3
					LEFT JOIN cp.dbo.item_db AS Name4 ON Name4.ID = ItemLog.dbo.$GET_view.slot4
					LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.accountID
					WHERE $condition
					ORDER BY date
					";
				}
				$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
				EchoHead(100);
				echo "
				<tr class=mytitle>
					<td colspan=9>Composition Log</td>
				</tr>
				<tr class=mytitle>
					<td colspan=9><a href=\"view_server_log.php?view=$GET_view&act=gm\">GM Composition</a></td>
				</tr>
				<tr class=myheader>
					<td>Date</td>
					<td>ID</td>
					<td>charname</td>
					<td>Map</td>
					<td>addedCardID</td>
					<td>Card 1</td>
					<td>Card 2</td>
					<td>Card 3</td>
					<td>Card 4</td>
				</tr>
				";
				$skipped_columns = array(1, 3, 8, 10, 12, 14);
				$card_columns = array(7, 9, 11, 13);
				while ($line = $result->FetchRow()) {
					echo "<tr class=mycell>";
					foreach ($line as $display_index => $col_value) {
						if (in_array($display_index, $skipped_columns)) {
							continue;
						}
						elseif (in_array($display_index, $card_columns)) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index + 1]}\">$col_value</a>";
						}
						elseif ($display_index == 0) {
							$col_value = convert_date($col_value);
						}
						elseif ($display_index == 2) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=accountID&value={$line[1]}\">$col_value</a>";
						}
						elseif ($display_index == 4) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=characterID&value={$line[3]}\">$col_value</a>";
						}
						elseif ($display_index == 5) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=mapName&value={$line[5]}\">$col_value</a>";
						}
						echo "<td>$col_value</td>";
					}
					echo "</tr>";
				}
				break;
			case "itemLog":
				if (!$GET_act) {
					$query = "SELECT Action, logtime, ip, srcAccountID, srcAccountName, srcCharID, srcCharName,
					desAccountID, desAccountName, desCharID, desCharName, ItemName, ItemID, ItemCount, MapName,
					ItemLog.dbo.itemLog.price, Name1.Name, slot1, Name2.Name, slot2, Name3.Name, slot3, Name4.Name, slot4, slot5,
					Serialcode
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name1 ON Name1.ID = ItemLog.dbo.$GET_view.slot1
					LEFT JOIN cp.dbo.item_db AS Name2 ON Name2.ID = ItemLog.dbo.$GET_view.slot2
					LEFT JOIN cp.dbo.item_db AS Name3 ON Name3.ID = ItemLog.dbo.$GET_view.slot3
					LEFT JOIN cp.dbo.item_db AS Name4 ON Name4.ID = ItemLog.dbo.$GET_view.slot4
					WHERE $condition
					ORDER BY logtime
					";
				}
				else {
					$query = "SELECT Action, logtime, ip, srcAccountID, srcAccountName, srcCharID, srcCharName,
					desAccountID, desAccountName, desCharID, desCharName, ItemName, ItemID, ItemCount, MapName,
					ItemLog.dbo.itemLog.price, Name1.Name, slot1, Name2.Name, slot2, Name3.Name, slot3, Name4.Name, slot4, slot5,
					Serialcode, src.AID, des.AID
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name1 ON Name1.ID = ItemLog.dbo.$GET_view.slot1
					LEFT JOIN cp.dbo.item_db AS Name2 ON Name2.ID = ItemLog.dbo.$GET_view.slot2
					LEFT JOIN cp.dbo.item_db AS Name3 ON Name3.ID = ItemLog.dbo.$GET_view.slot3
					LEFT JOIN cp.dbo.item_db AS Name4 ON Name4.ID = ItemLog.dbo.$GET_view.slot4
					LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.srcAccountID
					LEFT JOIN cp.dbo.privilege AS des ON des.AID = ItemLog.dbo.$GET_view.desAccountID
					WHERE $condition
					ORDER BY logtime
					";
				}	
				$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
				EchoHead(100);
				echo "
				<tr class=mytitle>
					<td colspan=16>ItemLog</td>
				</tr>
				<tr class=mytitle>
					<td colspan=16><a href=\"view_server_log.php?view=$GET_view&act=gm\">GM Interactions</a></td>
				</tr>
				<tr class=myheader>
					<td>Action</td>
					<td>Date</td>
					<td>IP</td>
					<td>srcAcc</td>
					<td>srcChar</td>
					<td>desAcc</td>
					<td>desChar</td>
					<td>Item</td>
					<td>Amount</td>
					<td>Map</td>
					<td>Zeny</td>
					<td>Card1</td>
					<td>Card2</td>
					<td>Card3</td>
					<td>Card4</td>
					<td>Trace</td>
				</tr>
				";
				$skipped_columns = array(3, 5, 7, 9, 12, 17, 19, 21, 23, 24, 26, 27);
				$card_columns = array(16, 18, 20, 22);
				$src_cols = array(3, 4, 5, 6);
				$des_cols = array(7, 8, 9, 10);
				while ($line = $result->FetchRow()) {
					echo "<tr class=mycell>";
					foreach ($line as $display_index => $col_value) {
						if ($GET_act) {
							// GM interactions are being viewed
							if ($line[26] != NULL) {
								$src_gm = true;
							}
							if ($line[27] != NULL) {
								$des_gm = true;
							}
						}
						else {
							$src_gm = false;
							$des_gm = false;
						}
						if (in_array($display_index, $skipped_columns)) {
							continue;
						}
						elseif (in_array($display_index, $card_columns)) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index + 1]}&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 0) {
							$display_string = array(0 => "Drop", 1 => "Pick", 
							3 => "Trade", 4 => "BuyVend", 5 => "BuyNPC", 
							6 => "SellVend", 7 => "MVP", 9 => "GiveFromNPC", 
							10 => "GiveToNPC"
							);
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=Action&value=$col_value&act=$GET_act\">{$display_string[$col_value]}</a>";
						}
						elseif ($display_index == 1) {
							$col_value = convert_date($col_value);
						}
						elseif ($display_index == 2) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=ip&value=$col_value&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 4) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=srcAccountID&value={$line[3]}&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 6) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=srcCharID&value={$line[5]}&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 8) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=desAccountID&value={$line[7]}&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 10) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=desCharID&value={$line[9]}&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 11) {
							if ($line[24] > 0) {
								$col_value = "
								<a href=\"view_server_log.php?view=$GET_view&column=slot5&value={$line[24]}&act=$GET_act\">+{$line[24]}</a>
								<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[12]}&act=$GET_act\">$col_value</a>
								";
							}
							else {
								$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[12]}&act=$GET_act\">$col_value</a>";
							}
						}
						elseif ($display_index == 14) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=mapName&value=$col_value&act=$GET_act\">$col_value</a>";
						}
						elseif ($display_index == 25) {
							if ($col_value > 0) {
								$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=Serialcode&value=$col_value&act=$GET_act\">Trace</a>";
							}
							else {
								$col_value = "";
							}
						}
						if (in_array($display_index, $src_cols) && $src_gm) {
							echo "<td><b>$col_value</b></td>";
						}
						elseif (in_array($display_index, $des_cols) && $des_gm) {
							echo "<td><b>$col_value</b></td>";
						}
						else {
							echo "<td>$col_value</td>";
						}
					}
					echo "</tr>";
				}
				break;
			case "makingLog":
				if (!$GET_act) {
					$query = "SELECT date, accountID, accountName, characterID, characterName, 
					mapName, success, Name.Name, itemID, Slot1.Name, meterial1, Slot2.Name, meterial2, 
					Slot3.Name, meterial3
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name ON Name.ID = ItemLog.dbo.$GET_view.ItemID
					LEFT JOIN cp.dbo.item_db AS Slot1 ON Slot1.ID = ItemLog.dbo.$GET_view.meterial1
					LEFT JOIN cp.dbo.item_db AS Slot2 ON Slot2.ID = ItemLog.dbo.$GET_view.meterial2
					LEFT JOIN cp.dbo.item_db AS Slot3 ON Slot3.ID = ItemLog.dbo.$GET_view.meterial3
					WHERE $condition
					ORDER BY date
					";
				}
				else {
					$query = "SELECT date, accountID, accountName, characterID, characterName, 
					mapName, success, Name.Name, itemID, Slot1.Name, meterial1, Slot2.Name, meterial2, 
					Slot3.Name, meterial3
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name ON Name.ID = ItemLog.dbo.$GET_view.ItemID
					LEFT JOIN cp.dbo.item_db AS Slot1 ON Slot1.ID = ItemLog.dbo.$GET_view.meterial1
					LEFT JOIN cp.dbo.item_db AS Slot2 ON Slot2.ID = ItemLog.dbo.$GET_view.meterial2
					LEFT JOIN cp.dbo.item_db AS Slot3 ON Slot3.ID = ItemLog.dbo.$GET_view.meterial3
					LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.accountID
					WHERE $condition
					ORDER BY date
					";
				}
				$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
				EchoHead(100);
				echo "
				<tr class=mytitle>
					<td colspan=9>Production Log</td>
				</tr>
				<tr class=mytitle>
					<td colspan=9><a href=\"view_server_log.php?view=$GET_view&act=gm\">GM Production</a></td>
				</tr>
				<tr class=myheader>
					<td>Date</td>
					<td>Account</td>
					<td>Char</td>
					<td>Map</td>
					<td>Success?</td>
					<td>Item</td>
					<td>Slot 1</td>
					<td>Slot 2</td>
					<td>Slot 3</td>
				</tr>
				";
				$skipped_columns = array(1, 3, 8, 10, 12, 14);
				$slot_columns = array(7, 9, 11, 13);
				while ($line = $result->FetchRow()) {
					echo "<tr class=mycell>";
					foreach ($line as $display_index => $col_value) {
						if (in_array($display_index, $skipped_columns)) {
							continue;
						}
						if (in_array($display_index, $slot_columns)) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index + 1]}\">$col_value</a>";
						}
						elseif ($display_index == 0) {
							$col_value = convert_date($col_value);
						}
						elseif ($display_index == 2) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=accountID&value={$line[1]}\">$col_value</a>";
						}
						elseif ($display_index == 4) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=charID&value={$line[3]}\">$col_value</a>";
						}
						elseif ($display_index == 5) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=mapName&value=$col_value\">$col_value</a>";
						}
						elseif ($display_index == 6) {
							$col_value = $col_value == 1? "<font color=green>Yes</font>" : "<font color=red>No</font>";
						}
						echo "<td>$col_value</td>";
					}
					echo "</tr>";
				}
				break;
			case "refiningLog":
				if (!$GET_act) {
					$query = "SELECT date, accountID, accountName, characterID, characterName, 
					mapName, success, Name.Name, itemID, itemLevel
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name ON Name.ID = ItemLog.dbo.$GET_view.ItemID
					WHERE $condition
					ORDER BY date
					";
				}
				else {
					$query = "SELECT date, accountID, accountName, characterID, characterName, 
					mapName, success, Name.Name, itemID, itemLevel
					FROM ItemLog.dbo.$GET_view
					LEFT JOIN cp.dbo.item_db AS Name ON Name.ID = ItemLog.dbo.$GET_view.ItemID
					LEFT JOIN cp.dbo.privilege AS src ON src.AID = ItemLog.dbo.$GET_view.accountID
					WHERE $condition
					ORDER BY date
					";
				}
				$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
				EchoHead(100);
				echo "
				<tr class=mytitle>
					<td colspan=7>Refine Log</td>
				</tr>
				<tr class=mytitle>
					<td colspan=7><a href=\"view_server_log.php?view=$GET_view&act=gm\">GM Refines</a></td>
				</tr>
				<tr class=myheader>
					<td>Date</td>
					<td>Account</td>
					<td>Char</td>
					<td>Map</td>
					<td>Success?</td>
					<td>Item</td>
					<td>Level</td>
				</tr>
				";
				$skipped_columns = array(1, 3, 8);
				while ($line = $result->FetchRow()) {
					echo "<tr class=mycell>";
					foreach ($line as $display_index => $col_value) {
						if (in_array($display_index, $skipped_columns)) {
							continue;
						}
						elseif ($display_index == 0) {
							$col_value = convert_date($col_value);
						}
						elseif ($display_index == 2) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=accountID&value={$line[1]}\">$col_value</a>";
						}
						elseif ($display_index == 4) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=characterID&value={$line[3]}\">$col_value</a>";
						}
						elseif ($display_index == 5) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=mapName&value=$col_value\">$col_value</a>";
						}
						elseif ($display_index == 6) {
							$col_value = $col_value == 1? "<font color=green>Yes</font>" : "<font color=red>No</font>";
						}
						elseif ($display_index == 7) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=itemID&value={$line[8]}\">$col_value</a>";
						}
						elseif ($display_index == 9) {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=itemLevel&value=$col_value\">$col_value</a>";
						}
						echo "<td>$col_value</td>";
					}
					echo "</tr>";
				}
				break;
		}
		
		$pages_result = execute_query($pages_query, "view_server_log.php");
		$line = $pages_result->FetchRow();
		$rows = $line[0];
		$max_pages = intval($rows / $CONFIG_results_per_page) + 1;
		echo "
		<tr class=mycell>
			<td colspan=20>
		";
		for ($i = 1; $i < $max_pages; $i++) {
			echo "<a href=\"view_server_log.php?view=$GET_view&page=$i&column=$GET_column&value=$GET_value&act=$GET_act\">$i</a>";
			if ($i % 50 == 0) {
				echo "<br>";
			}
			else {
				echo "-";
			}
		}
		echo "<a href=\"view_server_log.php?view=$GET_view&page=$i&column=$GET_column&value=$GET_value&act=$GET_act\">$i</a>
			</td>
		</tr>";
		
		echo "
	</table>
		";
	}
}
//Else Athena
elseif (($CONFIG_server_type == 1) || ($CONFIG_server_type == 2 && $CONFIG_db_logs)) {
	EchoHead(80);
	if ($CONFIG_server_type == 1) {
	echo "
		<tr class=mytitle>
			<td>$CONFIG_server_name Server Logs</td>
		</tr>
		<tr class=mycell>
			<td>
				<a href=\"view_server_log.php?view=atcommandlog\">Atcommands</a><br>
				<a href=\"view_server_log.php?view=branchlog\">Dead Branches</a><br>
				<a href=\"view_server_log.php?view=droplog\">Monster Drop Logs</a><br>
				<a href=\"view_server_log.php?view=mvplog\">MVP Logs</a><br>
				<a href=\"view_server_log.php?view=presentlog\">Presents</a><br>
				<a href=\"view_server_log.php?view=producelog\">Produce</a><br>
				<a href=\"view_server_log.php?view=refinelog\">Refine</a><br>
				<a href=\"view_server_log.php?view=tradelog\">Trades</a><br>
			</td>
		</tr>
	</table>
	";
	}
	else {
		echo "
		<tr class=mytitle>
			<td>$CONFIG_server_name Server Logs</td>
		</tr>
		<tr class=mycell>
			<td>
				<a href=\"view_server_log.php?view=atcommandlog\">Atcommands</a><br>
				<a href=\"view_server_log.php?view=branchlog\">Dead Branches</a><br>
				<a href=\"view_server_log.php?view=droplog\">Monster Drop Logs</a><br>
				<a href=\"view_server_log.php?view=chatlog\">Chat Logs</a><br>
				<a href=\"view_server_log.php?view=mvplog\">MVP Logs</a><br>
				<a href=\"view_server_log.php?view=npclog\">NPC Logs</a><br>
				<a href=\"view_server_log.php?view=presentlog\">Presents</a><br>
				<a href=\"view_server_log.php?view=producelog\">Produce</a><br>
				<a href=\"view_server_log.php?view=refinelog\">Refine</a><br>
				<a href=\"view_server_log.php?view=tradelog\">Trades</a><br>
				<a href=\"view_server_log.php?view=vendlog\">Vending</a><br>
			</td>
		</tr>
	</table>
	";
	}
	if ($GET_view) {
		if ((($GET_view == "chatlog") ||  ($GET_view == "npclog") || ($GET_view == "vendlog")) && ($CONFIG_server_type != 2)) { redir("view_server_log.php","Invalid log for this server"); }
		if (!$GET_page) {
			$GET_page = 1;
		}
		$offset = ($GET_page * $CONFIG_results_per_page) - $CONFIG_results_per_page;
		EchoHead(100);
		if (!$GET_column) {
			$GET_column = 1;
			$GET_value = 1;
		}
		if ($GET_type == "advanced") {
			switch($GET_column) {
				case "account_name":
					$GET_column = "$GET_view.account_id";
					$GET_value = UserID_To_AccountID($GET_value);
					break;
				case "src_account_name":
					$GET_column = "src_account_id";
					$GET_value = UserID_To_AccountID($GET_value);
					break;
				case "des_account_name":
					$GET_column = "des_account_id";
					$GET_value = UserID_To_AccountID($GET_value);
					break;
				case "nameid":
					$GET_column = "nameid";
					$GET_value = ItemName_To_ItemID($GET_value);
					break;
				case "zeny":
					$GET_column = "$GET_view.zeny";
					break;		
				case "userid":
					$GET_column = "src_accountid";
					$GET_value = UserID_To_AccountID($GET_value);
				break;
				case "message":
					$GET_column = "message";
					$GET_value = $GET_value;
				break;
				case "src_char_name":
				case "des_char_name":
			}
		}
		if ($GET_column == "nameid") {
			if ($GET_view == "presentlog") {
				$condition = "nameid = '$GET_value'";
			}
			else {
				$condition = "'$GET_value' IN(nameid, card0, card1, card2, card3)";
			}
		}
		elseif ($GET_column == "itemnameid") {
			$condition = "'$GET_value' IN(item1, item2, item3, item4, item5, item6, item7, item8, item9)";
		}
		elseif ($GET_column == "material") {
			$condition = "'$GET_value' IN(slot1, slot2, slot3)";
		}
		elseif ($GET_column == "$GET_view.zeny") {
			$condition = "$GET_column >= $GET_value ORDER BY zeny DESC";
		}
		elseif ($GET_column == "command") {
			$condition = "$GET_column LIKE '%$GET_value%'";
		}
		elseif ($GET_column == "message") {
			$condition = "$GET_column LIKE '%$GET_value%'";
		}
		else {	
			$condition = "$GET_column = '$GET_value'";
		}
		if ($CONFIG_server_type != 2) { $logs = 'log'; } else { $logs = $CONFIG_db_logs; }
		$pages_query = "SELECT count(*) FROM $logs.$GET_view WHERE $condition";
		if ($CONFIG_server_type == 1) { $namefield = "Name"; } else { $namefield = "name_japanese"; }
		switch($GET_view) {
		case "atcommandlog":
			$query = "SELECT atcommand_id, atcommand_date, $GET_view.account_id, login.userid, $GET_view.char_id, char_name, map, command
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON $CONFIG_db_name.login.account_id = $GET_view.account_id
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=6>Atcommand Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Map</td>
			<td>Command</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 2) {
						continue;
					}
					elseif ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 4) {
						continue;
					}
					elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.char_id&value={$line[4]}\">$col_value</a>";
					}
					elseif ($display_index == 6) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[6]}\">$col_value</a>";
					}
					elseif ($display_index == 7) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=command&value={$line[7]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			echo "
		<form action=\"view_server_log.php\" method=\"GET\">
				<input type=\"hidden\" name=\"view\" value=\"$GET_view\">
				<tr class=mycell>
					<td colspan=20>
					Search: 
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<select name=\"column\" class=\"myctl\" size=\"1\">
						<option value=\"account_name\">Account Name</option>
						<option value=\"char_name\">Char Name</option>
						<option value=\"map\">Map</option>
						<option value=\"command\">Command</option>
					</select>
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
					</td>
				</tr>
				<input type=\"hidden\" name=\"type\" value=\"advanced\">
		</form>
			";
		break;
		case "branchlog":
			$query = "SELECT branch_id, branch_date, $CONFIG_db_name.login.account_id, $CONFIG_db_name.login.userid, char_name, map
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON $CONFIG_db_name.login.account_id = $GET_view.account_id
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=5>Dead Branch Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Map</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {//VICH
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
				if ($display_index == 2) {
					continue;
				}
				elseif ($display_index == 3) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[2]}\">$col_value</a>";
				}
				elseif ($display_index == 4) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=char_name&value={$line[4]}\">$col_value</a>";
				}
				elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[5]}\">$col_value</a>";
				}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			echo "
		<form action=\"view_server_log.php\" method=\"GET\">
				<input type=\"hidden\" name=\"view\" value=\"$GET_view\">
				<tr class=mycell>
					<td colspan=20>
					Search: 
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<select name=\"column\" class=\"myctl\" size=\"1\">
						<option value=\"account_name\">Account Name</option>
						<option value=\"char_name\">Char Name</option>
						<option value=\"map\">Map</option>
					</select>
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
					</td>
				</tr>
				<input type=\"hidden\" name=\"type\" value=\"advanced\">
		</form>
				
			";
			break;
		case "droplog":
			$query = "SELECT drop_id, drop_date, kill_char_id, $CONFIG_db_name.char.name, monster_id, mob.Name2, $GET_view.item1, name1.$namefield, $GET_view.item2, name2.$namefield, $GET_view.item3, name3.$namefield, $GET_view.item4, name4.$namefield,
			$GET_view.item5, name5.$namefield, $GET_view.item6, name6.$namefield, $GET_view.item7, name7.$namefield, $GET_view.item8, name8.$namefield, $GET_view.item9, name9.$namefield, map
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.char ON $CONFIG_db_name.char.char_id = $GET_view.kill_char_id
			LEFT JOIN $CONFIG_db_name.mob_db AS mob ON mob.ID = $GET_view.monster_id
			LEFT JOIN $CONFIG_db_name.item_db AS name1 ON name1.ID = $GET_view.item1
			LEFT JOIN $CONFIG_db_name.item_db AS name2 ON name2.ID = $GET_view.item2
			LEFT JOIN $CONFIG_db_name.item_db AS name3 ON name3.ID = $GET_view.item3
			LEFT JOIN $CONFIG_db_name.item_db AS name4 ON name4.ID = $GET_view.item4
			LEFT JOIN $CONFIG_db_name.item_db AS name5 ON name5.ID = $GET_view.item5
			LEFT JOIN $CONFIG_db_name.item_db AS name6 ON name6.ID = $GET_view.item6
			LEFT JOIN $CONFIG_db_name.item_db AS name7 ON name7.ID = $GET_view.item7
			LEFT JOIN $CONFIG_db_name.item_db AS name8 ON name8.ID = $GET_view.item8
			LEFT JOIN $CONFIG_db_name.item_db AS name9 ON name9.ID = $GET_view.item9
			WHERE $condition
			";
		$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
		echo "
		<tr class=mytitle>
			<td colspan=13>Drop Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Char Name</td>
			<td>Monster</td>
			<td colspan=8>Items</td>
			<td>Map</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index % 2 == 0 && $display_index != 0 && $display_index != 22) {
						continue;
					}
					if ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=kill_char_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=monster_id&value={$line[$display_index - 1]}\">$col_value</a>";
					}
					elseif (($display_index > 5) && ($display_index < 22)) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=itemnameid&value={$line[$display_index - 1]}\">$col_value</a>";
					}
					elseif ($display_index == 22) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[24]}\">{$line[24]}</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			break;
		case "mvplog":
			$query = "SELECT mvp_id, mvp_date, kill_char_id, char.name, monster_id, mob_db.Name2, prize, item_db.$namefield, mvpexp, map
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.mob_db ON $CONFIG_db_name.mob_db.ID = $GET_view.monster_id
			LEFT JOIN $CONFIG_db_name.item_db ON $CONFIG_db_name.item_db.ID = $GET_view.prize
			LEFT JOIN $CONFIG_db_name.char ON $CONFIG_db_name.char.char_id = $GET_view.kill_char_id
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=7>MVP Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Char Name</td>
			<td>Monster</td>
			<td>Prize</td>
			<td>EXP</td>
			<td>Map</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 2) {
						continue;
					}
					elseif ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=kill_char_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 4) {
						continue;
					}
					elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=monster_id&value={$line[4]}\">$col_value</a>";
					}
					elseif ($display_index == 6) {
						continue;
					}
					elseif ($display_index == 7) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=prize&value={$line[6]}\">$col_value</a>";
					}
					elseif ($display_index == 9) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[9]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			break;
		case "presentlog":
			$query = "SELECT present_id, present_date, src_id, $GET_view.account_id, $CONFIG_db_name.login.userid, char_id, char_name, nameid, item_db.$namefield, map
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON $CONFIG_db_name.login.account_id = $GET_view.account_id
			LEFT JOIN $CONFIG_db_name.item_db ON $CONFIG_db_name.item_db.ID = $GET_view.nameid
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=7>Present Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Source</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Item</td>
			<td>Map</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 2) {
						switch ($col_value) {
							case 1:
								$col_value = "OBB";
								break;
							case 2:
								$col_value = "OVB";
								break;
							case 3:
								$col_value = "OCA";
								break;
							case 4:
								$col_value = "GB";
								break;
							case 5:
								$col_value = "WOS";
								break;
						}
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=src_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 3) {
						continue;
					}
					elseif ($display_index == 4) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[3]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						continue;
					}
					elseif ($display_index == 6) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.char_id&value={$line[5]}\">$col_value</a>";
					}
					elseif ($display_index == 7) {
						continue;
					}
					elseif ($display_index == 8) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[7]}\">$col_value</a>";
					}
					elseif ($display_index == 9) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[9]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			break;
					
		case "producelog":
			$query = "SELECT produce_id, produce_date, $GET_view.account_id, $CONFIG_db_name.login.userid, char_name, nameid, item_db.$namefield, name1.$namefield, slot1, name2.$namefield, slot2, name3.$namefield, slot3, success, map
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON $CONFIG_db_name.login.account_id = $GET_view.account_id
			LEFT JOIN $CONFIG_db_name.item_db ON $CONFIG_db_name.item_db.ID = $GET_view.nameid
			LEFT JOIN $CONFIG_db_name.item_db AS name1 ON name1.ID = $GET_view.slot1
			LEFT JOIN $CONFIG_db_name.item_db AS name2 ON name2.ID = $GET_view.slot1
			LEFT JOIN $CONFIG_db_name.item_db AS name3 ON name3.ID = $GET_view.slot3
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=10>Produce Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Item</td>
			<td colspan=3>Materials</td>
			<td>Success?</td>
			<td>Map</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 2) {
						continue;
					}
					elseif ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 4) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=char_name&value={$line[4]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						continue;
					}
					elseif ($display_index == 6) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.nameid&value={$line[5]}\">$col_value</a>";
					}
					elseif (($display_index > 6) && ($display_index < 13)) {
						if ($display_index % 2 == 0) {
							continue;
						} else {
							$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=material&value={$line[$display_index+1]}\">$col_value</a>";
						}
					}
					elseif ($display_index == 13) {
						$col_value = $col_value == 1? "<font color=green>Yes</font>" : "<font color=red>No</font>";
					}
					elseif ($display_index == 14) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[14]}\">$col_value</a>";
					}				
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			break;
		case "refinelog":
				$query = "SELECT refine_id, refine_date, $GET_view.account_id, $CONFIG_db_name.login.userid, char_id, char_name, nameid, equip.$namefield, card0, name0.$namefield, card1, name1.$namefield, card2, name2.$namefield, card3, name3.$namefield, success, map, item_level
				FROM $logs.$GET_view
				LEFT JOIN $CONFIG_db_name.login ON $CONFIG_db_name.login.account_id = $GET_view.account_id
				LEFT JOIN $CONFIG_db_name.item_db AS equip ON equip.ID = $GET_view.nameid
				LEFT JOIN $CONFIG_db_name.item_db AS name0 ON name0.ID = $GET_view.card0
				LEFT JOIN $CONFIG_db_name.item_db AS name1 ON name1.ID = $GET_view.card1
				LEFT JOIN $CONFIG_db_name.item_db AS name2 ON name2.ID = $GET_view.card2
				LEFT JOIN $CONFIG_db_name.item_db AS name3 ON name3.ID = $GET_view.card3
				WHERE $condition
				";
				if ($GET_sort == "item_level") { $query .= "\nORDER BY $GET_sort DESC"; }
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=12>Refine Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Item</td>
			<td>Card 1</td>
			<td>Card 2</td>
			<td>Card 3</td>
			<td>Card 4</td>
			<td>Success?</td>
			<td>Map</td>
			<td><a href=\"view_server_log.php?view=$GET_view&sort=item_level\">Level</a></td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index % 2 == 0 && $display_index != 0 && $display_index < 16) {
						continue;
					}
					elseif ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.char_id&value={$line[4]}\">$col_value</a>";
					}
					elseif ($display_index >= 7 && $display_index <= 15) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index - 1]}\">$col_value</a>";
					}
					elseif ($display_index == 16) {
						$col_value = $col_value == 1? "<font color=green>Yes</font>" : "<font color=red>No</font>";
					}					
					elseif ($display_index == 17) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[17]}\">$col_value</a>";
					}
					elseif ($display_index == 18) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=item_level&value={$line[18]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</td>";
			}
			break;
		case "tradelog":
			$query = "SELECT trade_id, trade_date, src_account_id, src.userid, src_char_id, src_char.name, des_account_id, des.userid, des_char_id, des_char.name,
			amount, refine, nameid, equip.$namefield, card0, name0.$namefield, card1, name1.$namefield, card2, name2.$namefield, card3, name3.$namefield, map, $GET_view.zeny
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login AS src ON src.account_id = $GET_view.src_account_id
			LEFT JOIN $CONFIG_db_name.char AS src_char ON src_char.char_id = $GET_view.src_char_id
			LEFT JOIN $CONFIG_db_name.login AS des ON des.account_id = $GET_view.des_account_id
			LEFT JOIN $CONFIG_db_name.char AS des_char ON des_char.char_id = $GET_view.des_char_id
			
			LEFT JOIN $CONFIG_db_name.item_db AS equip ON equip.ID = $GET_view.nameid
			LEFT JOIN $CONFIG_db_name.item_db AS name0 ON name0.ID = $GET_view.card0
			LEFT JOIN $CONFIG_db_name.item_db AS name1 ON name1.ID = $GET_view.card1
			LEFT JOIN $CONFIG_db_name.item_db AS name2 ON name2.ID = $GET_view.card2
			LEFT JOIN $CONFIG_db_name.item_db AS name3 ON name3.ID = $GET_view.card3
			WHERE $condition
			";
			if ($GET_sort == "zeny") { $query .= "\nORDER BY $GET_sort DESC"; }
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=15>Trade Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Src Acc</td>
			<td>Src Char</td>
			<td>Des Acc</td>
			<td>Des Char</td>
			<td>Amount</td>
			<td>Refine</td>
			<td>Name</td>
			<td colspan=4>Cards</td>
			<td>Map</td>
			<td><a href=\"view_server_log.php?view=$GET_view&sort=zeny\">Zeny</a></td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index % 2 == 0 && $display_index != 0 && $display_index != 10 && $display_index <= 20) {
						continue;
					}
					elseif ($display_index == 3) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.src_account_id&value={$line[2]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.src_char_id&value={$line[4]}\">$col_value</a>";
					}
					elseif ($display_index == 7) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.des_account_id&value={$line[6]}\">$col_value</a>";
					}
					elseif ($display_index == 9) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.des_char_id&value={$line[8]}\">$col_value</a>";
					}
					elseif ($display_index >= 13 && $display_index <= 21) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index - 1]}\">$col_value</a>";
					}
					elseif ($display_index == 22) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[22]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</tr>\n";
			}
			echo "
		<form action=\"view_server_log.php\" method=\"GET\">
				<input type=\"hidden\" name=\"view\" value=\"$GET_view\">
				<tr class=mycell>
					<td colspan=20>
					Search: 
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<select name=\"column\" class=\"myctl\" size=\"1\">
						<option value=\"src_account_name\">Source Account Name</option>
						<option value=\"src_char_name\">Source Char Name</option>
						<option value=\"des_account_name\">Des Account Name</option>
						<option value=\"des_char_name\">Des Char Name</option>
						<option value=\"nameid\">Item Name</option>
						<option value=\"zeny\">Zeny</option>
					</select>
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
					</td>
				</tr>
				<input type=\"hidden\" name=\"type\" value=\"advanced\">
		</form>
			";
		break;
		case "chatlog":
			$query = "SELECT id, time, type, src_accountid, $CONFIG_db_name.login.userid, src_charid, src_char.name, dst_charname, src_map, message
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON login.account_id = $GET_view.src_accountid
			LEFT JOIN $CONFIG_db_name.char AS src_char ON src_char.char_id = $GET_view.src_charid
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=8>Chat Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Type</td>
			<td>Src Acc</td>
			<td>Src Char</td>
			<td>Des Char</td>
			<td>Map</td>
			<td>Message</td>
		</tr>
			";
			while ($line = $result->FetchRow()) {
				echo "<tr class=mycell>";
				foreach ($line as $display_index => $col_value) {
					if ($display_index == 2) {
					switch($col_value) { case "W": $col_value = "Whisper"; break; case "P": $col_value = "Party"; break; case "G": $col_value = "Guild"; break; }
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=type&value={$line[$display_index]}\">$col_value</a>";
					}
					elseif ($display_index == 3) {
						continue;
					}
					elseif ($display_index == 4) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=src_accountid&value={$line[3]}\">$col_value</a>";
					}
					elseif ($display_index == 5) {
						continue;
					}
					elseif ($display_index == 6) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=src_charid&value={$line[5]}\">$col_value</a>";
					}
					elseif ($display_index == 7) {
						if ($line[2] != "W") { $col_value = ""; }
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=dst_charname&value={$line[7]}\">$col_value</a>";
					}
					elseif ($display_index == 8) {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=src_map&value={$line[$display_index]}\">$col_value</a>";
					}
					echo "<td>$col_value</td>";
				}
				echo "</tr>\n";
			}
			echo "
				<form action=\"view_server_log.php\" method=\"GET\">
				<input type=\"hidden\" name=\"view\" value=\"$GET_view\">
				<tr class=mycell>
					<td colspan=20>
					Search: 
					<input type=\"text\" name=\"value\" class=\"myctl\">
					<select name=\"column\" class=\"myctl\" size=\"1\">
						<option value=\"userid\">Source Account Name</option>
						<option value=\"src_char.name\">Source Char Name</option>
						<option value=\"dst_charname\">Des Char Name</option>
						<option value=\"message\">Message</option>
					</select>
					<input type=\"submit\" class=\"myctl\" value=\"Search\">
					</td>
				</tr>
				<input type=\"hidden\" name=\"type\" value=\"advanced\">
		</form>";
		break;
		case "npclog":
			$query = "SELECT npc_id, npc_date, $GET_view.account_id, $CONFIG_db_name.login.userid, char_id, char_name, map, mes
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login ON login.account_id = $GET_view.account_id
			WHERE $condition
			";
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=6>NPC Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Account Name</td>
			<td>Char Name</td>
			<td>Map</td>
			<td>Message</td>
		</tr>
			";
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>";
			foreach ($line as $display_index => $col_value) {
				if ($display_index == 2) {
					continue;
				}
				elseif ($display_index == 3) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=$GET_view.account_id&value={$line[2]}\">$col_value</a>";
				}
				elseif ($display_index == 4) {
					continue;
				}
				elseif ($display_index == 5) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=char_id&value={$line[4]}\">$col_value</a>";
				}
				elseif ($display_index == 6) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[$display_index]}\">$col_value</a>";
				}
				echo "<td>$col_value</td>";
			}
			echo "</tr>\n";
		}
		break;
		
		case "vendlog":
		if (!$GET_sort == "zeny") { $orderby == "ORDER BY $GET_sort DESC"; } else { $orderby=""; }
			$query = "SELECT vend_id, vend_date, vend_account_id, seller.userid, vend_char_id, vend_char_name, 
			buy_account_id, buyer.userid, buy_char_id,  buy_char_name, nameid, $CONFIG_db_name.item_db.$namefield, amount, refine, 
			name0.$namefield, card0, name1.$namefield, card1, name2.$namefield, card2, name3.$namefield, card3, map, zeny  
			FROM $logs.$GET_view 
			LEFT JOIN $CONFIG_db_name.login AS seller ON seller.account_id = $GET_view.vend_account_id
			LEFT JOIN $CONFIG_db_name.login AS buyer ON buyer.account_id = $GET_view.buy_account_id
			LEFT JOIN $CONFIG_db_name.item_db ON $CONFIG_db_name.item_db.id = $GET_view.nameid
			LEFT JOIN $CONFIG_db_name.item_db AS name0 ON name0.id = $GET_view.card0
			LEFT JOIN $CONFIG_db_name.item_db AS name1 ON name1.id = $GET_view.card1
			LEFT JOIN $CONFIG_db_name.item_db AS name2 ON name2.id = $GET_view.card2
			LEFT JOIN $CONFIG_db_name.item_db AS name3 ON name3.id = $GET_view.card3
			WHERE $condition
			";
			if ($GET_sort == "zeny") { $query .= "\nORDER BY $GET_sort DESC"; }
			$result = execute_query($query, "view_server_log.php", $CONFIG_results_per_page, $offset);
			echo "
		<tr class=mytitle>
			<td colspan=15>Vend Log</td>
		</tr>
		<tr class=myheader>
			<td>ID</td>
			<td>Date</td>
			<td>Seller Acc</td>
			<td>Seller Char</td>
			<td>Buyer Acc</td>
			<td>Buyer Char</td>
			<td>Item</td>
			<td>Amount</td>
			<td>Refined</td>
			<td colspan=4>Cards</td>
			<td>Map</td>
			<td><a href=\"view_server_log.php?view=$GET_view&sort=zeny\">Zeny</a></td>
		</tr>
			";
		while ($line = $result->FetchRow()) {
			echo "<tr class=mycell>";
			foreach ($line as $display_index => $col_value) {
				if (($display_index >= 2) && ($display_index <= 10)  && ($display_index % 2 == 0)) {
					continue;
				}
				elseif ($display_index == 3) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=vend_account_id&value={$line[2]}\">$col_value</a>";
				}
				elseif ($display_index == 5) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=vend_char_id&value={$line[4]}\">$col_value</a>";
				}
				elseif ($display_index == 7) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=buy_account_id&value={$line[6]}\">$col_value</a>";
				}
				elseif ($display_index == 9) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=buy_char_id&value={$line[8]}\">$col_value</a>";
				}
				elseif ($display_index == 11) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[10]}\">$col_value</a>";
				}
				elseif (($display_index > 13)  && ($display_index < 22)) {
					if ($display_index % 2 == 1) { continue; }
					else {
						$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=nameid&value={$line[$display_index  + 1]}\">$col_value</a>";
					}
				}
				elseif ($display_index == 22) {
					$col_value = "<a href=\"view_server_log.php?view=$GET_view&column=map&value={$line[$display_index]}\">$col_value</a>";
				}
				echo "<td>$col_value</td>";
			}
			echo "</tr>\n";
		}
		break;
		}
		$pages_result = execute_query($pages_query, "view_server_log.php");
		$line = $pages_result->FetchRow();
		$rows = $line[0];
		$max_pages = intval($rows / $CONFIG_results_per_page) + 1;
		echo "
		<tr class=mycell>
			<td colspan=20>
		";
		for ($i = 1; $i < $max_pages; $i++) {
			echo "<a href=\"view_server_log.php?view=$GET_view&page=$i&column=$GET_column&value=$GET_value\">$i</a>";
			if ($i % 50 == 0) {
				echo "<br>";
			}
			else {
				echo "-";
			}
		}
		echo "<a href=\"view_server_log.php?view=$GET_view&page=$i&column=$GET_column&value=$GET_value\">$i</a>
			</td>
		</tr>";
		
		echo "
	</table>
		";
	}
}
else {
	redir("index.php", "Server Logs are not supported on this server!");
}
require 'footer.inc';
?>