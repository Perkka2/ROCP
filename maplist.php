<?php
require 'memory.php';
require 'header.inc';
check_auth($_SERVER['PHP_SELF']); // checks for required access
echo "<table class=\"contentTable maplist\"><tr>";
if ($CONFIG_server_type == 0) {
  $mapName = ParseMapNameTable("./dbtranslation/mapnametable.txt");
  $query = sprintf(GET_ALL_MAPS);
  $result = execute_query($query, "exptable.php");
  $mapnumber = 0;
	while ($line = $result->FetchRow()){
    if($mapcheck != $line[0]){
      $mapnumber++;
      $mapcheck = $line[0];
    }
    if (($GET_zone == $mapnumber)|| (!isset($GET_zone) && $mapnumber == 1)){
    $showmaps .= "<tr><td>Zone Server $mapnumber</td><td><img src=\"images/maps/".str_replace(".gat", "", $line[1]).".png\" onerror=\"this.onerror=''; this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';\"/></td><td>".$mapName[$line[1]]." ($line[1])</td></tr>";
  }
  }
}
$i = 1;
while ($i <= $mapnumber){
  if (($i+2) % 3 == 0) {
    echo "</tr><tr>";
  }
  echo "<td><a href=\"?zone=$i\">Zone Server $i</a></td>";
  $i++;
}
echo "</tr><tr class=\"contentRowHeader maplist\"><td>Server</td><td>Map Thumbnail</td><td>Map Name</td></tr>
    $showmaps";
echo "</table>";
require 'footer.inc';
?>
