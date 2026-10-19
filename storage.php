<?php
// The Kafra storage of the account: what is in it and its zeny, read only
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$query = query_format(GET_ALL_ITEMS);
$result = execute_query_union($query, "storage.php");
$itemTable = ParseAllItems($result);
$clientItemNameTable = cached_parse_table('itemdisplayname', "./dbtranslation/idnum2itemdisplaynametable.txt", 'ParseIdNum2ItemDisplayNameTable');
$itemdesctable = cached_parse_table('itemdesc', 'dbtranslation/idnum2itemdesctable.txt', 'ParseIdNum2ItemDescTable');

$storage = GetStorageItems($clientItemNameTable, $STORED_id, $itemTable);
$items = $storage['items'];
$amount = 0;
foreach ($items as $item) {
	$amount += $item['amount'];
}

echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Storage</div>
		<div class=\"infoTiles\">
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format(count($items)) . "</div><div class=\"infoLabel\">Kinds of items</div></div>
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($amount) . "</div><div class=\"infoLabel\">Items</div></div>
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($storage['zeny']) . "</div><div class=\"infoLabel\">Zeny</div></div>
		</div>
		<div class=\"newsInfo accountNote\">Your Kafra storage as the game last saved it. Items are put in and taken out in the game, at a Kafra employee.</div>
	</div>
";

if (count($items) == 0) {
	echo "<div class=\"homeSection\"><div class=\"newsItem\">Your storage is empty.</div></div>";
}
else {
	// The same groups as the inventory
	$groups = array(
		"Item" => filterItems($items, array('heal')),
		"Gear" => filterItems($items, array('armor','weapon','bothhand','bow','armorMB','armorTB','armorTM','armorTMB','gun','ThrowWeapon')),
		"Misc" => filterItems($items, array('event','card','arrow','ammo','cannonball','CashPointItem','special')),
		"Quest" => filterItems($items, array('guest'))
	);
	echo "<div class=\"homeSection\"><table class=\"charitems storageItems\">";
	foreach ($groups as $name => $group) {
		if (count($group) == 0) {
			continue;
		}
		echo "<tr class=\"charitems\"><td>$name</td><td>";
		foreach ($group as $item) {
			echo item_tile($item, $itemdesctable);
		}
		echo "</td></tr>";
	}
	echo "</table></div>";
}
echo "</div>\n";

require 'footer.inc';
?>
