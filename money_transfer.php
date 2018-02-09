<?php
// Credits to Nucleo, most of the code is his
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$transfer_account_id = $STORED_id;

// Series of checks to make sure all conditions are met.

// Check that server is online
if (!is_server_online()) {
	redir("index.php", "You cannot transfer money when server is offline!");
}
// Check that character is offline
elseif (is_online($transfer_account_id)) {
	redir("index.php" ,"You must be logged off in-game to use this feature.");
}

if (!$POST_step) { $POST_step = "1"; }
if ($POST_step == "1") {
	$query = sprintf(MONEY_GET_FIRST, $transfer_account_id);
	$result = execute_query($query, "money_transfer.php");
	if ($result->RowCount() < 2) {
		redir("index.php", "You must have more than one character at least level 20 to transfer money");
	}
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=6>Money Transfer</td>
	</tr>
	<tr class=myheader>
		<td colspan=6>Select the character you would like to take money from.</td>
	</tr>
	<tr class=myheader>
		<td>Slot</td>
		<td>Name</td>
		<td>Class</td>
		<td>Level</td>
		<td>Zeny</td>
		<td>Select</td>
	</tr>
	";
	
	while ($line = $result->FetchRow()) {
		$GID = md5($line[0] . $CONFIG_passphrase);
		$slot = $line[1];
		$charname = $line[2];
		$class = determine_class($line[3]);
		$clevel = $line[4];
		$joblevel = $line[5];
		$money = $line[6];
		
		echo "    
	<tr class=mycell>
		<td>$slot</td>
		<td>$charname</td>
		<td>$class</td>
		<td>$clevel/$joblevel</td>
		<td>$money</td>
		<td>
			<form action=\"money_transfer.php\" method=\"POST\">
				<input type=\"submit\" value=\"Select\" class=\"myctl\">
				<input type=\"hidden\" name=\"charnum\" value=\"$slot\">
				<input type=\"hidden\" name=\"action\" value=\"money\">
				<input type=\"hidden\" name=\"step\" value=\"2\">
				<input type=\"hidden\" name=\"GID1\" value=\"$GID\">
			</form>
		</td>
	</tr>
		";
		
	}
	echo "</table>";
}
elseif ($POST_step == "2") {
	$query = sprintf(MONEY_GET_SECOND, $transfer_account_id, $CONFIG_passphrase, $POST_GID1);
	$result = execute_query($query, "money_transfer.php");
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=6>Money Transfer</td>
	</tr>
	<tr class=myheader>
		<td colspan=6>Select the character you would like to send money to.</td>
	</tr>
	<tr class=myheader>
		<td>Slot</td>
		<td>Name</td>
		<td>Class</td>
		<td>Level</td>
		<td>Zeny</td>
		<td>Select</td>
	</tr>
	";
	while ($line = $result->FetchRow()) {
		$GID = md5($line[0] . $CONFIG_passphrase);
		$slot = $line[1];
		$charname = $line[2];
		$class = determine_class($line[3]);
		$clevel = $line[4];
		$joblevel = $line[5];
		$money = $line[6];
		
		if ($GID != $POST_GID1) {
			echo "
		<tr class=mycell>
			<td>$slot</td>
			<td>$charname</td>
			<td>$class</td>
			<td>$clevel/$joblevel</td>
			<td>$money</td>
			<td>
				<form action=\"money_transfer.php\" method=\"POST\">
					<input type=\"hidden\" name=\"action\" value=\"money\">
					<input type=\"hidden\" name=\"step\" value=\"3\">
					<input type=\"hidden\" name=\"GID1\" value=\"$POST_GID1\">
					<input type=\"hidden\" name=\"GID2\" value=\"$GID\">
					<input type=\"submit\" value=\"Select\" class=\"myctl\">
				</form>
			</td>
		</tr>
		
			";
		}
	}
	echo "</table>";
}
elseif ($POST_step == "3") {
	$query = sprintf(GET_TRANSFER_INFO, $CONFIG_passphrase, $POST_GID1);
	$result = execute_query($query, "money_transfer.php");
	$char1 = $result->fields[0];
	$money1 = $result->fields[1];
	$query = sprintf(GET_TRANSFER_INFO, $CONFIG_passphrase, $POST_GID2);
	$result = execute_query($query, "money_transfer.php");
	$char2 = $result->fields[0];
	$money2 = $result->fields[1];
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td>Money Transfer</td>
	</tr>
	<tr class=myheader>
		<td>Please enter the the amount of money you wish to transfer.</td>
	</tr>
	<tr class=mycell>
		<td>You are transferring money from <b>$char1</b> to <b>$char2</b></td>
	</tr>
	<tr class=mycell>
		<td>There is $money1 zeny on <b>$char1</b>, and $money2 zeny on <b>$char2</b></td>
	</tr>
	<tr class=mycell>
		<form action=\"money_transfer.php\" method=\"POST\">
			<td>
				<input type=\"hidden\" name=\"action\" value=\"money\">
				<input type=\"hidden\" name=\"step\" value=\"4\">
				<input type=\"hidden\" name=\"GID1\" value=\"$POST_GID1\">
				<input type=\"hidden\" name=\"GID2\" value=\"$POST_GID2\">
				<input type=\"text\" name=\"amount\" value=\"0\" class=\"myctl\" size=\"10\">
				<input type=\"submit\" value=\"Send Money\" class=\"myctl\">
			</td>
		</form>
	</tr>
	";
}
elseif ($POST_step == "4") {
	// Get info about character from
	$query = sprintf(CHECK_TRANSFER_INFO, $CONFIG_passphrase, $POST_GID1);
	$result = execute_query($query, "money_transfer.php");
	$acc1 = $result->fields[0];
	$char1 = $result->fields[1];
	$level1 = $result->fields[2];
	$money1 = $result->fields[3];
	
	// Get info about character to
	$query = sprintf(CHECK_TRANSFER_INFO, $CONFIG_passphrase, $POST_GID2);
	$result = execute_query($query, "money_transfer.php");
	$acc2 = $result->fields[0];
	$char2 = $result->fields[1];
	$level2 = $result->fields[2];
	$money2 = $result->fields[3];
	
	if ($acc1 != $acc2) {
		// User has tried to steal from an account that isn't theirs!
		add_exploit_entry("Tried to steal zeny from another account!");
		redir("money_transfer.php","Cannot trade with a character that is not yours!");
	}
	elseif ($char1 == "" or $char2 == "") {
		add_exploit_entry("Tried to transfer with non-existant character");
		redir("money_transfer.php", "Character(s) involved in the transaction do not exist! Please try again.");
	}
	elseif (CharName_To_CharID($char1) == 0 OR CharName_To_CharID($char2) == 0) {
		add_exploit_entry("Tried to transfer with non-existent character");
		redir("money_transfer.php", "Character(s) involved in the transaction do not exist! Please try again.");
	}
	elseif ($POST_GID1 == $POST_GID2) {
		add_exploit_entry("Tried to transfer between the same character!");
		redir("money_transfer.php", "Cannot trade money from one character to themselves! Please try again.");
	}
	else {
		if ($POST_amount > $money1) {
			add_exploit_entry("Tried to transfer more than they owned! ($POST_amount, only having $money1)");
			redir("money_transfer.php", "You do not have enough money on $char1!");
		}
		elseif ($level1 < $CONFIG_minimum_transfer) {
			redir("money_transfer.php", "$char1 must be level $CONFIG_minimum_transfer or above to transfer zeny!");
		}
		elseif ($level2 < $CONFIG_minimum_transfer) {
			redir("money_transfer.php", "$char2 must be level $CONFIG_minimum_transfer or above to transfer zeny!");
		}
		else {
			if (strlen($POST_GID1) != 32 OR strlen($POST_GID2) != 32) {
				add_exploit_entry("Possible SQL injection attempt in money_transfer.php ($POST_GID1 & $POST_GID2)");
				redir("money_transfer.php", "Invalid character ID(s)!");
			}
			if ($POST_amount >= 1) {
				if (strlen($POST_amount) > 8) {
					add_exploit_entry("Possible SQL injection attempt in money_transfer.php ($POST_amount)");
					redir("money_transfer.php", "Invalid transfer amount!");
				}
				$query = sprintf(FINAL_TRANSFER, "-", $POST_amount, $CONFIG_passphrase, $POST_GID1, $money1);
				$result = execute_query($query, "money_transfer.php");
				$query = sprintf(FINAL_TRANSFER, "+", $POST_amount, $CONFIG_passphrase, $POST_GID2, $money2);
				$result = execute_query($query, "money_transfer.php");
				add_money_entry($POST_GID1, $POST_GID2, "Transferred $POST_amount zeny");
				redir("index.php", "$POST_amount zeny has been transfered from $char1 to $char2");
			}
			else {
				add_exploit_entry("Tried to transfer less than 1 zeny! ($POST_amount)");
				redir("money_transfer.php", "You can't transfer $POST_amount zeny!");
			}
		}
	}
}

require 'footer.inc';

function MoneyOnChar($input_char_id) {
	global $CONFIG_passphrase;
	$query = sprintf(MONEY_ON_CHAR, $CONFIG_passphrase, $input_char_id);
	$result = execute_query($query, "money_transfer.php");
	return $result->fields[0];
}

function LevelOfChar($input_char_id) {
	global $CONFIG_passphrase;
	$query = sprintf(LEVEL_OF_CHAR, $CONFIG_passphrase, $input_char_id);
	$result = execute_query($query, "money_transfer.php");
	return $result->fields[0];
}
?>