<?php
extract($_GET, EXTR_PREFIX_ALL, "GET");
extract($_POST, EXTR_PREFIX_ALL, "POST");
extract($CONFIG, EXTR_PREFIX_ALL, "CONFIG");

// Login state is kept server-side in a PHP session; the browser only holds the session id.
// $CONFIG['save_type'] is ignored, credentials are never read from cookies.
if (!defined('SESSION_IDLE_TIMEOUT')) {
	define('SESSION_IDLE_TIMEOUT', 60 * 60); // seconds of inactivity before being logged out
}

if (session_id() == "") {
	$session_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443;
	ini_set('session.use_only_cookies', 1);
	ini_set('session.use_trans_sid', 0);
	ini_set('session.use_strict_mode', 1);
	ini_set('session.cookie_httponly', 1);
	session_name('rocp_session');
	if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
		session_set_cookie_params(array(
			'lifetime' => 0,
			'path' => '/',
			'secure' => $session_secure,
			'httponly' => true,
			'samesite' => 'Lax'
		));
	}
	else {
		session_set_cookie_params(0, '/; samesite=Lax', '', $session_secure, true);
	}
	session_start();
}

// Expire idle sessions
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT) {
	$_SESSION = array();
	session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();

// Remove credential cookies left behind by older versions of the CP
foreach (array("login", "password", "mysql", "skin") as $legacy_cookie) {
	if (isset($_COOKIE[$legacy_cookie])) {
		setcookie($legacy_cookie, "", time() - 3600);
		unset($_COOKIE[$legacy_cookie]);
	}
}

$STORED_login = isset($_SESSION['login']) ? $_SESSION['login'] : "";
$STORED_password = isset($_SESSION['password']) ? $_SESSION['password'] : "";
?>