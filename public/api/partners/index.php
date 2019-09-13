<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");

header('Content-type: application/json');
include('../inc/conn.inc.php');

$res = $mysqli->query("SELECT * FROM mod248_mmp_markers");

$data = array();

while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode(utf8ize($data));

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

?>
