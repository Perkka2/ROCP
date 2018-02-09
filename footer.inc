<!-- Begin footer -->
	</div>
</div>
<script type="text/javascript">
<!--
function move(form) {
	var index = form.option.selectedIndex;
	if (form.option.options[index].value != "0") {
		if (form.option.options[index].value != "none") {
			location=form.option.options[index].value;
		}
	}
}
//-->
</SCRIPT>
<?php

$execute_queries = $queries;


if (!$STORED_skin) {
	$STORED_skin = $CONFIG_default_skin;
}

// Get the skin author
require "skin/$STORED_skin/skin.php";
$sql_type = $CONFIG_server_type == 0? "MSSQL" : "MySQL";
$powered = sprintf($lang['poweredby'], $php_version, $sql_type);
$codedby = sprintf($lang['codedby'], $cp_version);
$skinby = sprintf($lang['skinby'], $SKIN['name'], $SKIN['author']);
echo "
<div class=\"footer\">
<div class=\"footerinfo\" >
			$powered<br/>
		$codedby<br />
		$skinby<br />
		{$lang['bestviewed']}<br/>
		</div>
";
$end_time = getmicrotime();
$execution_time = ($end_time - $start_time);
$gzip = $CONFIG_do_gzip_compress? $lang['gzipenabled'] : $lang['gzipdisabled'];
$execution_time = sprintf($lang['exectime'], floor(($execution_time) * 1000 + .5) * .001);
if ($STORED_level > 0)	{
	echo "
<div class=\"skinselection\">
	<form action=\"select.php\" method=\"get\">Current Skin:<br/>
			<select multiple class=\"myctl\" name=\"option\" onchange=\"move(this.form);\">
		";

		$dir = "skin/";
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if (is_dir("skin/$file")) {
						if ($file != "." && $file != "..") {
							$skin_name = $file;
							if ($file == $STORED_skin) {
								$selected = " selected";
							}
							else {
								$selected = "";
							}
							echo "<option$selected value=\"change_skin.php?new=$skin_name\"> - $skin_name</option>\n";
						}
					}
				}
				closedir($dh);
			}
		}


		echo "
			</select>
	</form>
</div>
	";
}
echo "
<div class=\"footerinfo\" >
			[ $execution_time ]<br/>
			[ {$lang['execquery']} $execute_queries ]<br/>
			[ $gzip ]<br/>
</div>
</div>";


if ($CONFIG_debug && $STORED_level > 2 && $queries > 0) {
	$debug_message = preg_replace("/$CONFIG_passphrase/", "********", $debug_message);
	echo "<div class=\"debuginfo\"><table>{$lang['queriesexecuted']} $queries</br>
	{$lang['querytotal']}</br>
	$total_execution</br>
	<tr>
	<td>{$lang['query#']}</td>
	<td>{$lang['querytype']}</td>
	<td>{$lang['querysource']}</td>
	<td>{$lang['queryquery']}</td>
	<td>{$lang['querytime']}</td>
	</tr>
	$debug_message
	</table>
	</div>
		";
}
echo "
</body>
</html>
	";
if ($CONFIG_do_gzip_compress) {
	// Compress buffered output if required and send to browser
	//
	// Borrowed from php.net!
	//
	$gzip_contents = ob_get_contents();
	ob_end_clean();

	$gzip_size = strlen($gzip_contents);
	$gzip_crc = crc32($gzip_contents);

	$gzip_contents = gzcompress($gzip_contents, 9);
	$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

	echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
	echo $gzip_contents;
	echo pack('V', $gzip_crc);
	echo pack('V', $gzip_size);
	exit();
}
?>
