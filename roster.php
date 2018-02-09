<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (is_online($STORED_id)) {
	redir("login.php","You can't modify your roster while you're logged in the game!");
}
if (!$POST_action) {
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=6>Character Roster</td>
	</tr>
	<tr class=myheader>
		<td>Slot</td>
		<td>Name</td>
		<td>Class</td>
		<td>Base Level</td>
		<td>Job Level</td>
		<td>Zeny</td>
	</tr>
	";
	
	$query = sprintf(GET_ROSTER_CHARS, $STORED_id);
	$result = execute_query($query, "roster.php");
	if ($result->RowCount() == 0) {
		redir("index.php", "You must have at least 1 character to use the roster!");
	}
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		foreach ($line as $display_index => $col_value) {
			if ($display_index == 0) {
				$char_slot = $col_value;
				if ($col_value > 10) {
					$col_value -= 10;
				}
			}
			elseif ($display_index == 2) {
				$col_value = determine_class($col_value);
			}
			if ($char_slot >= 0 && $char_slot <= 2) {
				echo "<td><b>$col_value</b></td>\n";
			}

			else {
				echo "<td>$col_value</td>\n";
			}
		}
		echo "</tr>\n";
	}

	echo "</table>\n";
	
	EchoHead(80);
	echo "
		<tr class=mytitle>
			<td>What would you like to do?</td>
		</tr>
		<tr class=mycell>
			<td>
				<form action=\"\" method=\"POST\">
					Switch Slot 
					<input type=\"text\" name=\"slot1\" class=myctl maxlength=1 style=\"width:10px\"></input>
					with slot
					<input type=\"text\" name=\"slot2\" class=myctl maxlength=1 style=\"width:10px\"></input>
		</select>
				<input type=\"submit\" name=\"action\" class=myctl value=\"Swap!\">
			</td>
		</tr>
	</table>
	";
}

elseif ($POST_action == "Swap!") {
	if ((!$POST_slot1) || (!$POST_slot2)) {
		redir("roster.php", "Both character slots not entered");
	}
	elseif ($POST_slot1 == $POST_slot2) {
		redir("roster.php", "Both character slots are the same");
	}
	elseif (!is_numeric($POST_slot1) || !is_numeric($POST_slot2)) {
		add_exploit_entry("Used $POST_slot1 & $POST_slot2 as illegal input!");
		redir("roster.php", "Invalid character slots selected");
	}
	elseif (($POST_slot1 > $CONFIG_max_characters) || ($POST_slot2 > $CONFIG_max_characters)) {
		add_exploit_entry("Used $POST_slot1 & $POST_slot2 as illegal input!");
		redir("roster.php", "Invalid character slots selected");
	}
	//Check for GID of first char
	$query = sprintf(GET_ROSTER_GID, $POST_slot1, $STORED_id);
	$result = execute_query($query, "roster.php");
	if ($result->RowCount() > 0) {
		$GID1 = $result->fields[0];
	}
	else {
		$GID1 = 0;
	}
	//Check for GID of second char
	$query = sprintf(GET_ROSTER_GID, $POST_slot2, $STORED_id);
	$result = execute_query($query, "roster.php");
	if ($result->RowCount() > 0) {
		$GID2 = $result->fields[0];
	}
	else {
		$GID2 = 0;
	}
	
	if ($GID1 == 0 AND $GID2 == 0) {
		// Swapping two empty slots
		redir("roster.php", "You can't swap two empty slots!");
	}
	elseif ($GID1 > 0 AND $GID2 == 0) {
		// Swapping active character to blank spot
		$query = sprintf(MOVE_ROSTER, $POST_slot2, $GID1, $STORED_id);
		$result = execute_query($query, "roster.php");
		add_user_entry("Switched Slot $POST_slot1 with $POST_slot2");
		redir("roster.php", "Character moved from active to reserve roster successfully");
	}
	elseif ($GID1 == 0 AND $GID2 > 0) {
		// Swapping inactive character to blank spot
		$query = sprintf(MOVE_ROSTER, $POST_slot1, $GID2, $STORED_id);
		$result = execute_query($query, "roster.php");
		add_user_entry("Switched Slot $POST_slot1 with $POST_slot2");
		redir("roster.php", "Character moved from reserve to active roster successfully");
	}
	elseif ($GID1 > 0 AND $GID2 > 0) {
		// Swapping active character with another active character
		$query = sprintf(MOVE_ROSTER, $POST_slot2, $GID1, $STORED_id);
		$result = execute_query($query, "roster.php");
		$query = sprintf(MOVE_ROSTER, $POST_slot1, $GID2, $STORED_id);
		$result = execute_query($query, "roster.php");
		add_user_entry("Switched Slot $POST_slot1 with $POST_slot2");
		redir("roster.php", "Characters swapped between active and reserve successfully");
	}
	else {
		add_exploit_entry("Used $POST_slot1 & $POST_slot2 as illegal input!");
		redir("roster.php", "Invalid character slots selected");
	}
}
require 'footer.inc';
?>