<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access

if ($CONFIG_server_type != 0) {
	EchoHead(100);
	echo "
	<tr class=\"mycell\">
		<td>The experience table is only available for Aegis servers.</td>
	</tr>
</table>
	";
	require 'footer.inc';
	exit();
}

// Column names from GET_ALL_EXP (depends on $CONFIG['aegis_version']), in display order
$exp_names = array(
	"ExpParameterExp" => "Base Exp",
	"ExpParameter2Exp" => "Rebirth Base Exp",
	"ExpParameter3Exp" => "Third Job Base Exp",
	"TribeExp" => "Doram Exp",
	"NoviceJobExpParameterExp" => "Novice Job Exp",
	"FirstJobExpParameterExp" => "First Job Exp",
	"SecondJobExpParameterExp" => "Second Job Exp",
	"NoviceJobExpParameter2Exp" => "Rebirth Novice Job Exp",
	"FirstJobExpParameter2Exp" => "Rebirth First Job Exp",
	"SecondJobExpParameter2Exp" => "Rebirth Second Job Exp",
	"FirstJobExpParameter3Exp" => "Gunslinger/Ninja Exp",
	"ThirdJobExpParameterExp" => "Third Job Exp"
);

// One row per level, one column per experience table. The number of levels comes from
// the tables themselves (99 on old servers, up to 260 base / 55 job levels on newer ones).
$query = query_format(GET_ALL_EXP);
$result = execute_query_union($query, "exptable.php");
$columns = array();
for ($i = 1; $i < $result->FieldCount(); $i++) {
	$field = $result->FetchField($i);
	$columns[$i] = $field->name;
}
$levels = array();
$used = array();
while ($line = $result->FetchRow()) {
	$levels[intval($line[0])] = $line;
	foreach ($columns as $i => $name) {
		if ($line[$i] !== null && $line[$i] !== "") {
			$used[$i] = true;
		}
	}
}
ksort($levels);
// Only tables this server has, in the order of $exp_names
$shown = array();
foreach ($exp_names as $name => $label) {
	$i = array_search($name, $columns);
	if ($i !== false && isset($used[$i])) {
		$shown[$i] = $label;
	}
}
foreach ($columns as $i => $name) {
	if (isset($used[$i]) && !isset($shown[$i])) {
		$shown[$i] = $name;
	}
}

// Newer databases also have experience per job: TribeExp (base) and JobExp (job)
$job_tables = array("TribeExp" => array("Base exp per job", "base exp"), "JobExp" => array("Job exp per job", "job exp"));
$job_names = array(
	"DO_SUMMONER" => "Doram Summoner",
	"SUPERNOVICE" => "Super Novice",
	"SUPERNOVICE2" => "Expanded Super Novice",
	"STAR" => "Star Gladiator",
	"STAR_EMPEROR" => "Star Emperor",
	"SOUL_REAPER" => "Soul Reaper"
);
$job_options = array();
foreach ($job_tables as $table => $names) {
	$group = $names[0];
	$result = execute_query(query_format(EXP_TABLE_EXISTS, $table), "exptable.php");
	if (!$result->fields[0]) {
		continue;
	}
	$result = execute_query(query_format(GET_JOB_EXP_JOBS, $table), "exptable.php");
	while ($line = $result->FetchRow()) {
		$job = $line[0];
		// _B are the baby classes
		$baby = substr($job, -2) == "_B";
		$base_job = $baby ? substr($job, 0, -2) : $job;
		$name = isset($job_names[$base_job]) ? $job_names[$base_job] : ucwords(strtolower(str_replace("_", " ", $base_job)));
		$job_options[$group]["$table.$job"] = array($table, $job, $name . ($baby ? " (Baby)" : "") . " - " . $names[1]);
	}
}

// ?type= shows one table with the total experience needed to reach each level:
// a column of the table above, or a job of the per job tables (TribeExp.DO_SUMMONER)
$type = isset($_GET['type']) ? $_GET['type'] : "";
$single = false;
$single_rows = array();
foreach ($shown as $i => $label) {
	if ($columns[$i] === $type) {
		$single = $type;
		foreach ($levels as $level => $line) {
			if ($line[$i] !== null && $line[$i] !== "") {
				$single_rows[$level] = $line[$i];
			}
		}
	}
}
foreach ($job_options as $group => $options) {
	if (isset($options[$type])) {
		$single = $type;
		$result = execute_query(query_format(GET_JOB_EXP, $options[$type][0], $options[$type][1]), "exptable.php");
		while ($line = $result->FetchRow()) {
			$single_rows[intval($line[0])] = $line[1];
		}
	}
}

// Experience needed at $level to reach the next level, from the first table in $keys that has it.
// A key is a column of the table above (ExpParameterExp) or a per job table (TribeExp.DO_SUMMONER).
function exp_for_level($keys, $level) {
	global $levels, $columns, $job_options, $job_exp_cache;
	foreach ($keys as $key) {
		if (strpos($key, ".") === false) {
			$i = array_search($key, $columns);
			if ($i !== false && isset($levels[$level]) && $levels[$level][$i] !== null && $levels[$level][$i] !== "") {
				return array($key, (float)$levels[$level][$i]);
			}
			continue;
		}
		$found = false;
		foreach ($job_options as $options) {
			if (isset($options[$key])) {
				$found = $options[$key];
			}
		}
		if (!$found) {
			continue;
		}
		if (!isset($job_exp_cache[$key])) {
			$job_exp_cache[$key] = array();
			$result = execute_query(query_format(GET_JOB_EXP, $found[0], $found[1]), "exptable.php");
			while ($line = $result->FetchRow()) {
				$job_exp_cache[$key][intval($line[0])] = (float)$line[1];
			}
		}
		if (isset($job_exp_cache[$key][$level])) {
			return array($key, $job_exp_cache[$key][$level]);
		}
	}
	return false;
}

// Tables a class levels with: array(base tables, job tables), the first table that has the level is used
function exp_tables_of_class($job) {
	$base = array("ExpParameterExp");
	$rebirth = array("ExpParameter2Exp", "ExpParameterExp");
	$third = array("ExpParameter3Exp", "ExpParameterExp");
	$third_rebirth = array("ExpParameter3Exp", "ExpParameter2Exp", "ExpParameterExp");
	$first = array("FirstJobExpParameterExp");
	$second = array("SecondJobExpParameterExp");
	$third_job = array("ThirdJobExpParameterExp");
	$gunslinger_ninja = array("FirstJobExpParameter3Exp", "FirstJobExpParameterExp");
	if ($job == 0 || $job == 4023) return array($base, array("NoviceJobExpParameterExp"));
	if (($job >= 1 && $job <= 6) || ($job >= 4024 && $job <= 4029) || $job == 4046 || $job == 4225) return array($base, $first);
	if (($job >= 7 && $job <= 21) || ($job >= 4030 && $job <= 4044) || $job == 4049 || $job == 4227) return array($base, $second);
	if ($job == 23) return array($base, array("JobExp.SUPERNOVICE", "SecondJobExpParameterExp"));
	if ($job == 4045) return array($base, array("JobExp.SUPERNOVICE_B", "JobExp.SUPERNOVICE", "SecondJobExpParameterExp"));
	if ($job == 24 || $job == 25 || $job == 4222 || $job == 4228) return array($base, $gunslinger_ninja);
	if ($job == 4047 || $job == 4048) return array($base, array("JobExp.STAR", "SecondJobExpParameterExp"));
	if ($job == 4226 || $job == 4238) return array($base, array("JobExp.STAR_B", "JobExp.STAR", "SecondJobExpParameterExp"));
	if ($job == 4001) return array($rebirth, array("NoviceJobExpParameter2Exp"));
	if ($job >= 4002 && $job <= 4007) return array($rebirth, array("FirstJobExpParameter2Exp"));
	if ($job >= 4008 && $job <= 4022) return array($rebirth, array("SecondJobExpParameter2Exp"));
	$third_rebirth_jobs = array(4060, 4061, 4062, 4063, 4064, 4065, 4073, 4074, 4075, 4076, 4077, 4078, 4079, 4081, 4083, 4085, 4087);
	if (in_array($job, $third_rebirth_jobs)) return array($third_rebirth, $third_job);
	if (($job >= 4054 && $job <= 4087) || ($job >= 4096 && $job <= 4112)) return array($third, $third_job);
	if ($job == 4190) return array($third, array("JobExp.SUPERNOVICE2"));
	if ($job == 4191) return array($third, array("JobExp.SUPERNOVICE2_B", "JobExp.SUPERNOVICE2"));
	if ($job == 4211) return array($third, array("JobExp.KAGEROU"));
	if ($job == 4212) return array($third, array("JobExp.OBORO"));
	if ($job == 4223) return array($third, array("JobExp.KAGEROU_B", "JobExp.KAGEROU"));
	if ($job == 4224) return array($third, array("JobExp.OBORO_B", "JobExp.OBORO"));
	if ($job == 4215) return array($third, array("JobExp.REBELLION"));
	if ($job == 4229) return array($third, array("JobExp.REBELLION_B", "JobExp.REBELLION"));
	if ($job == 4218) return array(array("TribeExp.DO_SUMMONER"), array("JobExp.DO_SUMMONER"));
	if ($job == 4220) return array(array("TribeExp.DO_SUMMONER_B", "TribeExp.DO_SUMMONER"), array("JobExp.DO_SUMMONER_B", "JobExp.DO_SUMMONER"));
	return array(array(), array());
}

// "50 (43%)" with a link to the table it was measured against
function exp_progress($keys, $level, $exp) {
	$level = intval($level);
	$needed = exp_for_level($keys, $level);
	if (!$needed || $needed[1] <= 0) {
		return $level;
	}
	$percent = min(100, floor((float)$exp * 100 / $needed[1]));
	return "<a href=\"exptable.php?type=" . html_escape($needed[0]) . "\" title=\"" . number_format((float)$exp) . " / "
	. number_format($needed[1]) . "\">$level ($percent%)</a>";
}

EchoHead(100);
echo "
	<tr class=\"mytitle\">
		<td colspan=\"3\">{$lang['page']['exptable.php']}</td>
	</tr>
	<tr class=\"mycell\">
		<td colspan=\"3\">
			<form action=\"exptable.php\" method=\"get\">
				Show: <select name=\"type\" class=\"myctl\" onchange=\"this.form.submit();\">
					<option value=\"\">All tables</option>
";
foreach ($shown as $i => $label) {
	$selected = $single === $columns[$i] ? " selected=\"selected\"" : "";
	echo "<option value=\"" . html_escape($columns[$i]) . "\"$selected>" . html_escape($label) . "</option>\n";
}
foreach ($job_options as $group => $options) {
	echo "<optgroup label=\"" . html_escape($group) . "\">\n";
	foreach ($options as $value => $option) {
		$selected = $single === $value ? " selected=\"selected\"" : "";
		echo "<option value=\"" . html_escape($value) . "\"$selected>" . html_escape($option[2]) . "</option>\n";
	}
	echo "</optgroup>\n";
}
echo "
				</select>
				<input type=\"submit\" class=\"myctl\" value=\"Show\" />
			</form>
		</td>
	</tr>
</table>
";

// The characters of the logged in account, with their progress to the next level
$job_exp_cache = array();
$result = execute_query(query_format(EXP_TABLE_CHARS, $STORED_id), "exptable.php");
if ($result->RowCount() > 0) {
	echo "<table class=\"contentTable exptable\">\n";
	echo "<tr class=\"contentRowHeader exptable\"><td>Your characters</td><td>Class</td><td align=\"right\">Base level</td><td align=\"right\">Job level</td></tr>\n";
	while ($line = $result->FetchRow()) {
		$tables = exp_tables_of_class(intval($line[1]));
		echo "<tr><td>" . html_escape($line[0]) . "</td><td>" . html_escape(determine_class($line[1])) . "</td>"
		. "<td align=\"right\">" . exp_progress($tables[0], $line[2], $line[4]) . "</td>"
		. "<td align=\"right\">" . exp_progress($tables[1], $line[3], $line[5]) . "</td></tr>\n";
	}
	echo "</table>\n<br />\n";
}

// The column header is repeated every 25 levels, so long tables stay readable
// (a sticky header would show the rows through the skins' see-through header backgrounds)
if ($single !== false) {
	$header = "<tr class=\"contentRowHeader exptable\"><td>Level</td><td align=\"right\">Exp to next level</td><td align=\"right\">Total to reach level</td></tr>\n";
}
else {
	$header = "<tr class=\"contentRowHeader exptable\"><td>Level</td>";
	foreach ($shown as $i => $label) {
		$header .= "<td align=\"right\">" . html_escape($label) . "</td>";
	}
	$header .= "</tr>\n";
}
echo "<table class=\"contentTable exptable\">\n";
$row = 0;
if ($single !== false) {
	$total = 0.0;
	ksort($single_rows);
	foreach ($single_rows as $level => $exp) {
		if ($row++ % 25 == 0) {
			echo $header;
		}
		echo "<tr><td>$level</td><td align=\"right\">" . number_format((float)$exp) . "</td><td align=\"right\">" . number_format($total) . "</td></tr>\n";
		$total += (float)$exp;
	}
}
else {
	foreach ($levels as $level => $line) {
		if ($row++ % 25 == 0) {
			echo $header;
		}
		echo "<tr><td>$level</td>";
		foreach ($shown as $i => $label) {
			$exp = $line[$i];
			echo "<td align=\"right\">" . ($exp === null || $exp === "" ? "" : number_format((float)$exp)) . "</td>";
		}
		echo "</tr>\n";
	}
}
echo "</table>\n";
require 'footer.inc';
?>
