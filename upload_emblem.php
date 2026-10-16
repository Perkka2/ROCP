<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

// Largest accepted file: a 24x24 BMP is about 2.4 KB even at 32 bits per pixel
$max_emblem_size = 4096;

// The guild id is the md5 hash made in header.inc, it is also the file name of the emblem
$guild_id = $POST_action ? $POST_guild_id : $GET_guild_id;
if (!preg_match('/^[0-9a-f]{32}$/', $guild_id)) {
	redir("index.php", "Invalid Guild");
}
// Only the guild master may see the page or upload, checked for both
$query = query_format(CHECK_MASTER, $CONFIG_passphrase, $guild_id);
$result = execute_query($query, "upload_emblem.php");
if ($result->RowCount() == 0) {
	redir("index.php", "Invalid Guild");
}
$line = $result->FetchRow();
if (account_of_character($line[0]) != $STORED_id) {
	add_exploit_entry("Tried to access another guild that was not theirs.");
	redir("index.php", "This is not your guild!");
}

if (!is_dir("emblem")) {
	// Creates the emblem folder if it doesn't exist
	mkdir("emblem");
}
$destination = dirname($_SERVER['SCRIPT_FILENAME']) . "/emblem/$guild_id.bmp";
$back = "upload_emblem.php?guild_id=$guild_id";

if (!$POST_action) {
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
		<td colspan=2><img src=\"emblem/$guild_id.bmp\"></td>
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
	display_upload_form($guild_id, $max_emblem_size);
}
else {
	$upload = isset($_FILES['binFile']) ? $_FILES['binFile'] : null;
	if (!$upload || $upload['error'] == UPLOAD_ERR_NO_FILE || $upload['name'] == '') {
		redir($back, "You did not upload a file!");
	}
	elseif ($upload['error'] != UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
		redir($back, "There was a problem uploading your file.");
	}
	elseif (filesize($upload['tmp_name']) > $max_emblem_size) {
		redir($back, "The emblem file is too large");
	}
	else {
		$emblem_check = check_emblem_bmp($upload['tmp_name']);
		if ($emblem_check !== true) {
			redir($back, $emblem_check);
		}
		elseif (move_uploaded_file($upload['tmp_name'], $destination)) {
			chmod($destination, 0644);
			redir($back, "Upload file success!");
		}
		else {
			redir($back, "There was a problem uploading your file.");
		}
	}
}
require 'footer.inc';

/**
 * @return mixed true, or the error message
 * @param string $file Path of the uploaded file
 * @desc Accepts only an uncompressed 24x24 BMP whose size matches its header exactly, so no other data can be hidden in it
*/
function check_emblem_bmp($file) {
	$data = file_get_contents($file);
	$length = strlen($data);
	if ($length < 54 || substr($data, 0, 2) != "BM") {
		return "The emblem must be a BMP image!";
	}
	$bmp = unpack('Vfile_size/vreserved1/vreserved2/Vdata_offset/Vheader_size/Vwidth/Vheight/vplanes/vbits/Vcompression', substr($data, 2, 32));
	// Height is negative for top-down bitmaps
	$height = $bmp['height'] > 0x7FFFFFFF ? 0x100000000 - $bmp['height'] : $bmp['height'];
	if ($bmp['width'] != 24 || $height != 24) {
		return "The image must be 24x24!";
	}
	if ($bmp['planes'] != 1 || $bmp['compression'] != 0 || !in_array($bmp['bits'], array(1, 4, 8, 24, 32))
	|| !in_array($bmp['header_size'], array(40, 108, 124))) {
		return "The emblem must be an uncompressed BMP image!";
	}
	$palette_size = $bmp['bits'] <= 8 ? 4 * (1 << $bmp['bits']) : 0;
	$row_size = (int)(floor(($bmp['bits'] * 24 + 31) / 32) * 4);
	if ($bmp['data_offset'] < 14 + $bmp['header_size'] || $bmp['data_offset'] > 14 + $bmp['header_size'] + $palette_size
	|| $bmp['file_size'] != $length || $bmp['data_offset'] + $row_size * 24 != $length) {
		return "The BMP file is not valid!";
	}
	return true;
}

function display_upload_form($guild_id, $max_emblem_size) {
	echo "
	<form method=\"POST\" ACTION=\"upload_emblem.php\" ENCTYPE=\"multipart/form-data\">
		<input type=\"hidden\" NAME=\"guild_id\" value=\"$guild_id\">
		<input type=\"hidden\" NAME=\"MAX_FILE_SIZE\" value=\"$max_emblem_size\">
		<input type=\"hidden\" NAME=\"action\" value=\"upload\">
		<tr class=mycell>
			<td>File (24x24 BMP):</td>
			<td><input type=\"file\" NAME=\"binFile\" class=myctl></td>
		</tr>
		<tr class=mycell>
			<td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"upload\" value=\"Upload\" class=myctl></td>
		</tr>
	</form>
</table>
	";
}
?>