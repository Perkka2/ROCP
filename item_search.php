<?php
require 'memory.php';
require 'header.inc';
require 'item_functions.php';
check_auth($_SERVER['PHP_SELF']); // checks for required access
echo "
<table align=\"center\" border=\"0\">
	<tr class=mycell>
		<form action=\"item_manage.php\" method=\"GET\">
		<td>Search Account:</td>
		<td><input type=\"text\" class=\"myctl\" name=\"account\"></td>
		<td><input type=\"submit\" class=\"myctl\" name=\"s_action\" value=\"Search Account\"></td>\n
		</form>
	</tr>
	<tr class=mycell>
		<form action=\"item_manage.php\" method=\"GET\">
		<td>Search Character:</td>
		<td><input type=\"text\" class=\"myctl\" name=\"char\" size=20></td>
		<td><input type=\"submit\" class=\"myctl\" name=\"s_action\" value=\"Search Character\"></td>
		</form>
	</tr>";
if ($CONFIG_server_type == 0) {
	echo "<tr class=mycell><td colspan=3><font color=\"#ff0000\">Due to the new gzipped save format on aegis servers item search is no longer avaiable and will return empty results</font></td></tr>";
}
	echo	"
<tr class=mycell>
	<form action=\"item_manage.php\" method=\"GET\">
	<td>Search Item:</td>
	<td><select name=\"item\" class=\"myctl\" size=1\">
";
$query = LIST_ITEMS;
$result = execute_query($query, "item_search.php");
while ($line = $result->FetchRow()) {
	echo "
				<option value={$line[0]}>{$line[1]}</option>
	";
}

echo "
			</select>
			<br>
			<input type=\"text\" class=\"myctl\" name=\"item2\">
		</td>
		<td><input type=\"submit\" class=\"myctl\" name=\"s_action\" value=\"Search Item\">
		</form>
	</tr>
";
if ($CONFIG_server_type > 0) {
	echo "
	<tr class=mycell>
		<form action=\"item_manage.php\" method=\"GET\">
		<td>Search Refined:</td>
		<td>
			<select name=\"refine\" class=\"myctl\" size=1\">
				<option value=1 selected>+1</option>
	";
	for ($i = 2; $i <= 10; $i++) {
		echo "
				<option value=$i>+$i</option>
		";
	}
	echo "
			</select>
		</td>
		<td><input type=\"submit\" class=\"myctl\" name=\"s_action\" value=\"Search Refined\"></td>
		</form>
	</tr>
	";
}




echo "
</table>	
";
require 'footer.inc';
?>