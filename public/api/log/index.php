<?php
  /* ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL); */

  header('Content-type: application/json');
  include('../inc/conn.inc.php');

  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $remote_addr = $_SERVER['REMOTE_ADDR'];
  $http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

  // https://stackoverflow.com/questions/18866571/receive-json-post-with-php
  $_POST = json_decode(file_get_contents('php://input'), true);
  $lng = (float) $_POST['lng'];
  $lat = (float) $_POST['lat'];
  $radius = (float) $_POST['radius'];

  $stmt = $mysqli->prepare("INSERT INTO maps_log (user_agent, remote_addr, http_accept_language, lng, lat, radius) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('sssddd', $user_agent, $remote_addr, $http_accept_language, $lng, $lat, $radius);
  $stmt->execute();
  $stmt->close();

  echo json_encode(array('status' => 'finished'));

?>
