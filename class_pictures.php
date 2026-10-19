<?php
// Admin tool: draws the class pictures (images/classes/<job>.png, the male and female character side by
// side) from the game sprites with ROChargen, for the classes that have none or again for all of them.
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
set_time_limit(300);

$picture_dir = "images/classes";
// the pictures this tool drew; the others came with the panel and are only redrawn one at a time
$drawn_file = "$picture_dir/drawn.txt";
$drawn = file_exists($drawn_file) ? array_map('intval', preg_split('/\s+/', trim(file_get_contents($drawn_file)), -1, PREG_SPLIT_NO_EMPTY)) : array();
// classes that only exist for one gender
$male_only = array(19, 4020, 4042, 4068, 4075, 4104, 4211, 4223);
$female_only = array(20, 4021, 4043, 4069, 4076, 4105, 4212, 4224);
// baby classes and their adult class: the client draws a baby with the adult sprites shrunk to 2/3
$babies = array(4023 => 0, 4024 => 1, 4025 => 2, 4026 => 3, 4027 => 4, 4028 => 5, 4029 => 6, 4030 => 7,
	4031 => 8, 4032 => 9, 4033 => 10, 4034 => 11, 4035 => 12, 4036 => 13, 4037 => 14, 4038 => 15, 4039 => 16,
	4040 => 17, 4041 => 18, 4042 => 19, 4043 => 20, 4044 => 21, 4045 => 23, 4096 => 4054, 4097 => 4055,
	4098 => 4056, 4099 => 4057, 4100 => 4058, 4101 => 4059, 4102 => 4066, 4103 => 4067, 4104 => 4068,
	4105 => 4069, 4106 => 4070, 4107 => 4071, 4108 => 4072, 4109 => 4080, 4110 => 4082, 4111 => 4084,
	4112 => 4086, 4191 => 4190, 4220 => 4218, 4222 => 25, 4223 => 4211, 4224 => 4212, 4225 => 4046, 4226 => 4047,
	4227 => 4049, 4228 => 24, 4229 => 4215, 4238 => 4048);

// All classes, by id
$classes = array();
foreach (file("class_by_id.def") as $line) {
	$parts = explode("\t", trim($line));
	if (count($parts) == 2 && ctype_digit($parts[0])) {
		$classes[(int)$parts[0]] = $parts[1];
	}
}

// ROChargen, loaded the way its own index.php does, but drawing here instead of over HTTP: its
// address cuts the picture to 128x128, which cuts the heads off tall mounted characters
function rochargen_start() {
	static $started = false;
	if ($started) {
		return;
	}
	$started = true;
	define('__ROOT__', dirname(__FILE__) . '/ROChargenPHP/');
	require_once(__ROOT__ . 'core/class.Debug.php');
	require_once(__ROOT__ . 'core/class.Controller.php');
	require_once(__ROOT__ . 'core/class.Cache.php');
	require_once(__ROOT__ . 'core/class.Client.php');
	require_once(__ROOT__ . 'core/class.DB.php');
	require_once(__ROOT__ . 'render/class.CharacterRender.php');
	Cache::$time = 0;
	Client::$path = __ROOT__ . "client/";
	Client::$data_ini = "DATA.INI";
	Client::$AutoExtract = false;
	DB::$path = __ROOT__ . "db/";
	Client::init();
}

// The character drawn by ROChargen, trimmed to its pixels, or false when there is no body to draw
function draw_class($job, $sex) {
	rochargen_start();
	$chargen = new CharacterRender();
	$chargen->action = 0;
	$chargen->direction = 0;
	$chargen->body_animation = 0;
	$chargen->sex = $sex;
	$chargen->class = $job;
	$chargen->body = 0;
	$chargen->clothes_color = 0;
	$chargen->hair = 2;
	$chargen->hair_color = 0;
	$chargen->doridori = 0;
	$chargen->head_top = 0;
	$chargen->head_mid = 0;
	$chargen->head_bottom = 0;
	$chargen->weapon = 0;
	$chargen->shield = 0;
	$chargen->robe = 0;
	$chargen->option = 0;
	$img = @$chargen->render();
	if (!$img) {
		return false;
	}
	$w = imagesx($img);
	$h = imagesy($img);
	$x0 = $w; $y0 = $h; $x1 = -1; $y1 = -1;
	for ($y = 0; $y < $h; $y++) {
		for ($x = 0; $x < $w; $x++) {
			if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) < 120) {
				$x0 = min($x0, $x); $y0 = min($y0, $y); $x1 = max($x1, $x); $y1 = max($y1, $y);
			}
		}
	}
	if ($x1 < 0 || ($y1 - $y0) < 20) {
		return false; // nothing, or only a shadow, was drawn
	}
	// a body must be drawn in the middle of the figure (without a body sprite only the head and the shadow are)
	$solid = 0;
	for ($y = $y0 + (int)(($y1 - $y0) * .45); $y <= $y0 + (int)(($y1 - $y0) * .65); $y++) {
		for ($x = $x0; $x <= $x1; $x++) {
			if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) < 30) {
				$solid++;
			}
		}
	}
	if ($solid < 20) {
		return false;
	}
	return array($img, $x0, $y0, $x1 - $x0 + 1, $y1 - $y0 + 1);
}

// Draws images/classes/<job>.png; returns false when the client has no sprites for the class
function make_class_picture($job) {
	global $picture_dir, $male_only, $female_only, $babies;
	rochargen_start();
	$baby = isset($babies[$job]);
	$sprites = $baby ? $babies[$job] : $job;
	// classes ROChargen has no body for would be drawn as a Novice (the Doram have their own sprites).
	// Asked through ROChargen: it loads its body list with require_once, so loading the list here as
	// well would leave it without one.
	if ($sprites != 0 && $sprites != 4218 && DB::get_body_path($sprites, 'M', 0) == DB::get_body_path(0, 'M', 0)) {
		return false;
	}
	$male = in_array($job, $female_only) ? false : draw_class($sprites, 'M');
	$female = in_array($job, $male_only) ? false : draw_class($sprites, 'F');
	$parts = array_values(array_filter(array($male, $female)));
	if (count($parts) == 0) {
		return false;
	}
	$out = imagecreatetruecolor(118, 111);
	imagealphablending($out, false);
	imagesavealpha($out, true);
	imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
	imagealphablending($out, true);
	$slot = 118 / count($parts);
	foreach ($parts as $i => $part) {
		list($img, $x, $y, $w, $h) = $part;
		$scale = min(($slot - 2) / $w, 107 / $h, 1.4) * ($baby ? 2 / 3 : 1);
		$dw = (int)round($w * $scale);
		$dh = (int)round($h * $scale);
		imagecopyresampled($out, $img, (int)round($i * $slot + ($slot - $dw) / 2), 111 - 2 - $dh, $x, $y, $dw, $dh, $w, $h);
	}
	return imagepng($out, "$picture_dir/$job.png");
}

if ($POST_action == "Draw missing pictures" || $POST_action == "Redraw drawn pictures" || $POST_action == "Redraw") {
	if (!is_writable($picture_dir)) {
		redir("class_pictures.php", "The web server cannot write to $picture_dir, give it write access first.");
	}
	if ($POST_action == "Redraw") {
		$jobs = isset($classes[(int)$POST_job]) ? array((int)$POST_job) : array();
	}
	elseif ($POST_action == "Redraw drawn pictures") {
		$jobs = array_values(array_intersect($drawn, array_keys($classes)));
	}
	else {
		$jobs = array_keys($classes);
	}
	$made = array();
	$no_sprites = array();
	foreach ($jobs as $job) {
		if ($POST_action == "Draw missing pictures" && file_exists("$picture_dir/$job.png")) {
			continue;
		}
		if (make_class_picture($job)) {
			$made[] = $job;
		}
		else {
			$no_sprites[] = $job;
		}
	}
	$drawn = array_unique(array_merge($drawn, $made));
	sort($drawn);
	file_put_contents($drawn_file, implode("\n", $drawn) . "\n");
	add_admin_entry("Drew class pictures: " . count($made) . " made");
	$message = count($made) . " class pictures drawn.";
	if (count($no_sprites) > 0) {
		$message .= " " . count($no_sprites) . " classes have no sprites in the client.";
	}
	redir("class_pictures.php", $message);
}

// The classes and their pictures
$missing = 0;
foreach ($classes as $job => $name) {
	if (!file_exists("$picture_dir/$job.png")) {
		$missing++;
	}
}
echo "<div class=\"homePage\">
	<div class=\"homeSection\"><div class=\"mytitle homeTitle\">Class Pictures</div>
		<div class=\"newsItem\">The pictures of the classes on Server Information, drawn from the game client sprites
			(the GRF in ROChargenPHP/client/DATA.INI). " . count($classes) . " classes, $missing without a picture.
			Redrawing the drawn pictures leaves the ones that came with the panel as they are.</div>
		<form action=\"class_pictures.php\" method=\"POST\" class=\"accountForm\">
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Draw missing pictures\">
			<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Redraw drawn pictures\">
		</form>
	</div>
	<div class=\"homeSection\"><div class=\"classCards\">";
foreach ($classes as $job => $name) {
	$file = "$picture_dir/$job.png";
	$picture = file_exists($file)
		? "<img class=\"classPicture\" src=\"$file?" . filemtime($file) . "\" alt=\"\">"
		: "<span class=\"classPicture\"></span>";
	echo "
		<div class=\"classCard\">$picture
			<div>
				<div class=\"charName\">" . html_escape($name) . "</div>
				<div class=\"charClass\">Class $job" . (!file_exists($file) ? " &middot; no picture" : (in_array($job, $drawn) ? " &middot; drawn" : "")) . "</div>
				<form action=\"class_pictures.php\" method=\"POST\">
					<input type=\"hidden\" name=\"job\" value=\"$job\">
					<input type=\"submit\" name=\"action\" class=\"myctl\" value=\"Redraw\">
				</form>
			</div>
		</div>";
}
echo "</div></div>
</div>
";

require 'footer.inc';
?>
