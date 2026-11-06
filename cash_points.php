<?php
// Cash shop points of the twRO EP17.1 servers (CashItemShop.dbo.TwCash, by account name): twRO's web shop sold them,
// nothing in the game gives them. Points are what the in-game cash shop spends; bonus points (FreeCash) are kept
// next to them.
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

$max_change = 1000000000;
$search = isset($GET_search) && is_string($GET_search) ? trim($GET_search) : "";

if (isset($POST_action) && $POST_action == "Change Points") {
	$name = is_string($POST_account) ? $POST_account : "";
	$cash = is_string($POST_cash) ? trim($POST_cash) : "";
	$free = is_string($POST_free) ? trim($POST_free) : "";
	$cash = $cash === "" ? "0" : $cash;
	$free = $free === "" ? "0" : $free;
	$query = query_format(CASH_GET, $name);
	$result = execute_query($query, "cash_points.php");
	if ($result->RowCount() == 0) {
		redir("cash_points.php", "There is no account named " . html_escape($name) . ".");
	}
	$name = $result->fields[1]; // as the login table has it
	$back = "cash_points.php?search=" . urlencode($name);
	if (!preg_match('/^[+-]?\d{1,10}$/', $cash) || !preg_match('/^[+-]?\d{1,10}$/', $free)
		|| abs((int)$cash) > $max_change || abs((int)$free) > $max_change) {
		redir($back, "Give the points as whole numbers between -" . number_format($max_change) . " and " . number_format($max_change) . ".");
	}
	$cash = (int)$cash;
	$free = (int)$free;
	if ($cash == 0 && $free == 0) {
		redir($back, "Nothing to change.");
	}
	$query = query_format(CASH_ADD, $name, $cash, $cash, $cash, $free, $free, $free, $name, $name, max(0, $cash), max(0, $free));
	execute_query($query, "cash_points.php");
	$query = query_format(CASH_GET, $name);
	$result = execute_query($query, "cash_points.php");
	$now_cash = (int)$result->fields[2];
	$now_free = (int)$result->fields[3];
	add_admin_entry(sprintf("Cash points of %s: %+d points, %+d bonus points (now %d and %d)", $name, $cash, $free, $now_cash, $now_free));
	redir($back, html_escape($name) . " now has " . number_format($now_cash) . " points and " . number_format($now_free) . " bonus points.");
}

$query = CASH_TOTALS;
$result = execute_query($query, "cash_points.php");
echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Cash Points</div>
		<div class=\"infoTiles\">
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($result->fields[0]) . "</div><div class=\"infoLabel\">Accounts with points</div></div>
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($result->fields[1]) . "</div><div class=\"infoLabel\">Points</div></div>
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($result->fields[2]) . "</div><div class=\"infoLabel\">Bonus points</div></div>
		</div>
		<div class=\"newsInfo accountNote\">The points the in-game cash shop spends, per account (all its characters share them).
			Nothing in the game gives them; on twRO the web shop sold them. Bonus points are kept next to them.</div>
		<form action=\"cash_points.php\" method=\"GET\" class=\"accountForm\">
			<label>Account name <input type=\"text\" name=\"search\" class=\"myctl\" maxlength=\"24\" value=\"" . html_escape($search) . "\"></label>
			<input type=\"submit\" class=\"myctl\" value=\"Show\">
		</form>
	</div>
";

if ($search != "") {
	$query = query_format(CASH_GET, $search);
	$result = execute_query($query, "cash_points.php");
	echo "<div class=\"homeSection\">";
	if ($result->RowCount() == 0) {
		echo "<div class=\"newsItem\">There is no account named " . html_escape($search) . ".</div>";
	}
	else {
		$name = html_escape($result->fields[1]);
		$changed = $result->fields[4] ? "last changed " . html_escape(date("M j, Y H:i", strtotime($result->fields[4]))) : "no points yet";
		echo "<div class=\"mytitle homeTitle\">$name</div>
		<div class=\"infoTiles\">
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($result->fields[2]) . "</div><div class=\"infoLabel\">Points</div></div>
			<div class=\"infoTile\"><div class=\"infoNumber\">" . number_format($result->fields[3]) . "</div><div class=\"infoLabel\">Bonus points</div></div>
		</div>
		<form action=\"cash_points.php\" method=\"POST\" class=\"accountForm\">
			<input type=\"hidden\" name=\"account\" value=\"$name\">
			<label>Points to add <input type=\"number\" name=\"cash\" class=\"myctl\" value=\"0\" min=\"-$max_change\" max=\"$max_change\"></label>
			<label>Bonus points to add <input type=\"number\" name=\"free\" class=\"myctl\" value=\"0\" min=\"-$max_change\" max=\"$max_change\"></label>
			<div class=\"newsInfo\">A negative number takes points away (never below 0). Account ID " . (int)$result->fields[0] . ", $changed.
				The change shows the next time the player opens the cash shop.</div>
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Change Points\">
		</form>";
	}
	echo "</div>";
}

$query = CASH_LIST;
$result = execute_query($query, "cash_points.php");
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Accounts with Points</div>";
if ($result->RowCount() == 0) {
	echo "<div class=\"newsItem\">No account has points.</div>";
}
else {
	echo "<table class=\"contentTable\">
		<tr class=\"myheader\"><td>Account</td><td>Points</td><td>Bonus points</td><td>Last change</td></tr>";
	while ($line = $result->FetchRow()) {
		$name = html_escape($line[0]);
		echo "<tr class=\"mycell\"><td><a href=\"cash_points.php?search=" . urlencode($line[0]) . "\">$name</a></td>
			<td>" . number_format($line[1]) . "</td><td>" . number_format($line[2]) . "</td>
			<td>" . html_escape(date("M j, Y H:i", strtotime($line[3]))) . "</td></tr>";
	}
	echo "</table>";
}
echo "<div class=\"newsInfo accountNote\">The 100 most recently changed, in the game or here.</div></div>";

$query = CASH_LOG;
$result = execute_query($query, "cash_points.php");
echo "<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Recent Changes Here</div>";
if ($result->RowCount() == 0) {
	echo "<div class=\"newsItem\">No points given or taken here yet.</div>";
}
while ($line = $result->FetchRow()) {
	echo "<div class=\"newsItem\"><div class=\"newsMessage\">" . html_escape(substr($line[2], strlen("Cash points of "))) . "</div>"
		. "<div class=\"newsInfo\">" . html_escape($line[1]) . ", " . html_escape(date("M j, Y H:i", strtotime($line[0]))) . "</div></div>";
}
echo "<div class=\"newsInfo accountNote\">From the admin log. Points spent in the game are not listed.</div></div>
</div>
";

require 'footer.inc';
?>
