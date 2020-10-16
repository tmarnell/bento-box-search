<?php
include('../search_includes.php');

// Get parameters
$query = sanitize_query($_POST['q']);
$encoded_query = encode_query($query);

// Get results
$results = new Credo($encoded_query, 'Credo', 'topics');

// Print results
if (!empty($results->data)) {
  foreach ($results->data as $topic) {
    echo '
    <div class="topic">';
    if (!empty($topic['image'])) {
      echo '
      <div class="pic"><a href="' . $topic['url'] . '" target="_blank"><img src="' . $topic['image'] . '" alt="' . $topic['text'] . '" /></a></div>';
    }
    echo '
      <div class="text">
        <p class="topic_header"><img src="' . LIBRARY_SEARCH_URL . '/images/credo_topic_icon.png" alt="Credo Topic Page" /> Topic Page</p>
        <h3><a href="' . $topic['url'] . '" target="_blank">' . $topic['text'] . '</a></h3>
        <p>' . $topic['subtext'] . ' (<a href="' . $topic['url'] . '" target="_blank">Continue</a>)</p>
      </div>
    </div>';
  }
}
else {
  echo 'No topics found for this keyword.';
}

?>