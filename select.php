<?php
// Only redirects to a page of this control panel, e.g. "change_skin.php?new=default"
$page = isset($_GET['option']) ? $_GET['option'] : "";
if (!is_string($page) || !preg_match('/^[A-Za-z0-9_]+\.php(\?[A-Za-z0-9_=&.-]*)?$/D', $page)) {
	$page = "index.php";
}
if ($page == 'money_transfer.php') {
	$page .= '?step=1';
}
echo "<head><meta http-equiv=\"refresh\" content='0;url=" . htmlspecialchars($page, ENT_QUOTES) . "'></head>";
?>