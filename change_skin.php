<?php
require 'memory.php';	// calls memory functions
require 'header.inc';	// brings in header
check_auth($_SERVER['PHP_SELF']); // checks for required access
EchoHead(20);

if ($GET_new != "") {
	if ($GET_new == $STORED_skin) {
		redir("index.php", "You are already using this skin!");
	}
	if (valid_skin($GET_new)) {
		$query = query_format(UPDATE_SKIN, $GET_new, $STORED_login);
		$result = execute_query($query, "change_skin.php", 0, 0, true);
		redir("index.php", "Skin Changed!");
	}
	else {
		redir("index.php", "Skin does not exist.");
	}
}
require 'footer.inc';
?>