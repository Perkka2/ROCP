<?php
require 'memory.php';	// calls memory functions
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($GET_new != "" && $GET_new != $STORED_skin) {
	if (!valid_skin($GET_new)) {
		require 'header.inc';
		redir("index.php", "Skin does not exist.");
	}
	$query = query_format(UPDATE_SKIN, $GET_new, $STORED_login);
	$result = execute_query($query, "change_skin.php", 0, 0, true);
}

// Go straight back to the page the skin was changed on, with a plain HTTP redirect.
// Only pages of this site, and never one that performs an action (it would run again).
$return_page = "index.php";
if (isset($_SERVER['HTTP_REFERER'])) {
	$referer = parse_url($_SERVER['HTTP_REFERER']);
	$referer_host = isset($referer['host']) ? $referer['host'] . (isset($referer['port']) ? ":" . $referer['port'] : "") : "";
	if ($referer_host != "" && isset($referer['path']) && strcasecmp($referer_host, $_SERVER['HTTP_HOST']) == 0) {
		$referer_page = strtolower(basename($referer['path']));
		$referer_params = array();
		if (isset($referer['query'])) {
			parse_str($referer['query'], $referer_params);
		}
		if (preg_match('/^[a-z0-9_]+\.php$/', $referer_page) && !in_array($referer_page, array('change_skin.php', 'select.php'))
		&& !csrf_get_action($referer_page, $referer_params)) {
			$return_page = $referer_page . (count($referer_params) ? "?" . http_build_query($referer_params) : "");
		}
	}
}
// Absolute URL, some very old browsers only follow absolute redirects
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
$folder = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
header("Location: " . ($https ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "$folder/$return_page");
exit();
?>
