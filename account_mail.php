<?php
// Account mail of the twRO EP17.1 servers (GlobalInfo.dbo.RoDex): RoDEX mail to an account, which every character of
// it sees in its mailbox, with zeny and an item to take. twRO's web shop and events sent these; nothing in the game does.
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$max_zeny = 1000000000;
$max_count = 30000;
$max_days = 365;
// Items that do not stack (equipment) go one per mail
$gear_tables = array('armor', 'armorMB', 'armorTB', 'armorTM', 'armorTMB', 'weapon', 'bothhand', 'bow', 'gun');

$query = query_format(GET_ALL_ITEMS);
$result = execute_query_union($query, "account_mail.php");
$itemTable = ParseAllItems($result);
$clientItemNameTable = cached_parse_table('itemdisplayname', "./dbtranslation/idnum2itemdisplaynametable.txt", 'ParseIdNum2ItemDisplayNameTable');

function mail_item_name($id) {
	global $itemTable, $clientItemNameTable;
	if (!empty($clientItemNameTable[$id])) {
		return str_replace("_", " ", $clientItemNameTable[$id]);
	}
	return isset($itemTable[$id]) ? str_replace("_", " ", $itemTable[$id]['name']) : "item $id";
}

function mail_post($name) {
	return isset($_POST[$name]) && is_string($_POST[$name]) ? str_replace("\r\n", "\n", $_POST[$name]) : "";
}

$form = array('to' => 'one', 'account' => '', 'sender' => 'GM', 'title' => '', 'text' => '', 'zeny' => '0',
	'item' => '', 'count' => '1', 'days' => '30');
if (isset($GET_account) && is_string($GET_account)) {
	$form['account'] = $GET_account;
}

if (isset($POST_action) && $POST_action == "Delete") {
	$first = (int)$POST_first;
	$last = (int)$POST_last;
	if ($first > 0 && $last >= $first) {
		execute_query(query_format(MAIL_DELETE, $first, $last), "account_mail.php");
		add_admin_entry("Account mail #$first-#$last deleted");
	}
	redir("account_mail.php", "The mail was deleted from the mailboxes it was still in.");
}

$errors = array();
if (isset($POST_action) && $POST_action == "Send Mail") {
	foreach ($form as $field => $default) {
		$form[$field] = mail_post($field);
	}
	$form['account'] = trim($form['account']);
	$form['sender'] = trim($form['sender']);
	$form['title'] = trim($form['title']);
	$aid = 0;
	if ($form['to'] != 'all') {
		$form['to'] = 'one';
		$aid = $form['account'] == "" ? 0 : UserID_To_AccountID($form['account']);
		if ($aid == 0) {
			$errors[] = "There is no account named " . html_escape($form['account']) . ".";
		}
	}
	elseif (mail_post('confirm') != "1") {
		$errors[] = "Tick the box to confirm sending it to every account.";
	}
	if ($form['sender'] == "" || strlen($form['sender']) > 24) {
		$errors[] = "The sender needs a name of 1 to 24 characters.";
	}
	if ($form['title'] == "" || strlen($form['title']) > 50) {
		$errors[] = "The title needs 1 to 50 characters.";
	}
	if (strlen($form['text']) > 1000) {
		$errors[] = "The text can have at most 1,000 characters.";
	}
	$zeny = trim($form['zeny']) === "" ? 0 : trim($form['zeny']);
	if (!preg_match('/^\d{1,10}$/', $zeny) || $zeny > $max_zeny) {
		$errors[] = "Zeny must be a whole number from 0 to " . number_format($max_zeny) . ".";
	}
	$item = trim($form['item']);
	$count = trim($form['count']);
	$item_id = 0;
	if ($item !== "") {
		if (!preg_match('/^\d{1,9}$/', $item) || !isset($itemTable[(int)$item])) {
			$errors[] = "There is no item with ID " . html_escape($item) . ".";
		}
		elseif (!preg_match('/^\d{1,5}$/', $count) || $count < 1 || $count > $max_count) {
			$errors[] = "The item count must be from 1 to " . number_format($max_count) . ".";
		}
		elseif ($count > 1 && in_array($itemTable[(int)$item]['table'], $gear_tables)) {
			$errors[] = html_escape(mail_item_name((int)$item)) . " does not stack: send it one per mail.";
		}
		else {
			$item_id = (int)$item;
		}
	}
	$days = trim($form['days']);
	if (!preg_match('/^\d{1,3}$/', $days) || $days < 1 || $days > $max_days) {
		$errors[] = "The mail must stay from 1 to $max_days days.";
	}
	if (count($errors) == 0) {
		$result = execute_query(MAIL_LAST_ID, "account_mail.php");
		$last_id = (int)$result->fields[0];
		$query = query_format(MAIL_SEND, sql_text($form['sender'], 24), sql_text($form['title'], 50), sql_text($form['text'], 1000),
			$item_id ? $item_id : "NULL", $item_id ? (int)$count : "NULL", $zeny > 0 ? (int)$zeny : "NULL", (int)$days, $aid);
		execute_query($query, "account_mail.php");
		$result = execute_query(query_format(MAIL_SENT, $last_id), "account_mail.php");
		$first = (int)$result->fields[0];
		$last = (int)$result->fields[1];
		$sent = (int)$result->fields[2];
		$attached = array();
		if ($item_id) {
			$attached[] = (int)$count . " x " . mail_item_name($item_id) . " ($item_id)";
		}
		if ($zeny > 0) {
			$attached[] = number_format($zeny) . " zeny";
		}
		$to = $aid ? "account " . $form['account'] : "all $sent accounts";
		add_admin_entry("Account mail #$first-#$last to $to" . (count($attached) ? ": " . implode(", ", $attached) : ""));
		redir("account_mail.php", "The mail was sent to " . html_escape($to) . ".");
	}
}

$result = execute_query(MAIL_ACCOUNT_COUNT, "account_mail.php");
$accounts = (int)$result->fields[0];
$checked_one = $form['to'] == 'all' ? "" : " checked";
$checked_all = $form['to'] == 'all' ? " checked" : "";
echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Account Mail</div>
		<div class=\"newsInfo accountNote\">RoDEX mail to an account: every character of it sees the mail in its mailbox, and
			the first one to take the zeny or the item gets it. It shows the next time a character opens the mailbox.
			Nothing in the game sends these; on twRO the web shop and events did.</div>";
if (count($errors)) {
	echo "<div class=\"newsItem loginFailed\"><div class=\"newsMessage\">" . implode("<br>", $errors) . "</div></div>";
}
echo "
		<form action=\"account_mail.php\" method=\"POST\" class=\"accountForm\">
			<label><input type=\"radio\" name=\"to\" value=\"one\"$checked_one> To one account</label>
			<label>Account name <input type=\"text\" name=\"account\" class=\"myctl\" maxlength=\"24\" value=\"" . html_escape($form['account']) . "\"></label>
			<label><input type=\"radio\" name=\"to\" value=\"all\"$checked_all> To every account (" . number_format($accounts) . ")</label>
			<label><input type=\"checkbox\" name=\"confirm\" value=\"1\"> Yes, send it to every account</label>
			<label>Sender <input type=\"text\" name=\"sender\" class=\"myctl\" maxlength=\"24\" value=\"" . html_escape($form['sender']) . "\"></label>
			<label>Title <input type=\"text\" name=\"title\" class=\"myctl\" maxlength=\"50\" value=\"" . html_escape($form['title']) . "\"></label>
			<label>Text <textarea name=\"text\" class=\"myctl\" rows=\"6\" maxlength=\"1000\">" . html_escape($form['text']) . "</textarea></label>
			<label>Zeny <input type=\"number\" name=\"zeny\" class=\"myctl\" min=\"0\" max=\"$max_zeny\" value=\"" . html_escape($form['zeny']) . "\"></label>
			<label>Item ID <input type=\"number\" name=\"item\" class=\"myctl\" min=\"1\" value=\"" . html_escape($form['item']) . "\"></label>
			<label>Item count <input type=\"number\" name=\"count\" class=\"myctl\" min=\"1\" max=\"$max_count\" value=\"" . html_escape($form['count']) . "\"></label>
			<label>Days in the mailbox <input type=\"number\" name=\"days\" class=\"myctl\" min=\"1\" max=\"$max_days\" value=\"" . html_escape($form['days']) . "\"></label>
			<div class=\"newsInfo\">One item kind per mail (equipment one at a time); leave the item ID empty for none.
				Item IDs are in the <a href=\"view_items.php\">Item Database</a>.</div>
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Send Mail\">
		</form>
	</div>
";

$result = execute_query(MAIL_SENDS, "account_mail.php");
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Sent Mail</div>";
if ($result->RowCount() == 0) {
	echo "<div class=\"newsItem\">No account mail in any mailbox.</div>";
}
else {
	echo "<table class=\"contentTable\">
		<tr class=\"myheader\"><td>Sent</td><td>To</td><td>Title</td><td>Attached</td><td>Read</td><td>Not taken yet</td><td>Expires</td><td></td></tr>";
	while ($line = $result->FetchRow()) {
		$first = (int)$line[0];
		$last = (int)$line[1];
		$count = (int)$line[2];
		$to = $count == 1 ? html_escape($line[3]) : number_format($count) . " accounts";
		$attached = array();
		if ($line[11] > 0) {
			$attached[] = (int)$line[12] . " x " . html_escape(mail_item_name((int)$line[11]));
		}
		if ($line[13] > 0) {
			$attached[] = number_format($line[13]) . " zeny";
		}
		$untaken = array();
		if ($line[9] > 0) {
			$untaken[] = "item in " . number_format($line[9]);
		}
		if ($line[10] > 0) {
			$untaken[] = "zeny in " . number_format($line[10]);
		}
		echo "<tr class=\"mycell\">
			<td>" . html_escape(date("M j, Y H:i", strtotime($line[6]))) . "</td>
			<td>$to</td>
			<td>" . html_escape($line[5]) . "<div class=\"newsInfo\">from " . html_escape($line[4]) . "</div></td>
			<td>" . (count($attached) ? implode("<br>", $attached) : "-") . "</td>
			<td>" . number_format($line[8]) . " of " . number_format($count) . "</td>
			<td>" . (count($untaken) ? implode("<br>", $untaken) : "-") . "</td>
			<td>" . html_escape(date("M j, Y", strtotime($line[7]))) . "</td>
			<td><form action=\"account_mail.php\" method=\"POST\" onsubmit=\"return confirm('Delete this mail from every mailbox it is in? What was not taken is lost.');\">
				<input type=\"hidden\" name=\"first\" value=\"$first\">
				<input type=\"hidden\" name=\"last\" value=\"$last\">
				<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Delete\">
			</form></td>
		</tr>";
	}
	echo "</table>";
}
echo "<div class=\"newsInfo accountNote\">The 50 newest sends still in a mailbox. Attached shows what is left to take;
	a mail the player deleted, or that expired and was cleaned up, is gone from here.</div></div>
</div>
";

require 'footer.inc';
?>
