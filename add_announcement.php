<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// This allows you to edit messages
if ($POST_action == "Add Admin Message") {
	$new_message = add_escape(generate_breaks($POST_admin_message));
	$poster = $STORED_login;
	$query = sprintf(ANNOUNCE_ADD_ADMIN, $new_message, $poster);
	$result = execute_query($query, "add_announcement.php");
	redir("add_announcement.php", "Announcement Added!");
}
elseif ($POST_action == "Add GM Message") {
	$new_message = add_escape(generate_breaks($POST_gm_message));
	$poster = $STORED_login;
	$query = sprintf(ANNOUNCE_ADD_GM, $new_message, $poster);
	$result = execute_query($query, "add_announcement.php");
	redir("add_announcement.php", "Announcement Added!");
}
elseif ($POST_action == "Add User Message") {
	$new_message = add_escape(generate_breaks($POST_user_message));
	$poster = $STORED_login;
	$query = sprintf(ANNOUNCE_ADD_USER, $new_message, $poster);
	$result = execute_query($query, "add_announcement.php");
	redir("add_announcement.php", "Announcement Added!");
}
EchoHead(80);
echo "
<tr class=mytitle>
	<td>Add an Announcement</td>
</tr>
<tr class=myheader>
	<td>
		This section allows you to add an announcement.
	</td>
</tr>
<form action=\"add_announcement.php?edit=true\" method=\"POST\">
";

if ($STORED_level > 1) {
	// display GM and normal message
	echo "
<tr class=mycell>
	<td>
		Message to All Users (seen by everyone):
	</td>
</tr>
<tr class=mycell>
	<td>
		<textarea name = \"user_message\" class=\"myctl\" ROWS=10 COLS=80></textarea>
	</td>
</tr>
<tr class=mycell>
	<td>
		<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Add User Message\">
	</td>
</tr>
<tr>
	<td height=5></td>
</tr>
<tr class=mycell>
	<td>
		Message to All GMs (seen by GMs and Admins):
	</td>
</tr>
<tr class=mycell>
	<td>
		<textarea name = \"gm_message\" class=\"myctl\" ROWS=10 COLS=80></textarea>
	</td>
</tr>
<tr class=mycell>
	<td>
		<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Add GM Message\">
	</td>
</tr>
	";
}

if ($STORED_level > 2) {
	// display Admin message
	echo "
<tr>
	<td height=5></td>
</tr>
<tr class=mycell>
	<td>
		Message to All Admins (seen by Admins):
	</td>
</tr>
<tr class=mycell>
	<td>
		<textarea name = \"admin_message\" class=\"myctl\" ROWS=10 COLS=80></textarea>
	</td>
</tr>
<tr class=mycell>
	<td>
		<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Add Admin Message\">
	</td>
</tr>
	";
}
echo "</form>
</table>
";
require 'footer.inc';
?>
