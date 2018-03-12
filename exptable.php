<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
echo "<table class=\"contentTable\">";
if ($CONFIG_server_type == 0) {
  $query = sprintf(GET_ALL_EXP);
  //$query = sprintf(GET_CHARACTER_ITEMS, 100012);
  //echo $query;
  $result = execute_query_union($query, "exptable.php");
  echo "<tr class=\"contentRowHeader exptable\"><td>Level</td><td>Base Exp</td><td>Novice Job Exp</td><td>1st Job Exp</td><td>2nd Job Exp</td><td>Supernovice</td></tr>";
  $i = 1;
	while ($line = $result->FetchRow()){
    $exptables[$i++] = $line;
  }
  $i = 1;
  while ($exptables[$i++][1]) {
    $line = $exptables[$i-1];
    $mainlvl .= "<tr><td>$line[0]</td><td>$line[1]</td><td>$line[4]</td><td>$line[5]</td><td>$line[6]</td><td>$line[10]</td></tr>";
  }
  $i = 1;
  while ($exptables[$i++][0]||$exptables[$i-99][11]) {
    $line = $exptables[$i-1];
    if($line[0]==100){
    $rebirthlvl .= "<tr class=\"contentRowHeader exptable\"><td>After 3rd Job</td><td>Base Exp</td><td>Job Level</td><td>Job Exp</td><td></td><td></td></tr>";
    }
    $rebirthlvl .= "<tr><td>$line[0]</td><td>$line[2]$line[3]</td><td>$line[7]{$exptables[$i-100][0]}</td><td>$line[8]{$exptables[$i-100][11]}</td><td>$line[9]</td><td></td></tr>";
  }

  echo $mainlvl;
  echo "<tr class=\"contentRowHeader exptable\"><td>Rebirth Level</td><td>Base Exp</td><td>Novice Job Exp</td><td>1st Job Exp</td><td>2nd Job Exp</td><td></td></tr>";;
  echo $rebirthlvl;
}
echo "</table>";
require 'footer.inc';
?>
