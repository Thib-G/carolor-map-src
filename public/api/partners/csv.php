<?php

include('../inc/conn.inc.php');
$res = $mysqli->query("SELECT * FROM mod248_mmp_markers");

$filename = 'partners.csv';

// file creation
$file = fopen($filename,"w");

$data = array();

while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

$data_utf8 = utf8ize($data);

foreach ($data_utf8 as $line){
 fputcsv($file, $line);
}

fclose($file);

// download
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$filename);
header("Content-Type: application/csv; "); 

readfile($filename);

// deleting file
unlink($filename);
exit();

function utf8ize($d) {
  if (is_array($d)) {
      foreach ($d as $k => $v) {
          $d[$k] = utf8ize($v);
      }
  } else if (is_string ($d)) {
      return utf8_encode($d);
  }
  return $d;
}
