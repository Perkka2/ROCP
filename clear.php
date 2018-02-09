<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// Credits to Nucleo
set_time_limit(0);
if ($GET_action == "purge" or $GET_action == "purgeignore") {
	$purged = 0;
	if ($GET_action == "purge") {
		$query = "
		SELECT account_id
		FROM `login`
		WHERE lastlogin <= DATE_SUB(NOW(), INTERVAL $CONFIG_inactive_days DAY) AND lastlogin < 5
		AND account_id > 20
		";
	}
	elseif ($GET_action == "purgeignore") {
		$query = "
		SELECT account_id
		FROM `login`
		WHERE lastlogin <= DATE_SUB(NOW(), INTERVAL $CONFIG_inactive_days DAY)
		AND account_id > 20
		";
	}
	$result = execute_query($query, "clear.php");
	while ($line = $result->FetchRow()) {
		clear_account($line[0], true);
		$purged++;
	}
	if ($purged > 1) {
		add_admin_entry("Purged $purged Accounts!");
	}
	redir("clear.php", "Purged $purged Accounts!");
}
elseif ($GET_action == "unlinked") {
	$unlinked = 0;
}
	// Possible query using joins:
	/*
	$query = "SELECT char.char_id
	LEFT JOIN `login` ON login.account_id = char.account_id
	WHERE login.account_id IS NULL
	";
	*/
	/*
	$query = "SELECT account_id, char_id FROM `char`";
	$result = execute_query($query, "clear.php");
	while ($line = $result->FetchRow()) {
		$query2 = "SELECT account_id FROM `login` WHERE account_id = '{$line[0]}'";
		$result2 = execute_query($query2, "clear.php");
		if (mysql_num_rows($result2) == 0) {
			clear_character($line[1], true);
			echo "Removed Character {$line[1]}!<br>";
			$unlinked++;
		}
	}
	if ($unlinked > 1) {
		add_admin_entry("Removed $unlinked Unlinked Characters");
	}
	redir("clear.php", "Removed $unlinked Unlinked Characters!");
}
elseif ($GET_action == "novices") {
	$query = "DELETE FROM `char` WHERE class = 0 AND base_level = 0 AND job_level = 0";
	$result = execute_query($query, "clear.php");
	$novices = $link->Affected_Rows();
	add_admin_entry("Removed $novices 1/1 Novices");
	redir("clear.php", "Removed $novices 1/1 Novices!");
}
elseif ($GET_action == "nologin") {
	$query = "DELETE FROM `login` WHERE logincount = 0";
	$result = execute_query($query, "clear.php");
	$nologin = $link->Affected_Rows();
	add_admin_entry("Removed $nologin Accounts with 0 logincount");
	redir("clear.php", "Removed $nologin Accounts with 0 logincount!");
}
elseif ($GET_action == "nochar") {
	$query = "SELECT account_id FROM `login` WHERE account_id > 20";
	$result = execute_query($query, "clear.php");
	while ($line = $result->FetchRow()) {
		$query2 = "SELECT char_id FROM `char` WHERE account_id = {$line[0]}";
		$result2 = execute_query($query2, "clear.php");
		if (mysql_num_rows($result2) == 0) {
			// Account has no characters
			$query3 = "SELECT item_id FROM `storage` WHERE account_id = {$line[0]}";
			$result3 = execute_query($query3, "clear.php");
			if (mysql_num_rows($result3) == 0) {
				// Account's storage is empty, clear the account
				clear_account($line[0], true);
				$cleared++;
			}
		}
	}
	if ($cleared > 1) {
		add_admin_entry("Removed $cleared Accounts with 0 chars and empty storage");
	}
	redir("clear.php", "Removed $cleared Accounts with 0 chars and empty storage!");
}
elseif ($GET_action == "nochar_ignorestorage") {
	$query = "SELECT account_id FROM `login`";
	$result = execute_query($query, "clear.php");
	while ($line = $result->FetchRow()) {
		$query2 = "SELECT char_id FROM `char` WHERE account_id = {$line[0]}";
		$result2 = execute_query($query);
		if (mysql_num_rows($result2) == 0) {
			// Account has no characters, clear the account
			clear_account($line[0], true);
			$cleared++;
		}
	}
	if ($cleared > 1) {
		add_admin_entry("Removed $cleared Accounts with 0 chars");
	}
	redir("clear.php", "Removed $cleared Accounts with 0 chars!");
}*/

if ($CONFIG_server_type > 0 && $CONFIG_inactive_days > 0) {
	// Selects all users, besides GMs, and selects their last login time
	$query = sprintf(SHOW_INACTIVE_ACCOUNTS, $CONFIG_inactive_days);
	$result = execute_query($query, "clear.php");
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=4>
			Inactive Accounts (Have not logged in $CONFIG_inactive_days days!) - " . $result->RowCount() . " Accounts
		</td>
	</tr>
	<tr class=myheader>
		<td>Account</td>
		<td>Last Login</td>
		<td>Login Count</td>
		<td>Delete?</td>
	</tr>
	";

	if ($result->RowCount() == 0) {
		echo "
	<tr class=mycell>
		<td colspan=4>None</td>
	</tr>
		";
	}

	while ($line = $result->FetchRow()) {
		$list_account = $line[0];
		$list_account_name = $line[1];
		$last_login = $line[2];
		$login_count = intval($line[3]);
		$last_date = substr($line[2], 0, 10);
		$last_date = $line[2];
		$difference = strtotime("now") - strtotime($last_date);
		// Time duration to display
		$days = floor($difference / 86400);
		$hours = floor(($difference - ($days * 86400)) / 3600);
		$minutes = floor(($difference - ($days * 86400) - ($hours * 3600)) / 60);
		$seconds = floor(($difference - ($days * 86400) - ($hours * 3600) - ($minutes * 60)));
		if ($last_date == 0) {
			// Displays 0's if the account has never been logged in.
			$days = 0;
			$hours = 0;
			$minutes = 0;
			$seconds = 0;
		}
		echo "
	<tr class=mycell>
		<td><a href=\"account_manage.php?account=$list_account\">$list_account_name</td>
		<td>$days day(s), $hours hour(s), $minutes minute(s), $seconds second(s)</td>
		<td>$login_count</td>
		<td><a href=\"account_manage.php?option=deleteaccount&account_id=$list_account\">Delete</a></td>
	</tr>
		";
	}
	echo "
	</table>
	";
}

$query = SHOW_ILLEGAL_CHARS;
$result = execute_query($query, "clear.php");
EchoHead(80);
echo "
<tr class=mytitle>
	<td colspan=4>Illegal ASCII Character Names - " . $result->RowCount() . " Characters
	</td>
</tr>
<tr class=myheader>
	<td colspan=4>(Non-Alphanumeric)</td>
</tr>
<tr class=myheader>
	<td>Account</td>
	<td>Character Name</td>
	<td>Delete?</td>
</tr>
";
if ($result->RowCount() == 0) {
	echo "
<tr class=mycell>
	<td colspan=4>None</td>
</tr>
	";
}
while ($line = $result->FetchRow()) {
	echo "
<tr class=mycell>
	";
	$list_char = $line[1];
	foreach ($line as $display_index => $col_value) {
		if ($display_index == 0) {
			$col_value = $line[3];
		}
		elseif ($display_index == 1 or $display_index == 3) {
			continue;
		}
		$col_value = htmlspecialchars($col_value);
		echo "<td>$col_value</xmp>";
	}
	echo "
	<td>
		<a href=\"char_manage.php?option=deletechar&char_id=$list_char\">Delete</a>
	</td>
</tr>
	";
}
echo "
</table>
";
/*
echo "
<b>
<br>
<a href=\"clear.php?action=purge\">Purge all accounts that havn't been logged in over $CONFIG_inactive_days days and logged in less than 5 times</a><br>
<a href=\"clear.php?action=purgeignore\">Purge all accounts that havn't been logged in over $CONFIG_inactive_days days</a><br>
<a href=\"clear.php?action=unlinked\">Delete all characters unlinked to an account</a><br>
<a href=\"clear.php?action=novices\">Delete all 1/1 Novices</a><br>
<a href=\"clear.php?action=nologin\">Delete all Logins with 0 Logincount</a><br>
<a href=\"clear.php?action=nochar\">Delete all Logins with 0 Characters and Empty Storage</a><br>
<a href=\"clear.php?action=nochar_ignorestorage\">Delete all Logins with 0 Characters</a>
</b>";*/
require 'footer.inc';
?>
