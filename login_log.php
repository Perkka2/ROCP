<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
set_time_limit(0);
if ($GET_login != "") {
	$query = "SELECT ip FROM `loginlog` WHERE ip <> '' AND user = '$GET_login' GROUP BY ip ORDER BY ip DESC";
	$header = "IPs that have accessed $GET_login";
	$display_type = 1;
}
elseif ($GET_ip != "") {
	$ip = long2ip($GET_ip);
	$query = "SELECT user FROM `loginlog` WHERE ip <> '' AND ip = '$ip' AND user <> 'unknown' GROUP BY user";
	$header = "Account (IP: $ip)</td><td>Action";
	$display_type = 2;
}
elseif ($GET_history != "") {
	$query = "SELECT time, ip, user, log FROM `loginlog` WHERE user = '$GET_history'";
	$header = "Time</td><td>IP</td><td>User</td><td>Log";
	$display_type = 3;
}
else {
	$query = "SELECT ip, count(DISTINCT user) AS linked FROM `loginlog` WHERE ip <> '' AND user <> 'unknown' GROUP BY ip ORDER BY linked DESC";
	$header = "IP</td><td>Accounts";
	$display_type = 4;
}
$result = execute_query($query, "login_log.php");
if ($result->RowCount() == 0) {
	echo "No actions have been taken!";
}
else {
	EchoHead(80);
	echo "
	<tr class=mytitle>
		<td colspan=5>Login Log for $CONFIG_server_name</td>
	</tr>
	<tr class=myheader>
		<td>$header</td>
	</tr>
	";
	while ($line = $result->FetchRow()) {
		echo "<tr class=mycell>\n";
		foreach ($line as $display_index => $col_value) {
			if ($display_type == 3) {
				echo "<td>$col_value</td>";
			}
			else {
				if ($display_type == 1 or $display_type == 4) {
					$long_ip = ip2long($col_value);
					$display = "<a href=\"login_log.php?ip=$long_ip\">$col_value</a>";
				}
				else {
					$display = "<a href=\"login_log.php?login=$col_value\">$col_value</a>";
				}
				echo "<td>$display</td>\n";
				if ($display_type == 2) {
					echo "<td>
						<a href=\"ban.php?action=Ban Account&account_name=$col_value\">Ban</a>
						-
						<a href=\"login_log.php?history=$col_value\">Login History</a>
					</td>
					";
				}
			}
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
}
require 'footer.inc';
?>