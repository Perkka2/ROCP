<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$message = <<< MSG
	This Control Panel was coded by Azndragon and heavily modified by Perkka<br>
MSG;
$message;
if (!valid_skin($STORED_skin)) {
	$STORED_skin = $CONFIG_default_skin;
}
require "skin/$STORED_skin/skin.php";
echo "
        <p />
	<b>The current skin was made by {$SKIN['author']}:</b>
	<br />
	{$SKIN['about']}
";
require 'footer.inc';
?>
