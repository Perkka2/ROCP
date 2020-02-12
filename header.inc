<?php
if ($CONFIG_do_gzip_compress) {
	// Start gzip compression
	$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;

	if ($php_version >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
	{
		if ( extension_loaded('zlib') )
		{
			ob_start('ob_gzhandler');
		}
	}
	elseif ($php_version > '4.0' )
	{
		if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') )
		{
			if ( extension_loaded('zlib') )
			{
				$do_gzip_compress = TRUE;
				ob_start();
				ob_implicit_flush(0);

				header('Content-Encoding: gzip');
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<?php
echo "
<head>
	<title>".$CONFIG_panel_name."</title>
	<link rel=\"stylesheet\" type=\"text/css\" href=\"skin/$STORED_skin/style.css\" />
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\"></meta>
</head>
<body>
	<div class=\"header\" style=\"background-image: url(skin/$STORED_skin/images/acp.jpg);\">
		<div class=\"serverinfo\">
";
////////////////////////////
///// SCRIPT BY JOKER /////
//////////////////////////

if ($CONFIG_check_server) {
	if ($CONFIG_maintenance) {
		$accsrv = "<font color=\"#000000\">Maint</font>";
		$charsrv = "<font color=\"#000000\">Maint</font>";
		$mapsrv = "<font color=\"#000000\">Maint</font>";
	}
	else {
		$query = CHECK_STATUS;
		$result = execute_query($query, "header.inc");
		if ($result->RowCount() == 0) {
			$query = INSERT_STATUS;
			$result = execute_query($query, "header.inc", 0, 0, true);
		}
		$line = $result->FetchRow();
		$diff = time() - strtotime($line[0]);
		$next = 500 - $diff;
		if ($diff > 300 || $GET_act == "updatestatus") {
			// Over 5 minutes since last update
			$acc = @fsockopen ($CONFIG_accip, $CONFIG_accport, $errno, $errstr, 1);
			$char = @fsockopen ($CONFIG_charip, $CONFIG_charport, $errno, $errstr, 1);
			for ($i = 1; $i <= $CONFIG_mapcount; $i++) {
				$maptest = @fsockopen ($CONFIG_mapip, $CONFIG_mapport+$i-1, $errno, $errstr, 1);
				usleep(500);
				if ($maptest > 1){if(!feof($maptest)){$map = $map + (1 << $i-1);}}
			}
			usleep(500);
			if ($acc > 1){if(!feof($acc)){$acc = 1;} else {$acc = 0;}}
			if ($char > 1){if(!feof($char)){$char = 1;} else {$char = 0;}}
			//if ($maptest > 1) $map = 1;
			$query = sprintf(UPDATE_STATUS, $acc, $char, $map);
			$result = execute_query($query, "header.inc", 0, 0, true);
		}
		else {
			// Pull values from DB
			$acc = $line[1];
			$char = $line[2];
			$map = $line[3];
		}
		if (!$acc) {
			$accsrv = "<font color=\"#ff0000\">Offline</font>";
		}
		else {
			$accsrv = "<font color=\"#00ff00\">Online</font>";
		}

		if (!$char) {
			$charsrv = "<font color=\"#ff0000\">Offline</font>";
		}
		else {
			$charsrv = "<font color=\"#00ff00\">Online</font>";
		}

		if (!$map) {
			$mapsrv = "<font color=\"#ff0000\">Offline</font>";
		}
		else {
			$maponline = 0;
			if ($CONFIG_server_type == 0) {
				$lang['mapserv'] = "Zone Server";
			}
			for ($i = 1; $i <= $CONFIG_mapcount; $i++) {
				if ($map & (1 << $i-1)){
					$maponline++;
					$maptooltip .= "{$lang['mapserv']} $i <font color=\"#00ff00\">&nbsp; Online</font><br>";
				}
				else {
					$maptooltip .= "{$lang['mapserv']} $i <font color=\"#ff0000\">&nbsp; Offline</font><br>";
				}
			}
			if($maponline < $CONFIG_mapcount){
			$mapsrv = "<font color=\"#ff9900\">&nbsp; {$maponline}/{$CONFIG_mapcount} Online</font>";
			}
			else{
			$mapsrv = "<font color=\"#00ff00\">&nbsp; Online</font>";
			}
		}
	}
}
else {
	$accsrv = "<font color=\"#ff0000\">N/A</font>";
	$charsrv = "<font color=\"#ff0000\">N/A</font>";
	$mapsrv = "<font color=\"#ff0000\">N/A</font>";
}
$total_online = sprintf($lang['totalonline'], GetUserCount());
if ($CONFIG_server_type == 0) {
	$lang['mapserv'] = "Zone Server";
	if($CONFIG_mapcount > 1){
		$lang['mapserv'] = "Zone Servers";
	}
}
echo "{$lang['current']}</br>
			{$lang['accserv']} $accsrv</br>
			{$lang['charserv']}	$charsrv</br>
			<div class=\"tooltip\">{$lang['mapserv']} $mapsrv
		  	<span class=\"tooltipcontainer\">$maptooltip</span>
			</div>
			$total_online

	</div>
		<div class=\"servername\">{$CONFIG_server_name}</div>
</div>
";

if ($STORED_level == 0) {
	echo "
<div class=\"loginstatus\">
{$lang['loggedas']} <b>{$lang['guest']}</b></br>
			{$lang['accesslevel']} <b>None</b>
			<a href=\"terms.php\">{$lang['terms']}</a>
			[ <a href=\"login.php\">{$lang['login']}</a> ]
</div>
	";
}
else {
	if ($STORED_level == 4) {
		$edit_config = "
			<a href=\"edit_config.php\">Edit Config</a>
		";
	}
	if ($STORED_level >= 3) {
		$edit_config .= "
			<a href=\"index.php?act=updatestatus\">Update Server Status</a>
		";
	}
	echo "
	<div class=\"loginstatus\">
			{$lang['loggedas']} <b>$STORED_login</b>
			{$lang['accesslevel']} <b>$privilege</b>
		$edit_config
			<a href=\"terms.php\">{$lang['terms']}</a>
			[ <a href=\"login.php?action=logout\">{$lang['logout']}</a> ]
	</div>
	";
}
?>
<div class="main">

<?php
if ($STORED_level > 0)	{
	echo "
<div class=\"menu\">
<div class=\"dropdowncontainer\">
		<div class=\"menuheader\"><a href=#>{$lang['useroptions']}</a></div>
		<div class=\"dropdownmenu\">
	";
	foreach ($lang['page'] as $page => $string) {
		if ($access[$page] > 1 || $access[$page] == -1) {
			continue;
		}
		if ($page == "hairstyle.php") {
			break;
		}
		if ($page == "upload_emblem.php") {
			break;
		}
		echo "<a href=\"$page\">$string</a>\n";
	}

	if ($STORED_level >= $access['hairstyle.php'] && $access['hairstyle.php'] != -1) {
		$query = sprintf(SHOW_HAIRSTYLE, $STORED_id);
		$result = execute_query($query, 'header.inc');
		while ($line = $result->FetchRow()) {
			$char_name = $line[0];
			$char_num = $line[1];
			printf("<a href=hairstyle.php?char=%d>{$lang['page']['hairstyle.php']}</a>", $char_num, $char_name);
		}
	}

	if ($STORED_level >= $access['upload_emblem.php'] && $access['upload_emblem.php'] != -1) {
		$query = sprintf(CHARS_ON_ACCOUNT, $STORED_id);
		$result = execute_query($query, "header.inc");
		while ($line = $result->FetchRow()) {
			$char = CharID_To_CharName($line[0]);
			if ($char != "") {
				$query = sprintf(SHOW_GUILD_MASTER, $char);
				$master = execute_query($query, 'header.inc');
				$name = $master->FetchRow();
				if ($master->RowCount() == 1) {
					printf("<a href=upload_emblem.php?guild_id=%s>{$lang['page']['upload_emblem.php']}</a>", md5($name[0] . $CONFIG_passphrase), $name[1]);
				}
			}
		}
	}

	echo "</div></div>";
	// Only levels above 2 get GM menu
	if ($STORED_level > 2) {
			echo "<div class=\"dropdowncontainer\">
					<div class=\"menuheader\"><a href=#>GM/Admin Options:</a></div>
					<div class=\"dropdownmenu\">
		";
		foreach ($lang['gmpage'] as $page => $string) {
			if (substr($page, 0, 4) == "none") {
				echo "<div class=\"menusubheader\">$string</div>\n";
			}
			else {
				if ($STORED_level < $access[$page] || $access[$page] == -1) {
					continue;
				}
				echo "<a href=\"$page\">$string</a>\n";
				if ($CONFIG_server_type == 0 && $page == "view_ban_list.php") {
					echo "<a href=whosonline.php>{$lang['page']['whosonline.php']}</a>\n";
				}
			}
		}

			echo "</div></div>";
	}
	echo "
</div>";
}
 ?>
 <div class="content">
<!-- End Header -->
