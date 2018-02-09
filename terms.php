<?php
require 'memory.php';
require_once 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
EchoHead(80);
echo "
<tr class=\"mytitle\">
	<td>Terms of the Control Panel</td>
</tr>
<tr class=\"myheader\">
	<td>
	When using the Control Panel, be aware of the following:
	</td>
</tr>
<tr class=\"mycell\">
	<td>
		Only logins/passwords with (A-Z, a-z, 0-9, spaces) will be allowed access for security reasons.<br />
		All actions and changes to the database are logged.<br />
		Any attempts to exploit the control panel with malicious input, either intentionally or unintentionally will be logged.<br />
		Repeated exploit attempts can result in a ban, at the GMs' discretion.<br />
	</td>
</tr>
</table>
";
if (strpos($_SERVER['PHP_SELF'], "terms.php") !== FALSE) {
	require 'footer.inc';
}
?>