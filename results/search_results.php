<?php
include('../search_includes.php');

// Get parameters
$vendor = $_POST['vendor'];
$api = $_POST['api'];
$database = $_POST['db'];
$format = $_POST['format'];
$location = $_POST['loc'];
$type = $_POST['type'];
$query = sanitize_query($_POST['q']);
$encoded_query = encode_query($query);

// Get results
switch ($vendor) {
  case 'Credo':
    $results = new Credo($encoded_query, $api, $type);
  break;
  case 'Ebsco':
    $results = new Ebsco($encoded_query, $api, $database);
  break;
  case 'ExLibris':
    $results = new ExLibris($encoded_query, $api, $format, $location);
  break;
  case 'Gale':
    $results = new Gale($encoded_query, $api, $database);
  break;
  case 'Infobase':
    $results = new Infobase($encoded_query, $api, $database);
  break;
  case 'NewsAPI':
    $results = new NewsAPI($encoded_query, $api);
  break;
  case 'ProQuest':
    $results = new ProQuest($encoded_query, $api, $database);
  break;
  case 'Statista':
    $results = new Statista($encoded_query, $api);
  break;
  default:
    // Do nothing
  break;
}

// Print results
if ($results) {
  echo $results->print_results();
}

?>