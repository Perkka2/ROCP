<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
set_time_limit(0);

$query = "SELECT count(*) FROM mob_db";
$result = $link->Execute($query);
if (!$result || $result->fields[0] == 0) {
	redir("index.php", "You do not have a mob_db to rebuild from!");
}

$query = CLEAR_MOB_TABLE;
$link->Execute($query);

$query = GET_MOB_TABLE;
$result = execute_query($query, "rebuild_mobs.php");

while ($line = $result->FetchRow()) {
	$query2 = sprintf(INSERT_MOB_TABLE, $line[0], add_escape($line[1]), $line[2], $line[3], 
	$line[4], $line[5], $line[6], $line[7], $line[8], $line[9], $line[10], $line[11], $line[12],
	$line[13], $line[14], $line[15], $line[16], $line[17], $line[18], $line[19], $line[20], $line[21],
	$line[22], $line[23], $line[24], $line[25], $line[26], $line[27], $line[28], $line[29], $line[30],
	$line[31], $line[32], $line[33], $line[34]);
	$link->Execute($query2);
}

redir("index.php", "Mob Database Rebuilt!");
require 'footer.inc';
?>