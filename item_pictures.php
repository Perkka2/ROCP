<?php
// Admin tool: extracts the item pictures (images/items/images/<id>.png, the big picture on item tooltips, and
// images/items/icons/<id>.png, the inventory icon) from the game client GRFs in ROChargenPHP/client/DATA.INI,
// for the items that have none or again for the ones it extracted.
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
set_time_limit(0);
ini_set('memory_limit', '1024M');

$item_picture_dirs = array('images' => "images/items/images", 'icons' => "images/items/icons");
// the GRF folders of each kind of picture ("유저인터페이스" in the client's Korean code page)
$item_grf_dirs = array('images' => "data\\texture\\\xc0\xaf\xc0\xfa\xc0\xce\xc5\xcd\xc6\xe4\xc0\xcc\xbd\xba\\collection\\",
	'icons' => "data\\texture\\\xc0\xaf\xc0\xfa\xc0\xce\xc5\xcd\xc6\xe4\xc0\xcc\xbd\xba\\item\\");
// the items this tool extracted; the others came with the panel and are only extracted again one at a time
$item_extracted_file = "images/items/extracted.txt";

// ROChargen's GRF reader, loaded the way its own index.php does
function item_grf_start() {
	static $started = false;
	if ($started) {
		return;
	}
	$started = true;
	if (!defined('__ROOT__')) {
		define('__ROOT__', dirname(__FILE__) . '/ROChargenPHP/');
	}
	require_once(__ROOT__ . 'core/class.Debug.php');
	require_once(__ROOT__ . 'core/class.Client.php');
	Client::$path = __ROOT__ . "client/";
	Client::$data_ini = "DATA.INI";
	Client::$AutoExtract = false;
	Client::init();
}

// The folders the client keeps its System folder in: ROChargen's client folder and the folders of the GRFs
function item_client_dirs() {
	$dirs = array(rtrim(Client::$path, '/'));
	if (file_exists(Client::$path . Client::$data_ini)) {
		$ini = parse_ini_file(Client::$path . Client::$data_ini, true);
		if (isset($ini['Data'])) {
			foreach ($ini['Data'] as $grf) {
				$dirs[] = dirname(Client::$path . $grf);
			}
		}
	}
	$out = array();
	foreach ($dirs as $dir) {
		$real = realpath($dir);
		if ($real !== false && !in_array($real, $out)) {
			$out[] = $real;
		}
	}
	return $out;
}

// The item information files of the newer clients (System/itemInfo*.lub or .lua), any case
function item_info_files() {
	$files = array();
	foreach (item_client_dirs() as $dir) {
		foreach ((array)@scandir($dir) as $sub) {
			if (strtolower($sub) != 'system' || !is_dir("$dir/$sub")) {
				continue;
			}
			foreach ((array)@scandir("$dir/$sub") as $file) {
				if (preg_match('/^iteminfo.*\.lu[ab]$/i', $file)) {
					$files[] = "$dir/$sub/$file";
				}
			}
		}
	}
	return $files;
}

// Item id => resource name from an itemInfo file: compiled Lua 5.1 (.lub) or Lua text
function parse_item_info($file) {
	$data = file_get_contents($file);
	if (substr($data, 0, 5) == "\x1bLuaQ") {
		return parse_item_info_lub($data);
	}
	$names = array();
	$parts = preg_split('/\[\s*(\d+)\s*\]\s*=\s*\{/', $data, -1, PREG_SPLIT_DELIM_CAPTURE);
	for ($i = 1; $i + 1 < count($parts); $i += 2) {
		if (preg_match('/identifiedResourceName\s*=\s*"((?:[^"\\\\]|\\\\.)*)"/', $parts[$i + 1], $m)) {
			$names[(int)$parts[$i]] = stripcslashes($m[1]);
		}
	}
	return $names;
}

// Compiled Lua 5.1: runs the table building instructions of the main function (LOADK, NEWTABLE, SETTABLE, MOVE and the globals)
// and keeps identifiedResourceName of every table stored under a number
function parse_item_info_lub($data) {
	$names = array();
	$header = unpack('Cversion/Cformat/Cendian/Cint/Csize_t/Cinstr/Cnumber/Cintegral', substr($data, 4, 8));
	if ($header['int'] != 4 || $header['size_t'] != 4 || $header['instr'] != 4 || $header['number'] != 8) {
		return $names;
	}
	$pos = 12;
	$read_int = function () use ($data, &$pos) {
		$v = unpack('V', substr($data, $pos, 4));
		$pos += 4;
		return $v[1];
	};
	$read_string = function () use ($data, &$pos, $read_int) {
		$len = $read_int();
		$s = $len > 0 ? substr($data, $pos, $len - 1) : null;
		$pos += $len;
		return $s;
	};
	// main function: source, line defined, last line, upvalues, parameters, vararg, stack size
	$read_string();
	$pos += 8 + 4;
	$code_count = $read_int();
	$code_pos = $pos;
	$pos += $code_count * 4;
	$constants = array();
	$constant_count = $read_int();
	for ($i = 0; $i < $constant_count; $i++) {
		$type = ord($data[$pos++]);
		if ($type == 1) {
			$constants[] = ord($data[$pos++]) != 0;
		}
		elseif ($type == 3) {
			$v = unpack('d', substr($data, $pos, 8));
			$pos += 8;
			$constants[] = $v[1];
		}
		elseif ($type == 4) {
			$constants[] = $read_string();
		}
		else {
			$constants[] = null;
		}
	}
	$code = unpack('V*', substr($data, $code_pos, $code_count * 4));
	$registers = array();
	$tables = array(); // table number => resource name, or the item number of a table stored under one
	$table_items = array();
	$globals = array();
	$next_table = 0;
	foreach ($code as $op) {
		$opcode = $op & 0x3F;
		$a = ($op >> 6) & 0xFF;
		if ($opcode == 0) { // MOVE
			$b = ($op >> 23) & 0x1FF;
			$registers[$a] = isset($registers[$b]) ? $registers[$b] : null;
		}
		elseif ($opcode == 1) { // LOADK
			$registers[$a] = $constants[($op >> 14) & 0x3FFFF];
		}
		elseif ($opcode == 5) { // GETGLOBAL
			$name = $constants[($op >> 14) & 0x3FFFF];
			$registers[$a] = isset($globals[$name]) ? $globals[$name] : null;
		}
		elseif ($opcode == 7) { // SETGLOBAL
			$globals[$constants[($op >> 14) & 0x3FFFF]] = isset($registers[$a]) ? $registers[$a] : null;
		}
		elseif ($opcode == 10) { // NEWTABLE
			$registers[$a] = array('table' => $next_table++);
		}
		elseif ($opcode == 9) { // SETTABLE
			$b = ($op >> 23) & 0x1FF;
			$c = ($op >> 14) & 0x1FF;
			$key = $b >= 256 ? $constants[$b - 256] : (isset($registers[$b]) ? $registers[$b] : null);
			$value = $c >= 256 ? $constants[$c - 256] : (isset($registers[$c]) ? $registers[$c] : null);
			$table = isset($registers[$a]['table']) ? $registers[$a]['table'] : null;
			if ($table === null) {
				continue;
			}
			if ($key === 'identifiedResourceName' && is_string($value)) {
				$tables[$table] = $value;
			}
			elseif (is_float($key) && is_array($value) && isset($value['table'])) {
				$table_items[$value['table']] = (int)$key;
			}
		}
		elseif ($opcode != 34) { // anything but SETLIST leaves a register we do not follow
			$registers[$a] = null;
		}
	}
	foreach ($table_items as $table => $item) {
		if (isset($tables[$table]) && $tables[$table] !== '') {
			$names[$item] = $tables[$table];
		}
	}
	return $names;
}

// Item id => resource name: the GRFs' idnum2itemresnametable.txt, then the client's itemInfo (newer clients
// keep the names only there), then a table in the panel's dbtranslation folder
function item_resource_names() {
	item_grf_start();
	$names = array();
	$path = Client::getFile('data/idnum2itemresnametable.txt');
	$tables = array();
	if ($path !== false) {
		$tables[] = file_get_contents($path);
	}
	if (file_exists("dbtranslation/idnum2itemresnametable.txt")) {
		$tables[] = file_get_contents("dbtranslation/idnum2itemresnametable.txt");
	}
	foreach ($tables as $table) {
		preg_match_all('/^\s*(\d+)#([^#\r\n]+)#/m', $table, $matches, PREG_SET_ORDER);
		foreach ($matches as $m) {
			$names[(int)$m[1]] = $m[2];
		}
	}
	foreach (item_info_files() as $file) {
		foreach (parse_item_info($file) as $id => $name) {
			$names[$id] = $name;
		}
	}
	ksort($names);
	return $names;
}

// Lower case file name => the GRF path, for each kind of picture (the GRF reader matches the case exactly)
function item_grf_files() {
	global $item_grf_dirs;
	item_grf_start();
	$files = array();
	foreach ($item_grf_dirs as $kind => $dir) {
		$files[$kind] = array();
		foreach (Client::search('/' . preg_quote($dir, '/') . '[^\0\\\\]+\.bmp/i') as $path) {
			$name = strtolower(substr($path, strlen($dir), -4));
			if (!isset($files[$kind][$name])) {
				$files[$kind][$name] = $path;
			}
		}
	}
	return $files;
}

// A BMP (8, 24 or 32 bits) as a true colour image, magenta transparent
function item_bmp_image($data) {
	if (strlen($data) < 54 || substr($data, 0, 2) != 'BM') {
		return false;
	}
	$h = unpack('Voffset/Vheader/lwidth/lheight/x2/vbits/Vcompression', substr($data, 10, 24));
	$width = $h['width'];
	$height = abs($h['height']);
	$bits = $h['bits'];
	if ($width <= 0 || $height <= 0 || $width > 4096 || $height > 4096 || $h['compression'] != 0 || !in_array($bits, array(8, 24, 32))) {
		return false;
	}
	$palette = array();
	if ($bits == 8) {
		$start = 14 + $h['header'];
		for ($i = $start; $i + 3 < $h['offset'] && count($palette) < 256; $i += 4) {
			$palette[] = array(ord($data[$i + 2]), ord($data[$i + 1]), ord($data[$i]));
		}
	}
	$img = imagecreatetruecolor($width, $height);
	imagealphablending($img, false);
	imagesavealpha($img, true);
	$transparent = imagecolorallocatealpha($img, 255, 0, 255, 127);
	$bytes = $bits >> 3;
	$stride = (($width * $bits + 31) >> 5) << 2;
	if (strlen($data) < $h['offset'] + $stride * $height) {
		return false;
	}
	for ($row = 0; $row < $height; $row++) {
		$y = $h['height'] > 0 ? $height - 1 - $row : $row;
		$line = $h['offset'] + $row * $stride;
		for ($x = 0; $x < $width; $x++) {
			$i = $line + $x * $bytes;
			if ($bits == 8) {
				$index = ord($data[$i]);
				list($r, $g, $b) = isset($palette[$index]) ? $palette[$index] : array(0, 0, 0);
			}
			else {
				$b = ord($data[$i]);
				$g = ord($data[$i + 1]);
				$r = ord($data[$i + 2]);
			}
			// the client draws near magenta as transparent too
			if ($r >= 0xf8 && $g <= 0x07 && $b >= 0xf8) {
				imagesetpixel($img, $x, $y, $transparent);
			}
			else {
				imagesetpixel($img, $x, $y, ($r << 16) | ($g << 8) | $b);
			}
		}
	}
	return $img;
}

// Writes the item's pictures; returns the kinds written
function extract_item_pictures($id, $resource_name, $grf_files, $only_missing) {
	global $item_picture_dirs;
	$written = array();
	$name = strtolower($resource_name);
	foreach ($item_picture_dirs as $kind => $dir) {
		$file = "$dir/$id.png";
		if (($only_missing && file_exists($file)) || !isset($grf_files[$kind][$name])) {
			continue;
		}
		$path = Client::getFile($grf_files[$kind][$name]);
		if ($path === false) {
			continue;
		}
		$img = item_bmp_image(file_get_contents($path));
		if ($img && imagepng($img, $file)) {
			$written[] = $kind;
		}
		if ($img) {
			imagedestroy($img);
		}
	}
	return $written;
}

$extracted = file_exists($item_extracted_file) ? array_map('intval', preg_split('/\s+/', trim(file_get_contents($item_extracted_file)), -1, PREG_SPLIT_NO_EMPTY)) : array();

if ($POST_action == "Extract missing pictures" || $POST_action == "Extract extracted pictures again" || $POST_action == "Extract") {
	foreach ($item_picture_dirs as $dir) {
		if (!is_dir($dir) || !is_writable($dir) || (file_exists($item_extracted_file) ? !is_writable($item_extracted_file) : !is_writable(dirname($item_extracted_file)))) {
			redir("item_pictures.php", "The web server cannot write to $dir, give it write access first.");
		}
	}
	$names = item_resource_names();
	$grf_files = item_grf_files();
	if ($POST_action == "Extract") {
		$ids = isset($names[(int)$POST_item]) ? array((int)$POST_item) : array();
		if (count($ids) == 0) {
			redir("item_pictures.php", "The client does not know item " . (int)$POST_item . ".");
		}
	}
	elseif ($POST_action == "Extract extracted pictures again") {
		$ids = array_values(array_intersect($extracted, array_keys($names)));
	}
	else {
		$ids = array_keys($names);
	}
	// A run takes minutes, so it is done in batches of under a minute that continue from the next item
	$from = isset($POST_from) ? (int)$POST_from : 0;
	$pictures = isset($POST_pictures) ? (int)$POST_pictures : 0;
	$items = isset($POST_items) ? (int)$POST_items : 0;
	$started = microtime(true);
	$next = false;
	$made = array();
	foreach ($ids as $id) {
		if ($id < $from) {
			continue;
		}
		if (microtime(true) - $started > 45) {
			$next = $id;
			break;
		}
		$written = extract_item_pictures($id, $names[$id], $grf_files, $POST_action == "Extract missing pictures");
		if (count($written) > 0) {
			$made[] = $id;
			$pictures += count($written);
		}
	}
	$items += count($made);
	$extracted = array_unique(array_merge($extracted, $made));
	sort($extracted);
	file_put_contents($item_extracted_file, implode("\n", $extracted) . "\n");
	if ($next !== false) {
		$done = count(array_filter($ids, function ($id) use ($next) { return $id < $next; }));
		echo "<div class=\"homePage\"><div class=\"homeSection\"><div class=\"mytitle homeTitle\">Item Pictures</div>
			<div class=\"newsItem\">Extracting: $done of " . count($ids) . " items checked, $pictures pictures so far. This page continues by itself.</div>
			<form id=\"next\" action=\"item_pictures.php\" method=\"POST\" class=\"accountForm\">
				<input type=\"hidden\" name=\"action\" value=\"" . html_escape($POST_action) . "\">
				<input type=\"hidden\" name=\"from\" value=\"$next\">
				<input type=\"hidden\" name=\"pictures\" value=\"$pictures\">
				<input type=\"hidden\" name=\"items\" value=\"$items\">
				<noscript><input type=\"submit\" class=\"myctl\" value=\"Continue\"></noscript>
			</form>
			<script>document.getElementById('next').submit();</script>
		</div></div>\n";
		require 'footer.inc';
		exit;
	}
	add_admin_entry("Extracted item pictures: $pictures pictures of $items items");
	redir("item_pictures.php", "$pictures pictures extracted for $items items.");
}

// What the client has and what is missing
$names = item_resource_names();
$grf_files = item_grf_files();
$count = array('images' => 0, 'icons' => 0);
$in_grf = array('images' => 0, 'icons' => 0);
$missing = array();
foreach ($names as $id => $name) {
	$lower = strtolower($name);
	foreach ($item_picture_dirs as $kind => $dir) {
		if (file_exists("$dir/$id.png")) {
			$count[$kind]++;
		}
		elseif (isset($grf_files[$kind][$lower])) {
			$in_grf[$kind]++;
		}
		elseif ($kind == 'icons') {
			$missing[] = $id;
		}
	}
}
$info_files = item_info_files();
echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Item Pictures</div>
		<div class=\"newsItem\">The item pictures and inventory icons on the item lists, inventories and storage, extracted
			from the game client (the GRFs in ROChargenPHP/client/DATA.INI). The item names come from the GRFs'
			idnum2itemresnametable.txt and, for newer clients, from System/itemInfo.lub next to a GRF
			(" . (count($info_files) > 0 ? html_escape(implode(", ", array_map('basename', $info_files))) . " found" : "none found") . ").
			Extracting the extracted pictures again leaves the ones that came with the panel as they are.</div>
		<div class=\"newsItem\">The client knows " . count($names) . " items. Pictures: {$count['images']}, "
			. "{$in_grf['images']} more in the GRFs. Icons: {$count['icons']}, {$in_grf['icons']} more in the GRFs. "
			. count($missing) . " items have no icon in the panel or the GRFs. " . count($extracted) . " items were extracted by this tool.</div>
		<form action=\"item_pictures.php\" method=\"POST\" class=\"accountForm\">
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Extract missing pictures\">
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Extract extracted pictures again\">
		</form>
		<form action=\"item_pictures.php\" method=\"POST\" class=\"accountForm\">
			Item id <input type=\"text\" name=\"item\" class=\"myctl\" size=\"8\">
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Extract\">
		</form>
	</div>
</div>
";

require 'footer.inc';
?>
