<?php
include('../search_includes.php');

// Declare no results message
$no_results = 'No Did You Mean suggestions for this keyword.';

// Get parameters
$tab = $_POST['t'];
$tabs = get_tabs();
if (!isset($tabs[$tab])) {
  die($no_results);
}
$query = sanitize_query($_POST['q']);
$encoded_query = encode_query($query);

// Get results
$results = new ExLibris($encoded_query, 'XService', null, null);
if (empty($results->data)) {
  echo $no_results;
}
else {
  if ($results->result_count > 0) {
    $suggestion = $results->data[0]['dym'];
    echo '<p>Did you intend to search for: <a href="' . LIBRARY_SEARCH_URL . '/index.php?t=' . $tab . '&q=' . urlencode($suggestion) . '">' . $suggestion . '</a>?</p>';
  }
  else {
    echo $no_results;
  }
}

?>