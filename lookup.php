<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
EchoHead(80);
echo "
	<form action=\"lookup.php\" method=\"GET\">
		<tr class=mytitle>
			<td colspan=3>Account Lookup</td>
		</tr>
		<tr class=mycell>
			<td>Search Account:</td>
			<td><input type=\"text\" class=\"myctl\" name=\"account\"></td>
			<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Search Account\"></td>
		</tr>
		<tr class=mycell>
			<td>Search Account ID:</td>
			<td><input type=\"text\" class=\"myctl\" name=\"account_id\" size=20></td>
			<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Search Account ID\"></td>
		</tr>
		<input type=\"hidden\" name=\"search_type\" value=\"account\">
	</form>
</table>
";

EchoHead(80);
echo "
	<form action=\"lookup.php\" method=\"GET\">
		<tr class=mytitle>
			<td colspan=3>Character Lookup</td>
		</tr>
		<tr class=mycell>
			<td>Search Character:</td>
			<td><input type=\"text\" class=\"myctl\" name=\"char\"></td>
			<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Search Character\"></td>
		</tr>
		<tr class=mycell>
			<td>Search Character ID:</td>
			<td><input type=\"text\" class=\"myctl\" name=\"char_id\" size=20></td>
			<td><input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Search Character ID\">
			<input type=\"hidden\" name=\"search_type\" value=\"char\">
			</td>
		</tr>
		
	</form>
</table>
";

if ($GET_search_type == "account") {
	if ($GET_action == "Search Account") {
		$search_term = $GET_account;
		$query = sprintf(SEARCH_ACCOUNT, $GET_account);
	}
	elseif ($GET_action == "Search Account ID") {
		$search_term = $GET_account_id;
		$query = sprintf(SEARCH_ACCOUNT_ID, $GET_account_id);
	}
}
elseif ($GET_search_type == "char") {
	if ($GET_action == "Search Character") {
		$search_term = $GET_char;
		$query = sprintf(SEARCH_CHAR, $GET_char);
	}
	elseif ($GET_action == "Search Character ID") {
		$search_term = $GET_char_id;
		$query = sprintf(SEARCH_CHAR_ID, $GET_char_id);
	}
}
else {
	$query = "";
}
if ($query) {
	$result = execute_query($query, "lookup.php");
	while ($line = $result->FetchRow()) {
		display_account_data($line[0], $line[1], $search_term);
	}
}
echo "</table>";
require 'footer.inc';

function display_account_data ($input_account_id, $input_account_name, $search_term) {
	$account_text = "<a href=\"account_manage.php?search=$input_account_name\">$input_account_name</a>";
	EchoHead(80);
	echo "
		<tr class=mytitle>
			<td colspan=5>Account: " . $account_text . " (Account #:$input_account_id)</td>
		</tr>
		<tr class=myheader>
			<td>Character Name</td>
			<td>Character Class</td>
			<td>Base Level</td>
			<td>Job Level</td>
			<td>Zeny</td>
		</tr>
	";
	// displays the information about the characters in an account
	$query = sprintf(DISPLAY_CHAR_DATA, $input_account_id);
	$result = execute_query($query, 'functions.php');
	if ($result->RowCount() == 0) {
		echo "
		<tr class=mycell>
			<td colspan=5>None</td>
		</tr>
	</table>
		";
		return 0;
	}
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				//changed by maldiablo
				$col_value2 = highlight_search_term($col_value, $search_term);
				echo "<td><a href='char_manage.php?search=$col_value'>$col_value2</a></td>\n";
				//end of changes
			}
			elseif ($display_index == 1) {
				$col_value = determine_class($col_value);
				echo "<td>$col_value</td>\n";
			}
			else {
				echo "<td>$col_value</td>\n";
			}
		}
		echo "</tr>\n";
	}
	echo "
	</table>
	";
}
?>