<?php
require 'memory.php';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// determines what page to visit if nothing is being searched
if (!$GET_page) {
	$page = 1;
}
else {
	$page = $GET_page;
}
$size = $CONFIG_results_per_page;
$start = ($page * $size) - $size;
// Searches for character if requested
if ($GET_search != "") {
	require 'header.inc';
	$search_account = $GET_search;
        $query = sprintf(ACCOUNT_SEARCH, $search_account, $search_account, $search_account, $search_account);
	display_account_table($query, $start, $size);
	$query = ACCOUNT_SHOW_LIST;
	display_account_table($query, $start, $size);
	$display_table = true;
}
elseif ($GET_option == "deleteaccount" && $GET_account_id > 0) {
	require 'header.inc';
	$name_of_account = AccountID_To_UserID($GET_account_id);
	echo "Are you sure you want to delete $name_of_account?<p>";
	echo "
	<form action=\"account_manage.php\" method=\"GET\">
	<input type=\"hidden\" name=\"delete_id\" class=\"myctl\" value=\"$GET_account_id\">
	<input type=\"submit\" name=\"delete\" class=\"myctl\" value=\"Delete\">
	</form>
	";
}
elseif ($POST_finishedit == "Edit This Account!") {
	require 'header.inc';
	
	// Compare every value with the database value
	$edit_variables = array("Account ID", "Account Name", "Password", "Gender", "Email", "Status");
	$query = sprintf(ACCOUNT_SHOW_EDIT, $POST_var[0]);
	$result = execute_query($query, "account_manage.php");
	$line = $result->FetchRow();
	foreach ($line as $index => $col_value) {
		// Skip the last column obtained from SQL
		if ($index == 6) {
			continue;
		}
		
		// Check password
		if ($index == 2) {
			// Check if stored password and password in form match
			if ($col_value == $POST_var[$index]) {
				// Password is the same
				$new_pass = $POST_var[2];
			}
			else {
				// Password has been changed
				
				if ($CONFIG_use_md5) {
					$new_pass = md5($POST_var[2]);
				}
				else {
					$new_pass = $POST_var[2];
				}
			}
			continue;
		}
		
		if ($col_value != $POST_var[$index]) {
			// Check for password
			if ($index == 3 && $CONFIG_server_type == 0) {
				$original_sex = $col_value == 0 ? "F" : "M";
				$log_message = "Changed {$edit_variables[$index]} of {$line[1]}: $original_sex to {$POST_var[$index]}";
			}
			else {
				$log_message = "Changed {$edit_variables[$index]} of {$line[1]}: $col_value to {$POST_var[$index]}";
			}
			add_admin_entry($log_message);
		}
	}
	
	if ($CONFIG_server_type == 0) {
		$sex = $POST_var[3] == "F"? 0 : 1;
		$query = sprintf(UPDATE_ACCOUNT, $POST_var[1], $new_pass,
		$POST_var[5], $POST_var[0]);
		$result = execute_query($query, "account_manage.php");
		
		$query = sprintf(UPDATE_ACCOUNT2, $POST_var[1], $sex, 
		$POST_var[4], $POST_var[0]);
		$result = execute_query($query, "account_manage.php");
	}
	else {
		$query = sprintf(UPDATE_ACCOUNT, $POST_var[1], $new_pass,
		$POST_var[3], $POST_var[4], $POST_var[5], $POST_var[0]);
		$result = execute_query($query, "account_manage.php");
	}
	redir("account_manage.php", "Account Updated! Bringing you to Account Management");
}
elseif ($GET_delete == "Delete") {
	require 'header.inc';
	$account_name = AccountID_To_UserID($GET_delete_id);
	clear_account($GET_delete_id);
	add_admin_entry("Deleted Account $account_name");
	redir("account_manage.php", "Deleted Account $account_name");
}
elseif ($GET_option == "editaccount") {
	require 'header.inc';
        $query = sprintf(ACCOUNT_SHOW_EDIT, $GET_account_id);
	display_edit_table($query);
}
elseif ($GET_option == "addignore") {
	header("Location: ladder_ignore.php?add=$GET_account_id");
}
elseif ($GET_option == "delignore") {
	header("Location: ladder_ignore.php?del=$GET_account_id");
}
elseif ($GET_option == "ban") {
	$id = AccountID_To_UserID($GET_account_id);
	header("Location: ban.php?action=Ban Account&account_name=$id");
}
elseif ($GET_option == "unban") {
	$id = AccountID_To_UserID($GET_account_id);
	header("Location: unban.php?action=Unban Account&account_name=$id");
}
elseif ($GET_option == "itemsearch") {
	require 'header.inc';
	require 'item_functions.php';
	$query = sprintf(DISPLAY_ACCOUNT_ITEMS, $GET_account_id);
	$result = execute_query($query, 'functions.php');
	while ($line = $result->FetchRow()) {
		$char_id = $line[0];
		if ($char_id > 0) {
			$source_id_type = $CONFIG_server_type == 0? "GID" : "char_id";
			display_source_id_items($source_id_type, $char_id);
		}
	}
	$source_id_type = $CONFIG_server_type == 0? "AID" : "account_id";
	display_source_id_items($source_id_type, $GET_account_id);
}
else {
	require 'header.inc';
        $query = ACCOUNT_SHOW_LIST;
	display_account_table($query, $start, $size);
	$display_table = true;
}

// Gets # of characters on server
$number_of_accounts = GetAccountCount();
$max_pages = intval($number_of_accounts / $CONFIG_results_per_page) + 1;
if ($display_table) {
	for ($i = 1; $i < $max_pages; $i++) {
		echo "<a href=\"account_manage.php?page=$i&search=$GET_search\">$i</a>";
		if ($i % 50 == 0) {
			echo "<br>";
		}
		else {
			echo "-";
		}
	}
	echo "<a href=\"account_manage.php?page=$i&search=$GET_search\">$i</a>
	<form action=\"account_manage.php\" method=\"GET\">
		<table border=0 align=\"center\">
			<tr>
				<td class=\"mytext\" align=\"left\">Search: </td>
				<td><input type=\"text\" name=\"search\" class=\"myctl\"></td>
				<td align=\"left\"><input type=\"submit\" class=\"myctl\" value=\"Search\"></td>
			</tr>
		</table>
	</form>
	";
}
require 'footer.inc';

function display_account_table($input_query, $start, $size) {
	global $CONFIG_server_type, $CONFIG_admin_colour, $CONFIG_gm_colour, $CONFIG_game_gm_colour,
	$STORED_level;
	$result = execute_query($input_query, "account_manage.php", $size, $start);
	if ($result->RowCount() == 0) {
		echo "No Account Matching was found!";
		return 0;
	}
        if ($CONFIG_server_type == 0) {
                $ban_value = 1;
        }
        else {
                $ban_value = -1;
        }
	EchoHead(100);
	echo "
	<tr class=mytitle>
		<td colspan=7>Account Table</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Account ID</td>
		<td>Account Name</td>
		<td>Password</td>
		<td>Gender</td>
		<td>Email</td>
		<td>Status</td>
	</tr>
	";
	while ($line = $result->FetchRow()) {
		$account_id = $line[0];
		$access_type = $line[6];
		$bold_start = "";
		$bold_end = "";
		if ($CONFIG_server_type == 0) {
			if ($line[8] == 1) {
				$bold_start = "<b>";
				$bold_end = "</b>";
			}
		}
		echo "
	<tr class=mycell>
		";
		
		echo "
		<td>
		<form action=\"account_manage.php\" method=\"GET\">
			<select class=\"myctl\" name=\"option\">
				<option value=editaccount>Edit
				<option value=deleteaccount>Delete
				<option value=itemsearch>Items
		";
		if ($line[7] > 0) {
			echo "<option value=delignore>Show on Ladder";
		}
		else {
			echo "<option value=addignore>Ignore on Ladder";
		}
		if ($line[5] == $ban_value) {
			echo "<option value=unban>Unban";
		}
		else {
			echo "<option value=ban>Ban";
		}
		echo "
			</select>
			<input type=\"submit\" value=\"Go\" class=\"myctl\">
			<input type=\"hidden\" name=\"account_id\" value=\"{$line[0]}\">
		</form>
		</td>
		";
		foreach ($line as $display_index => $col_value) {
                        if ($_GET['search']) {
                                $col_value = highlight_search_term($col_value, $_GET['search']);
                        }
			if ($display_index == 3 && $CONFIG_server_type == 0) {
				$col_value = $col_value == 1? "M" : "F";
			}
			elseif ($display_index == 5) {
				switch ($col_value) {
					case $ban_value:
						$col_value = '<font color=red>Banned</font>';
						break;
					default:
						$col_value = 'Active';
						break;
				}
			}
			elseif ($display_index == 6) {
				break;
			}
			if ($access_type > 1) {
				if ($display_index == 2) {
					if ($STORED_level == 3) {
						// Censors the password
						$col_value = '***********';
					}
				}
				if ($access_type == 4) {
					$col_value = "<font color=#$CONFIG_admin_colour>$col_value</font>";
				}
				elseif ($access_type == 3) {
					$col_value = "<font color=#$CONFIG_gm_colour>$col_value</font>";
				}
				elseif ($access_type == 2) {
					$col_value = "<font color=#$CONFIG_game_gm_colour>$col_value</font>";
				}
			}
			echo "<td>$bold_start$col_value$bold_end</td>";
		}
		echo "
	</tr>
		";
	}
	echo "
</table>
	";
}

function display_edit_table($input_query) {
	global $CONFIG_server_type, $STORED_level, $STORED_id;
	$result = execute_query($input_query, "account_manage.php");
        if ($CONFIG_server_type == 0) {
                $ban_value = 1;
                $unban_value = 3;
        }
        else {
                $ban_value = -1;
                $unban_value = 0;
        }
	if ($result->NumRows() == 0) {
		echo "No Account Matching was found!";
		return 0;
	}
        $line = $result->FetchRow();
	$account_id = $line[0];
	$userid = $line[1];
	$user_pass = $line[2];
	$sex = $line[3];
	$email = $line[4];
	$level = $line[5];
	if ($line[6] >= $STORED_level && $STORED_id != $account_id) {
		// Disable GM from editing admin/GM accounts
		redir("account_manage.php", "You cannot edit this account!");
	}
	$online_string = is_online($account_id)? "<font color=\"green\">Online" : "<font color=\"red\">Offline";
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td colspan=2>Editing Account: $userid<br>
		Account Status: $online_string</font>
		</td>
	</tr>
	<form action=\"account_manage.php\" method=\"POST\">
		<tr class=mycell>
			<td class=myheader>Account ID</td>
			<td>$account_id</td>
			<input type=\"hidden\" name=\"var[0]\" class=\"myctl\" value=\"$account_id\">
		</tr>
		<tr class=mycell>
			<td class=myheader>Account Name</td>
			<td><input type=\"text\" name=\"var[1]\" class=\"myctl\" value=\"$userid\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Password</td>
			<td><input type=\"text\" name=\"var[2]\" class=\"myctl\" value=\"$user_pass\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Gender:</td>
			<td>
				<select name=\"var[3]\" class=\"myctl\">
	";
	if ($sex == 1 || $sex == "M") {
		echo "
					<option value=M selected>Male
					<option value=F>Female
		";
	}
	else {
		echo "
					<option value=M>Male
					<option value=F selected>Female
		";
	}
	echo "
				</select>
			</td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Email</td>
			<td><input type=\"text\" name=\"var[4]\" class=\"myctl\" value=\"$email\"></td>
		</tr>
		<tr class=mycell>
			<td class=myheader>Status</td>
			<td>
	<select class=\"myctl\" name=\"var[5]\">
	";
	if ($level == $ban_value) {
		echo "<option value=\"$unban_value\">Active";
		echo "<option value=\"$ban_value\" selected>Banned";
	}
        else {
                echo "<option value=\"$unban_value\" selected>Active";
                echo "<option value=\"$ban_value\">Banned";
        }
	echo "
				</select>
			</td>
		</tr>
		<tr class=mycell>
			<td colspan=2>
				<input type=\"submit\" name=\"finishedit\" class=\"myctl\" value=\"Edit This Account!\">
			</td>
		</tr>
	</form>
</table>
	";
}

?>