<?php
include('../search_includes.php');

// Get parameters
$query = sanitize_query($_POST['q']);
$encoded_query = encode_query($query);
$api = sanitize_query($_POST['a']);
if ($api == 'widget' || $api == 'atoz') {
  // Get results
  $html_result = new Springshare($encoded_query, $api);
  echo $html_result->data;
}
else {
  echo 'Invalid api. (' . $api . ')';
}

?>