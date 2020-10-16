<?php
include('../search_includes.php');
require_once(LIBRARY_SEARCH . '/recommended_resources/recommendation_functions.php');

// Get query
$query = urlencode(filter_var($_POST['q'], FILTER_SANITIZE_STRING));

// Get topic codes (truncated Library of Congress classifications)
$topic = get_topic($query);
if (!empty($topic)) {
  // Get recommended resources for found topics
  $recommendations = get_recommended_resources($topic);
  if (!empty($recommendations)) {
    echo print_recommended_resources($recommendations);
  }
}
else {
  echo 'No topics found for this keyword.';
}
?>