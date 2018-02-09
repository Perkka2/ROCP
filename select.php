<?php
require 'config.php';
$page = $_GET['option'];
if ($CONFIG['save_type'] == 2) {
	$sess_id = $_GET['sessid'];
	if ($page == 'money_transfer.php') {
		$page .= '?step=1';
		$page .= "&sessid=$sess_id";
	}
	elseif (substr($page,0,4) == 'hair') {
		$page .= "&sessid=$sess_id";
	}
	else {
		$page .= "?sessid=$sess_id";
	}
}
else {
	if ($page == 'money_transfer.php') {
		$page .= '?step=1';
	}
	elseif ($page == 'none') {
		$page = "index.php";
	}
}
echo "<head><meta http-equiv=\"refresh\" content='0;url=$page'></head>";
?>