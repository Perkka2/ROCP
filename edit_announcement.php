<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
// Checks if action has been performed
if (!$GET_edit && !$GET_del && $POST_finish == "") {
	if ($STORED_level > 2) {
		// Displays User announcement options
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=5>User Announcements</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Post ID</td>
		<td>Date</td>
		<td>Message</td>
		<td>Poster</td>
	</tr>
		";
		$query = SHOW_EDIT_USER_ANNOUNCE;
		$result = execute_query($query, "edit_announcement.php");
		if ($result->RowCount() == 0) {
			echo "
	<tr class=mycell>
		<td colspan=5>There are no announcements to edit!</td>
	</tr>
			";
		}
		else {
			while ($line = $result->FetchRow()) {
				$post_id = $line[0];
				echo "
	<tr class=mycell>
		<td>
			<a href=\"edit_announcement.php?type=1&edit=$post_id\">Edit</a>
			-
			<a href=\"edit_announcement.php?type=1&del=$post_id\">Delete</a>
		</td>
				";
				foreach ($line as $col_value) {
					echo "
		<td>
			$col_value 
		</td>
					";
				}
				echo "
	</tr>
				";
			}
		}
		echo "
	</table>
		";
		// Displays GM announcement options
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=5>GM Announcements</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Post ID</td>
		<td>Date</td>
		<td>Message</td>
		<td>Poster</td>
	</tr>
		";
		$query = SHOW_EDIT_GM_ANNOUNCE;
		$result = execute_query($query, "edit_announcement.php");
		if ($result->RowCount() == 0) {
			echo "
	<tr class=mycell>
		<td colspan=5>There are no announcements to edit!</td>
	</tr>
			";
		}
		else {
			while ($line = $result->FetchRow()) {
				$post_id = $line[0];
				echo "
	<tr class=mycell>
		<td>
			<a href=\"edit_announcement.php?type=2&edit=$post_id\">Edit</a>
			-
			<a href=\"edit_announcement.php?type=2&del=$post_id\">Delete</a>
		</td>
			";
			foreach ($line as $col_value) {
				echo "
		<td>
			$col_value 
		</td>
				";
			}
			echo "
	</tr>
				";
			}
		}
		echo "
	</table>
		";
	}
	if ($STORED_level == 4) {
		// Displays Admin announcement options
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=5>Admin Announcements</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Post ID</td>
		<td>Date</td>
		<td>Message</td>
		<td>Poster</td>
	</tr>
	";
		$query = SHOW_EDIT_ADMIN_ANNOUNCE;
		$result = execute_query($query, "edit_announcement.php");
		if ($result->RowCount() == 0) {
			echo "
	<tr class=mycell>
		<td colspan=5>There are no announcements to edit!</td>
	</tr>
			";
		}
		else {
			while ($line = $result->FetchRow()) {
				$post_id = $line[0];
				echo "
	<tr class=mycell>
		<td>
		<a href=\"edit_announcement.php?type=3&edit=$post_id\">Edit</a>
		-
		<a href=\"edit_announcement.php?type=3&del=$post_id\">Delete</a>
		</td>
			";
				foreach ($line as $col_value) {
					echo "
	<td>
		$col_value 
	</td>
					";
				}
				echo "
	</tr>
				";
			}
		}
		echo "
</table>
		";
	}
}
else {
	switch ($GET_type) {
		case 1:
		$table = "user_announce";
		break;
		case 2:
		$table = "gm_announce";
		break;
		case 3:
		$table = "admin_announce";
		break;
	}
	if ($STORED_level == 2 && $GET_type == 3) {
		// GM trying to edit admin message
		redir("index.php", "You are trying to edit the wrong message type!");
	}
	if ($POST_finish == "Save Changes") {
		$new_message = add_escape(generate_breaks($POST_message));
		$query = sprintf(SAVE_ANNOUNCEMENT, $POST_table, $POST_date, $new_message, $POST_poster, $POST_post_id);
		$result = execute_query($query, "edit_announcement.php");
		add_admin_entry("Edited an Announcement");
		redir("index.php", "Announcement Edited!");
	}
	if ($GET_edit != "") {
		EchoHead(80);
		echo "
	<tr class=mytitle>
		<td colspan=5>Edit Announcement</td>
	</tr>
	<tr class=myheader>
		<td>Options</td>
		<td>Post ID</td>
		<td>Date</td>
		<td>Message</td>
		<td>Poster</td>
	</tr>
		";
		$query = sprintf(SHOW_EDIT_MESSAGE, $table, $GET_edit);
		$result = execute_query($query, "edit_announcement.php");
		$line = $result->FetchRow();
		$post_id = $line[0];
		$date = $line[1];
		$message = del_escape($line[2]);
		$poster = $line[3];
		echo "
	<tr class=mycell>
	<form action=\"edit_announcement.php\" method=\"POST\">
		<input type=\"hidden\" name=\"post_id\" class=\"myctl\" value=\"$post_id\">
		<input type=\"hidden\" name=\"table\" class=\"myctl\" value=\"$table\">
		<td>
			<input type=\"submit\" name=\"finish\" class=\"myctl\" value=\"Save Changes\">
		</td>
		<td>
			$post_id
		</td>
		<td>
			<input type=\"text\" name=\"date\" class=\"myctl\" value=\"$date\">
		</td>
		<td>
			<textarea rows=10 cols=80 name=\"message\" class=\"myctl\">$message</textarea>
		</td>
		<td>
			<input type=\"text\" name=\"poster\" class=\"myctl\" value=\"$poster\">
		</td>
	</form>
	</tr>
</table>
		";	
	}
	if ($GET_del != "") {
		$query = sprintf(DELETE_ANNOUNCEMENT, $table, $GET_del);
		$result = execute_query($query, "edit_announcement.php");
		if ($link->Affected_Rows() > 0) {
			add_admin_entry("Deleted an announcement");
			redir("index.php", "Announcement successfully deleted!");
		}
		else {
			redir("index.php", "Announcement could not be deleted!");
		}
	}
}
require 'footer.inc';
?>