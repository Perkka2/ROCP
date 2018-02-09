<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (strlen($GET_char) > 3 && !is_numeric($GET_char)) {
	redir("index.php", "Invalid Character Number!");
}
$character_slot = $GET_char;
if (!$GET_action) {
	// Check if user is logged onto system
	if (is_online($STORED_id)) {
		redir("index.php", "You cannot edit your hairstyle while logged on. Please log off and try again.");
	}
	$query = sprintf(CHECK_SEX, $STORED_login);
	$result = execute_query($query, "hairstyle.php");
	$line = $result->FetchRow();
	if ($CONFIG_server_type == 0) {
		$current_gender = $line[0] == 0 ? "f" : "m";
	}
	else {
		$current_gender = strtolower($line[0]);
	}
	$query = sprintf(GET_HAIR_NUMBER, $character_slot, $STORED_id);
	$result = execute_query($query, "hairstyle.php");
	$line = $result->FetchRow();
	$current_hair = sprintf('%02d', $line[0]);
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function changehair () {
	var hairstyle;
	hairstyle = document.Hair.Style.value;
	document.HairStyle.src= hairstyle;
}
function na_call (str) {
  eval(str);
}

// -->
</SCRIPT>
<?php
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>Hairstyle Changer</td>
	</tr>
	<tr class=mycell>
		<td>
			Note: This is only for hair style, not hair colour. Colour can be changed through in-game NPC.
		</td>
	</tr>
	<tr>
		<td height=10></td>
	</tr>
	<tr class=mycell>
		<form name=\"Hair\" method=\"GET\">
		<td>
			<select name=\"Style\" class=\"myctl\" size=\"1\" onchange=\"na_call('changehair()');\">
	";
	$max = 20;
	for ($b = 1; $b < $max; $b++) {
		$load_image = $b - 1;
		if ($b < 10) {
			$b = "0$b";
		}
		if ($b == $current_hair) {
			echo "<option value=\"hair/" . $current_gender . $b . ".bmp\" selected>Style $b</option>";
		}
		else {
			echo "<option value=\"hair/" . $current_gender . $b . ".bmp\">Style $b</option>";
		}
		echo "\n";
		if ($current_gender == "M" AND $b == 18) {
			break;
		}
	}
	echo "		</select>
		</td>
	</tr>
	<tr class=mycell>
		<td><img name=\"HairStyle\" src=\"hair/" . $current_gender . $current_hair . ".bmp\"></td>
	</tr>
	<tr class=mycell>
		<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Change Hairstyle\"></td>
	</tr>
	<input type=\"hidden\" class=\"myctl\" name=\"char\" value=\"$GET_char\">
	</form>
</table>
	";
	}
else {
	$filename = basename($GET_Style, ".bmp");
	$new_hair_int = substr($filename, 1, 2);
	$query = sprintf(UPDATE_HAIR, $new_hair_int, $character_slot, $STORED_id);
	$result = execute_query($query, "hairstyle.php");
	if ($link->Affected_Rows() > 0) {
		redir("index.php", "Hair Style Change successful!");
	}
	else {
		redir("index.php", "Something went wrong with the hairstyle change!");
	}
}
require 'footer.inc';
?>