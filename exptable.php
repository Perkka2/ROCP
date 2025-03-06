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
  $i = 1;
  $expNameTable = array(
    "ExpParameterExp" => "Base Exp",
    "ExpParameter2Exp" => "Rebirth Base Exp",
    "NoviceJobExpParameterExp" => "Novice Job Exp",
    "FirstJobExpParameterExp" => "First Job Exp",
    "SecondJobExpParameterExp" => "Second Job Exp",
    "NoviceJobExpParameter2Exp" => "Rebirth Novice Job Exp",
    "FirstJobExpParameter2Exp" => "Rebirth First Job Exp",
    "SecondJobExpParameter2Exp" => "Rebirth Second Job Exp",
    "FirstJobExpParameter3Exp" => "Gunslinger/Ninja Exp",
    "ThirdJobExpParameterExp" => "Third Job Exp",
    "TribeExp" => "Doram Exp"
  );

	while ($line = $result->FetchRow()){
    $exptables[$i++] = $line;
  }
  $fields = $result->FieldCount();
  for($y=1; $y < $fields; $y++){
    $column = $result->fetchField($y);
    $array = get_object_vars($column);
    echo "<a href=\"#" . $expNameTable[$array['name']] . "\">" . $expNameTable[$array['name']] . "</a><br>";
    $tablename = $expNameTable[$array['name']];
    echo "<tr class=\"contentRowHeader exptable\" id=\"".$expNameTable[$array['name']]."\"><td>Level</td><td>$tablename</td></tr>";
    $i = 1;
    while ($exptables[$i++][$y]) {
      $line = $exptables[$i-1];
      echo "<tr><td>".($i-1)."</td><td>".$exptables[$i-1][$y]."</td></tr>";
    }
  }
}
echo "</table>";
require 'footer.inc';
?>
