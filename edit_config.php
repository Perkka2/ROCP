<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
EchoHead(100);
if ($GET_saveedit != "") {
	edit_config($GET_edit, $GET_new_value);

	// Reload the config.php file
	require 'config.php';
	if ($GET_edit == "server_rules") {
		$edit_message = "Changed \$CONFIG['$GET_edit'] to $GET_new_value";
	}
	else {
		$edit_message = "Changed \$CONFIG['$GET_edit'] to " . htmlspecialchars($CONFIG[$GET_edit]);
	}
	// Adds to log
	add_admin_entry(add_escape($edit_message));
	redir("edit_config.php", $edit_message);
}
if (!$GET_edit) {
	// refreshes the config variables to display properly
	require 'config.php';
	echo "
	<tr class=mytitle>
		<td colspan=4>config.php</td>
	</tr>
	<tr class=myheader>
		<td>Edit</td>
		<td>Variable</td>
		<td>Value</td>
		<td>Description</td>
	</tr>
	";
	$config_num_index = 0;
	foreach ($CONFIG as $config_index => $config_value) {
		$hide_values = array("db_username", "db_password", "passphrase", "smtp_login", "smtp_pass");
		if (is_bool($config_value)) {
			if ($config_value == true) {
				$config_value = "True";
			}
			else {
				$config_value = "False";
			}
		}
		if (in_array($config_index, $hide_values)) {
			$config_value = "*********";
		}
		$config_desc = htmlspecialchars(determine_config_desc($config_num_index));
		echo "
		<tr class=mycell>
			<td><a href=\"edit_config.php?edit=$config_index\">Edit</a></td>
			<td>$config_index</td>
			<td>$config_value</td>
			<td>$config_desc</td>
		</tr>
		";
		$config_num_index++;
	}
}
else {
	echo "
	<tr class=mytitle>
		<td colspan=3>config.php</td>
	</tr>
	<tr class=myheader>
		<td>Variable</td>
		<td>Value</td>
		<td>Description</td>
	</tr>
	";
	$config_num_index = 0;
	foreach ($CONFIG as $config_index => $config_value) {
		if ($config_index == $GET_edit) {
			if ($config_index == "db_username" or $config_index == "db_password" or $config_index == "passphrase") {
				$form_object = "password";
				$config_value = "";
			}
			elseif ($config_index == "server_rules") {
				$form_object = "textarea";
			}
			else {
				$form_object = "text";
			}
			echo "
			<form action=\"edit_config.php\" method=\"GET\">
			<input type=\"hidden\" name=\"edit\" class=\"myctl\" value=\"$config_index\">
				<tr class=mycell>
					<td>$config_index</td>
			";
			if ($form_object == "text" or $form_object == "password") {
				echo "
					<td><input type=\"$form_object\" name=\"new_value\" class=\"myctl\" value=\"$config_value\"></td>
				";
			}
			else {
				$config_value = str_replace("<br>", "\r\n", $config_value);
				echo "
				<td>
				<textarea rows=10 cols=80 name=\"new_value\" class=\"myctl\">$config_value</textarea>
				</td>
				";
			}
			$config_desc = htmlspecialchars(determine_config_desc($config_num_index));
			echo "
					<td>$config_desc</td>
				</tr>
				<tr class=mycell>
					<td colspan=3>
						<input type=\"submit\" name=\"saveedit\" class=\"myctl\" value=\"Save!\">
					</td>
				</tr>
			</form>
			";
			break;
		}
		$config_num_index++;
	}
}

echo "
	</tr>
</table>
";
require 'footer.inc';
?>