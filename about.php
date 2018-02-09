<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
$message = <<< MSG
	This Control Panel was coded by Azndragon and heavily modified by Perkka<br>
MSG;
$message;
require "skin/$STORED_skin/skin.php";
echo "
        <p />
	<b>The current skin was made by {$SKIN['author']}:</b>
	<br />
	{$SKIN['about']}
";
require 'footer.inc';
?>
