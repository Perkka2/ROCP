<?php
require 'config.php';
$page = $_GET['option'];
if ($page == 'money_transfer.php') {
	$page .= '?step=1';
}
elseif ($page == 'none') {
	$page = "index.php";
}
echo "<head><meta http-equiv=\"refresh\" content='0;url=$page'></head>";
?>