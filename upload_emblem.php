<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if (!is_dir("emblem")) {
	// Creates the emblem folder if it doesn't exist
	mkdir("emblem");
}
// Determines root location, and adds the emblem folder to destination
if ($POST_guild_id) {
	$destination = dirname($_SERVER['SCRIPT_FILENAME']) . "/emblem/$POST_guild_id.bmp";
}
else {
	$destination = dirname($_SERVER['SCRIPT_FILENAME']) . "/emblem/$GET_guild_id.bmp";
}
if (!$POST_action) {
	if (!$GET_guild_id) {
		redir("index.php", "Invalid Guild");
	}
	// Makes sure that the user is on the right guild page, otherwise, kicks them out
	$query = sprintf(CHECK_MASTER, $CONFIG_passphrase, $GET_guild_id);
	$result = execute_query($query, "upload_emblem.php");
	if ($result->RowCount() > 0) {
		$line = $result->FetchRow();
		$guildmaster_name = $line[0];
		// Checks if the account is the master of the current guild.
		if (account_of_character($guildmaster_name) != $STORED_id) {
			add_exploit_entry("Tried to access another guild that was not theirs.");
			redir("index.php", "This is not your guild!");
		}
	}
	else {
		redir("index.php", "Invalid Guild");
	}
	EchoHead(50);
	echo "
	<tr class=mytitle>
		<td colspan=2>Upload A Guild Emblem</td>
	</tr>
	";
	if (file_exists($destination)) {
		echo "
	<tr class=mycell>
		<td colspan=2>Here is your current logo:</td>
	</tr>
	<tr class=mycell>
		<td colspan=2><img src=\"emblem\\$GET_guild_id.bmp\"></td>
	</tr>
	<tr class=mycell>
		<td colspan=2>You can upload another one, if you wish.</td>
	</tr>
		";
	}
	else {
		
		echo "
	<tr class=mycell>
		<td colspan=2>You do not have an emblem uploaded! You can upload one below:</td>
	</tr>
		";
	}
	display_upload_form();
}
else {
	if(!empty($_FILES["binFile"])) {
		if($_FILES['binFile']['name'] == '') {
			redir("upload_emblem.php?guild_id=$POST_guild_id", "You did not upload a file!");
		}
		elseif($_FILES['binFile']['size'] == 0) {
			redir("upload_emblem.php?guild_id=$POST_guild_id", "There appears to be a problem with the logo your are uploading");
		}
		elseif($_FILES['binFile']['size'] > $POST_MAX_FILE_SIZE) {
			redir("upload_emblem.php?guild_id=$POST_guild_id", "The photo you selected is too large");
		}
		elseif(!getimagesize($_FILES['binFile']['tmp_name'])) {
			redir("upload_emblem.php?guild_id=$POST_guild_id", "You did not upload a proper image file!");
		}
		else {
			$image_data = getimagesize($_FILES['binFile']['tmp_name']);
			$width = $image_data[0];
			$height = $image_data[1];
			if ($width != 24 or $height != 24) {
				//require 'header.inc';
				redir("upload_emblem.php?guild_id=$POST_guild_id", "The image must be 24x24!");
			}
			else {
				// The source of the upload
				// Uploads the file to final position
				if (move_uploaded_file($_FILES['binFile']['tmp_name'], $destination)) {
					redir("upload_emblem.php?guild_id=$POST_guild_id", "Upload file success!");
				}
				else {
					print_r($_FILES);
					redir("upload_emblem.php?guild_id=$POST_guild_id", "There was a problem uploading your file.");
				}
			}
			
		}
	}
	else {
		redir("index.php", "You did not upload a file!");
	}
}
require 'footer.inc';

function display_upload_form() {
	global $GET_guild_id;
	echo '
	<form method="POST" ACTION="upload_emblem.php" ENCTYPE="multipart/form-data">
	';
	echo "
		<input type=\"hidden\" NAME=\"guild_id\" value=\"$GET_guild_id\">
	";
	echo '
		<input type="hidden" NAME="MAX_FILE_SIZE" value="1000000">
		<input type="hidden" NAME="action" value="upload">
		<tr class=mycell>
			<td>File:</td>
			<td><input type="file" NAME="binFile" class=myctl></td>
		</tr>
		<tr class=mycell>
			<td colspan="2" align="center"><input type="submit" name="upload" value="Upload" class=myctl></td>
		</tr>
	</form> 
</table>
	';
}
?>