<?php
// Get FAST classifications from the Classify API
// Returns array of parsed OCLC data
function get_fast_classifications($query) {
  $classify_results = new OCLC($query, 'Classify');
  return $classify_results->get_data();
}

// Get full FAST heading from the FAST API
// Returns array of parsed FAST RDF data
function get_fast_heading($fast_identifier) {
  $fast_results = new OCLC($fast_identifier, 'FAST');
  return $fast_results->get_data();
}

// Get first LC classification from LC Linked Data Service
// Returns string
function get_lc_classifications($loc_subject_identifier) {
  
  $lc_classification = '';
  
  // Get LC subject heading  
  $lc_results = new LC($loc_subject_identifier, 'LDS');
  $lc_data = $lc_results->get_data();
  
  // Get classification from subject heading
  if (count($lc_data->Topic->classification) > 0) {
    foreach ($lc_data->Topic->classification as $classification) {
      if (empty($lc_classification)) {
        $lc_classification = (string) $classification;
      }
    }
  }
  
  return $lc_classification;
}

// Get topics for keywords
// Returns string of LC classification
function get_topic($query) {
  
  $topics = array();
  
  // Get FAST classifications from search
  $fast_classifications = get_fast_classifications($query);
  
  if (is_object($fast_classifications) && count($fast_classifications->headings->heading) > 0) {
  
    $topic = '';
  
    // For each FAST heading...
    foreach ($fast_classifications->headings->heading as $heading) {
      
      if (empty($topic)) {
      
        // Get heading type (Topic, Person, etc.)
        $heading_type = (string) $heading['type'];
      
        // Get LC classification for Topic type
        if ($heading_type == 'Topic') {
          // Get the full FAST heading
          if ($fast_data = get_fast_heading($heading['ident'])) {
            // Get the Library of Congress identifier
            if (count($fast_data->Description->sameAs->Description) > 0) {
              foreach ($fast_data->Description->sameAs->Description as $description) {
                if (stristr($description['about'], 'id.loc.gov/authorities/subjects')) {
                  $loc_subject_identifier = end(explode('/', $description['about']));
                  // Get the full Library of Congress classifications
                  if ( $lc_classifications = get_lc_classifications($loc_subject_identifier)) {
                    // Save classifications to Topic key
                    $topic = $lc_classifications;
                  }
                }
              }
            }
          }
        }
      
        // For other heading types, hard-set LC classification
        else if ($heading_type == 'Person' || $heading_type == 'Place' || $heading_type == 'Event') {
          switch ($heading_type) {
            
            // For people, set topic to biographies
            case 'Person':
              if (!stristr( (string) $heading, '(Fictitious character)' )) {
                $topic = 'CT';
              }
              else {
                $topic = 'P';
              }
            break;
            
            // For places, set topic to travel (geography > leisure)
            case 'Place':
              $topic = 'GV';
            break;
            
            // For events, set topic to history
            case 'Event':
              $topic = 'D';
            break;
            
          }
        }
      }
    }
  
  }
  
  return $topic;
 
}

// Get recommended resources for LC classification from MySQL
function get_recommended_resources($lc_classification) {
  $recommendation = new Recommendations($lc_classification);
  return $recommendation->get_resources();
}

// Print recommended resources as an unordered list
function print_recommended_resources($recommendations) {
  $prev_ids = array();
  ob_start();
  echo '<ul>';
  foreach ($recommendations as $resource) {
    if (!in_array($resource['id'], $prev_ids)) {
      echo '<li><a href="' . $resource['url'] . '" target="_blank" title="' . $resource['name'] . '">' . $resource['name'] . '</a><br />' . $resource['text'] . '</li>';
      $prev_ids[] = $resource['id'];
    }
  }
  echo '</ul>';
  $ul = ob_get_contents();
  ob_end_clean();
  return $ul;
}
?>