<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
set_time_limit(0);
$armor_tables = array("armor", "armorTM", "armorTB", "armorMB", "armorTMB");
$weapon_tables = array("weapon", "bothhand", "bow", "gun");
$ammo_tables = array("ammo", "arrow", "cannonball");
$non_equip_tables = array("heal", "special", "event");
		
if ($CONFIG_server_type > 0) {
	$query = "SELECT count(*) FROM item_db";
	$result = $link->Execute($query);
	if (!$result || $result->fields[0] == 0) {
		redir("index.php", "You do not have an item_db to rebuild from!");
	}
}
		
$query = CLEAR_ITEM_TABLE;
$link->Execute($query);

if ($CONFIG_server_type == 0) {
	// armor DBs
	foreach ($armor_tables as $table_name) {
		$query = "SELECT ID, Name, PRICE, WEIGHT, DEF, SLOT, EQUIP, SEX, LOCA, minLevel FROM script.dbo.$table_name";
		$result = execute_query($query, "rebuild_items.php");
		while ($line = $result->FetchRow()) {
			$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 0, $line[2], 
			$line[3], "NULL", $line[4], "NULL", $line[5], $line[6], $line[7], $line[8], "NULL", $line[9]);
			$link->Execute($query2);
		}
		
	}
	
	// weapon DBs
	foreach ($weapon_tables as $table_name) {
		$query = "SELECT ID, Name, PRICE, WEIGHT, ATK, AR, SLOT, EQUIP, SEX, LOCA, [level], minLevel FROM script.dbo.$table_name";
		$result = execute_query($query, "rebuild_items.php");
		while ($line = $result->FetchRow()) {
			$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 2, $line[2], 
			$line[3], $line[4], "NULL", $line[5], $line[6], $line[7], $line[8], $line[9], $line[10], $line[11]);
			$link->Execute($query2);
		}
		
	}
	
	// ammo DBs
	foreach ($ammo_tables as $table_name) {
		$query = "SELECT ID, Name, PRICE, WEIGHT, ATK, minLevel FROM script.dbo.$table_name";
		$result = execute_query($query, "rebuild_items.php");
		while ($line = $result->FetchRow()) {
			$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 1, $line[2], 
			$line[3], $line[4], "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", $line[5]);
			$link->Execute($query2);
		}
	}
	
	
	//ThrowWeapon
	$query = "SELECT ID, Name, PRICE, WEIGHT, ATK, minLevel, EQUIP FROM script.dbo.ThrowWeapon";
	$result = execute_query($query, "rebuild_items.php");
	while ($line = $result->FetchRow()) {
		$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 8, $line[2], 
		$line[3], $line[4], "NULL", "NULL", "NULL", $line[6], 2, "NULL", "NULL", $line[5]);
		$link->Execute($query2);
	}
	
	
	// card DB
	$query = "SELECT ID, Name, PRICE, WEIGHT, compositionPos FROM script.dbo.card";
	$result = execute_query($query, "rebuild_items.php");
	while ($line = $result->FetchRow()) {
		$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 3, $line[2], 
		$line[3], "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", $line[4], "NULL", "NULL");
		$link->Execute($query2);
	}
	
	
	// guest DB
	$query = "SELECT ID, Name FROM script.dbo.guest";
	$result = execute_query($query, "rebuild_items.php");
	while ($line = $result->FetchRow()) {
		$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 5, "NULL", 
		"NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL");
		$link->Execute($query2);
	}
	
	// heal, special, event DB
	foreach ($non_equip_tables as $table_name) {
		$query = "SELECT ID, Name, PRICE, WEIGHT FROM script.dbo.$table_name";
		$result = execute_query($query, "rebuild_items.php");
		while ($line = $result->FetchRow()) {
			$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), 6, $line[2], 
			$line[3], "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL");
			$link->Execute($query2);
		}	
	}

}
else {
	$query = GET_ITEM_TABLE;
	$result = execute_query($query, "rebuild_items.php");
	while ($line = $result->FetchRow()) {
		foreach ($line as $index => $value) {
			if ($line[$index] === NULL) {
				$line[$index] = "NULL";
			}
		}
		$query2 = sprintf(INSERT_ITEM_TABLE, $line[0], add_escape($line[1]), $line[2],
		$line[3], $line[4], $line[5], $line[6], $line[7], $line[8], $line[9], $line[10],
		$line[11], $line[12], $line[13]);
		$link->Execute($query2);
	}
}

redir("index.php", "Item Database Rebuilt!");
require 'footer.inc';
?>