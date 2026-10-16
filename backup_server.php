<?php
require 'memory.php';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if (!$GET_action) {
	/*require 'header.inc';
	echo "This will allow you to backup the entire server into a text file and will be saved to the CP folder.\n";
	echo "<br><a href=\"backup_server.php?action=backup\">Download Backup Here!</a>";
	require 'footer.inc';*/
}
else {
	/* Source File For MySQL backup obtained from Planet Source Code
	Credits to: Keyur Itchhaporia */
	// Gives unlimited time
	set_time_limit(0);
	add_admin_entry("Backed up Database"); // add to admin log
	$asfile="download";
	$crlf="\r\n";
	mysql_connect($CONFIG_db_host, $CONFIG_db_username, $CONFIG_db_password);
	mysql_select_db($CONFIG_db_name);
	
	$dump_buffer = "";
	
	$tables = mysql_query("SHOW TABLES FROM `$CONFIG_db_name`");
	$num_tables = mysql_num_rows($tables);
	
	if ($num_tables == 0)
	{
		require 'header.inc';
		redir("index.php", "No tables found to back up!");
	}

	// Remembers when the last backup was made, for the reminder on index.php
	$file = fopen("last_backup.txt", "w");
	fputs($file, time());
	fclose($file);

	// The backup is sent to the browser as a download, nothing is stored in the web folder
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"backup_" . date("Y-m-d_H-i") . ".sql\"");

	$dump_buffer.= "# Database Backup $crlf";
	$dump_buffer.= "# Backup made:$crlf";
	$dump_buffer.= "# ".date("F j, Y, g:i a")."$crlf";
	$dump_buffer.= "# Database: $CONFIG_db_name$crlf";
	echo $dump_buffer;
	$i = 0;
	while($i < $num_tables)
	{
		$dump_buffer = "";
		$table = mysql_tablename($tables, $i);
		$skipped_tables = array("charlog", "interlog", "item_db", "loginlog", "mob_db");
		if (in_array($table, $skipped_tables)) {
			// Ignores some non-crucial Athena DBs
			$i++;
			continue;
		}
		$dump_buffer.= "# --------------------------------------------------------$crlf";
		$dump_buffer.= "$crlf#$crlf";
		$dump_buffer.= "# Table structure for table '$table'$crlf";
		$dump_buffer.= "#$crlf$crlf";
		$db = $table;
		$dump_buffer.= get_table_def($table, $crlf).";$crlf";
		$dump_buffer.= "$crlf#$crlf";
		$dump_buffer.= "# Dumping data for table '$table'$crlf";
		$dump_buffer.= "#$crlf$crlf";
		$tmp_buffer="";
		get_table_content($CONFIG_db_name, $table, 0, 0, 'my_handler');
		$dump_buffer.=$tmp_buffer;
		
		$i++;
		$dump_buffer.= "$crlf";
		echo $dump_buffer;
	}
	exit();
}

function get_table_def($table, $crlf)
{
	$schema_create = "DROP TABLE IF EXISTS `$table`;$crlf";
	$schema_create .= "CREATE TABLE `$table` ($crlf";
	$query = "SHOW FIELDS FROM `$table`";
	$result = mysql_query($query) or die("Query failed $query: " . mysql_error());
	while($row = mysql_fetch_array($result))
	{
		$field = $row['Field'];
		$schema_create .= "  `$field` {$row['Type']}";
		
		if(isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"] == "0"))
		$schema_create .= " DEFAULT '" . addslashes($row['Default']) . "'";
		if($row["Null"] != "YES")
		$schema_create .= " NOT NULL";
		if($row["Extra"] != "")
		$schema_create .= " {$row['Extra']}";
		$schema_create .= ",$crlf";
	}
	$schema_create = preg_replace("/," . preg_quote($crlf, "/") . "$/", "", $schema_create);
	$index = array();
	$query = "SHOW KEYS FROM `$table`";
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	while($row = mysql_fetch_array($result))
	{
		$kname=$row['Key_name'];
		$comment=(isset($row['Comment'])) ? $row['Comment'] : '';
		$sub_part=(isset($row['Sub_part'])) ? $row['Sub_part'] : '';
		
		if(($kname != "PRIMARY") && ($row['Non_unique'] == 0))
		$kname="UNIQUE|$kname";
		
		if($comment=="FULLTEXT")
		$kname="FULLTEXT|$kname";
		if(!isset($index[$kname]))
		$index[$kname] = array();
		
		if ($sub_part>1)
		$index[$kname][] = $row['Column_name'] . "(" . $sub_part . ")";
		else
		$index[$kname][] = $row['Column_name'];
	}
	
	foreach ($index as $x => $columns)
	{
		$schema_create .= ",$crlf";
		if($x == "PRIMARY")
		$schema_create .= "   PRIMARY KEY (";
		elseif (substr($x,0,6) == "UNIQUE")
		$schema_create .= "   UNIQUE " .substr($x,7)." (";
		elseif (substr($x,0,8) == "FULLTEXT")
		$schema_create .= "   FULLTEXT ".substr($x,9)." (";
		else
		$schema_create .= "   KEY $x (";
		
		$schema_create .= implode(", ", $columns) . ")";
	}
	
	$schema_create .= "$crlf)";
	return $schema_create;
}
function get_table_content($db, $table, $limit_from = 0, $limit_to = 0,$handler)
{
	// Defines the offsets to use
	if ($limit_from > 0) {
		$limit_from--;
	} 
	else {
		$limit_from = 0;
	}
	if ($limit_to > 0 && $limit_from >= 0) {
		$add_query  = " LIMIT $limit_from, $limit_to";
	} 
	else {
		$add_query  = '';
	}
	
	get_table_content_fast($db, $table, $add_query,$handler);
	
}

function get_table_content_fast($db, $table, $add_query = '',$handler)
{
	$query = 'SELECT * FROM `' . $table . "`" . $add_query;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	if ($result != false) {
		
		@set_time_limit(1200); // 20 Minutes
		
		// Checks whether the field is an integer or not
		for ($j = 0; $j < mysql_num_fields($result); $j++) {
			$field_set[$j] = mysql_field_name($result, $j);
			$type          = mysql_field_type($result, $j);
			if ($type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' ||
			$type == 'bigint'  ||$type == 'timestamp') {
				$field_num[$j] = true;
			} else {
				$field_num[$j] = false;
			}
		} // end for
		
		// Get the scheme
		if (isset($GLOBALS['showcolumns'])) {
			$fields        = implode(', ', $field_set);
			$schema_insert = "INSERT INTO `$table` (`$fields`) VALUES (";
		} else {
			$schema_insert = "INSERT INTO `$table` VALUES (";
		}
		
		$field_count = mysql_num_fields($result);
		
		$search  = array("\x0a","\x0d","\x1a"); //\x08\\x09, not required
		$replace = array("\\n","\\r","\Z");
		
		
		while ($row = mysql_fetch_row($result)) {
			for ($j = 0; $j < $field_count; $j++) {
				if (!isset($row[$j])) {
					$values[]     = 'NULL';
				} else if (!empty($row[$j])) {
					// a number
					if ($field_num[$j]) {
						$values[] = $row[$j];
					}
					// a string
					else {
						$values[] = "'" . str_replace($search, $replace, addslashes($row[$j])) . "'";
					}
				} else {
					$values[]     = "''";
				} // end if
			} // end for
			
			$insert_line = $schema_insert . implode(',', $values) . ')';
			unset($values);
			
			// Call the handler
			$handler($insert_line);
		} // end while
	} // end if ($result != false)
	
	return true;
}


function my_handler($sql_insert)
{
	global $crlf, $asfile;
	global $tmp_buffer;
	
	if(empty($asfile))
	$tmp_buffer.= htmlspecialchars("$sql_insert;$crlf");
	else
	$tmp_buffer.= "$sql_insert;$crlf";
}



function faqe_db_error()
{
	return mysql_error();
}



function faqe_db_insert_id($result)
{
	return mysql_insert_id($result);
}
?>