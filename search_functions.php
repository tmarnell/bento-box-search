<?php

// Log search in MySQL
function log_search($query, $tab) {
  
  if (!empty($query)) {

    // Get referer
    $referer = '';
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
      $referer = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
    }
    
    // Save to MySQL
    if ($mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
      $stmt = $mysqli->prepare('INSERT INTO log (log_referer, log_query, log_tab) VALUES (?, ?, ?)');
      $stmt->bind_param('sss', $referer, $query, $tab);
      $stmt->execute();
      $stmt->close();
      $mysqli->close();
    }
  
  }
  
}

// Include all class files
function include_search_classes() {
  
  // Database classes
  require_once(LIBRARY_SEARCH . '/search_classes/search.php');
  $vendor_dir = LIBRARY_SEARCH . '/search_classes/vendors/';
  if (is_dir($vendor_dir)) {
    if ($vh = opendir($vendor_dir)) {
      while (($file = readdir($vh)) !== false) {
        if ($file != '.' && $file != '..') {
          require_once($vendor_dir . $file);
        }
      }
    }
  }
  
  // Recommendation class
  require_once(LIBRARY_SEARCH . '/recommended_resources/recommendations.php');
}

// Sanitize query
function sanitize_query($query) {
  return filter_var($query, FILTER_SANITIZE_STRING);
}

// Encode query
function encode_query($query) {
  $decoded_query = html_entity_decode(trim($query), ENT_QUOTES);
  return urlencode($decoded_query);
}

// Print search form
function print_search_form($tab, $query) {
  ob_start();
  echo '
  <form id="bento-search" class="library-search-form" method="get" action="">
    <input type="hidden" name="t" value="' . $tab . '" />
    <input type="text" name="q" value="' . $query . '" placeholder="Search library resources" />
    <input type="submit" value="Search" />
  </form>';
  $search_form = ob_get_contents();
  ob_end_clean();
  return $search_form;
}
// Declare tabs
function get_tabs() {
  return array(
    'all' => 'Featured Results',
    'articles' => 'Articles',
    'books' => 'Books',
    'av' => 'Videos &amp; Audio',
    'images' => 'Images',
    'stats' => 'Statistics'
  );
}

// Print Did You Mean div
function print_dym() {
  return '
  <div class="row">
    <div class="box" id="dym_box">
      <div id="dym">&nbsp;</div>
    </div>
  </div>';
}

// Print tabs
function print_tabs($tab, $query) {
  
  // Encode query
  $encoded_query = encode_query($query);
  
  // Get tabs array
  $tabs = get_tabs();
  
  // Print tabs
  ob_start();
  echo '<ul id="tabs">';
  foreach ($tabs as $tab_key => $tab_label) {
    echo '<li';
    if ($tab_key == $tab) {
      echo ' class="active"';
    }
    echo '>';
    // Send Images tab to Image Quest in new window
    if ($tab_key == 'images') {
      echo '<a href="' . PROXY_PREFIX . 'https://quest.eb.com/search/' . str_replace('+', '-', $encoded_query) . '" title="Image Quest" target="_blank">' . $tab_label . '</a>';
    }
    // Send all other tabs to a new page with the same query in the same window
    else {
      echo '<a href="?q=' . $encoded_query . '&amp;t=' . $tab_key . '" title="' . $tab_label . '" title="' . $tab_label . '">' . $tab_label . '</a>';
    }
    echo '</li>';
  }
  echo '</ul>';
  $tabs_ul = ob_get_contents();
  ob_end_clean();
  
  // Return unordered list
  return $tabs_ul;
}

// Print contents of a tab
function print_tab_contents($tab) {
  ob_start();
  $tab_file = LIBRARY_SEARCH . '/tab_contents/' . $tab . '.php';
  if (is_file($tab_file)) {
    include($tab_file);
  }
  else {
    die('<p>Select one of the tabs above to view results.</p>');
  }
  $tab_contents = ob_get_contents();
  ob_end_clean();
  return $tab_contents;
}

?>