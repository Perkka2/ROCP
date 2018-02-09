<?php
extract($_GET, EXTR_PREFIX_ALL, "GET");
extract($_POST, EXTR_PREFIX_ALL, "POST");
extract($CONFIG, EXTR_PREFIX_ALL, "CONFIG");
if ($CONFIG_save_type == 1) {
	extract($_COOKIE, EXTR_PREFIX_ALL, "STORED");
}
elseif ($CONFIG_save_type == 2) {
	extract($_SESSION, EXTR_PREFIX_ALL, "STORED");
}
?>