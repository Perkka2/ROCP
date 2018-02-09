<?php
require 'memory.php';
require 'header.inc';
require 'item_functions.php';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$cp_db = $CONFIG['cp_db_name'];
if ($CONFIG_server_type == 0) {
	$search_account_id = "AID";
	$search_char_id = "GID";
}
else {
	$search_account_id = "account_id";
	$search_char_id = "char_id";
}

if ($GET_delete_inv > 0 or $GET_delete_stor > 0 or $GET_delete_cart > 0) {
	if ($GET_delete_inv > 0) {
		$delete_table = "inventory";
		$delete_value = $GET_delete_inv;
		$display_column = "char_id";
	}
	elseif ($GET_delete_stor > 0) {
		$delete_table = "storage";
		$delete_value = $GET_delete_stor;
		$display_column = "account_id";
	}
	elseif ($GET_delete_cart > 0) {
		$delete_table = "cart_inventory";
		$delete_value = $GET_delete_cart;
		$display_column = "char_id";
	}
	$query = "SELECT $delete_table.id, $display_column, nameid, amount, refine, name0.name_english, name1.name_english, name2.name_english, name3.name_english, item_db.name_english
	FROM `$delete_table`
	LEFT JOIN $cp_db.item_db ON item_db.ID = $delete_table.nameid	
	LEFT JOIN $cp_db.item_db AS name0 ON $delete_table.card0 = name0.ID
	LEFT JOIN $cp_db.item_db AS name1 ON $delete_table.card1 = name1.ID
	LEFT JOIN $cp_db.item_db AS name2 ON $delete_table.card2 = name2.ID
	LEFT JOIN $cp_db.item_db AS name3 ON $delete_table.card3 = name3.ID
	WHERE $delete_table.id = $delete_value
	";
	$result = execute_query($query, "item_manage.php");
	$line = $result->FetchRow();
	if ($display_column == "char_id" ) {
		$source = "Character: " . CharID_To_CharName($line[1]);
	}
	elseif ($display_column == "account_id") {
		$source = "Account: " . AccountID_To_UserID($line[1]);
	}
	$display = convert_equip($line[9], $line[4], $line[5], $line[6]);
	$display_msg = "Deleted {$line[3]} $display (Cards: [{$line[5]}] [{$line[6]}] [{$line[7]}] [{$line[8]}]) from $source!";
	// Make sure that character(s) are offline
	$query2 = "SELECT * FROM `char` WHERE online = 1 AND $display_column = '{$line[1]}'";
	$result2 = execute_query($query2, "item_manage.php");
	if ($result2->RowCount() > 0) {
		redir("char_manage.php", "You cannot delete this item if they are online!");
	}
	$display_msg = "Deleted {$line[3]} $display (Cards: [$card1] [$card2] [$card3] [$card4]) from $source!";
	$query = "DELETE FROM `$delete_table` WHERE id = $delete_value LIMIT 1";
	$result = execute_query($query, "item_manage.php");
	add_admin_entry(add_escape($display_msg));
	redir("char_manage.php", $display_msg);
}
if ($GET_s_action == "Search Character") {
	if ($GET_char_id > 0) {
		display_source_id_items($search_char_id, $GET_char_id);
	}
	else {
		$char_id = CharName_To_CharID($GET_char);
		display_source_id_items($search_char_id, $char_id);
	}
}
elseif ($GET_s_action == "Search Refined") {
	$refine_level = $GET_refine;
	display_item("refine = $GET_refine");
}
elseif ($GET_item > 0 || $GET_item2 > 0) {
	if ($GET_item2 > 0) {
		$GET_item = $GET_item2;
	}
	if ($CONFIG_server_type == 0) {
		display_aegis_item($GET_item);
	}
	else {
		display_item("$GET_item IN(nameid, card0, card1, card2, card3)");
	}
}
elseif ($GET_s_action == "Search Account") {
	if ($GET_account_id > 0) {
		$search_id = $GET_account_id;
	}
	else {
		$search_id = UserID_To_AccountID($GET_account);
	}
	$query = sprintf(CHARS_ON_ACCOUNT, $search_id);
	$result = execute_query($query, "item_manage.php");
	while ($line = $result->FetchRow()) {
		if ($line[0] != "") {
			display_source_id_items($search_char_id, $line[0]);
		}
	}
	display_source_id_items($search_account_id, $search_id);
}
require 'footer.inc';
?>