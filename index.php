<?php
include('search_includes.php');

// Get tab
$tab = 'all';
if (isset($_GET['t']) && !empty($_GET['t'])) {
  $tab = $_GET['t'];
}

// Get query
$query = filter_var($_GET['q'], FILTER_SANITIZE_STRING);

// Log search
log_search($query, $tab);

// Include header
include(LIBRARY_INCLUDES . '/header.php');
?>
  <title><?php echo SEARCH_NAME; if (!empty($query)) {echo ': ' . $query ;}?> | COCC Barber Library</title>
  <!-- jQuery UI -->
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <!-- Local scripts and styles -->
  <link rel="stylesheet" type="text/css" href="search.css" />
  <script src="search.js"></script>
  <script>
  <?php echo 'var query="' . $query . '";';?>
  <?php echo 'var tab="' . $tab . '";';?>
  </script>
<?php
include(LIBRARY_INCLUDES . '/header-end.php');
?>
<ul id="breadcrumbs">
  <li><a href="https://www.cocc.edu/">Home</a></li>
  <li><a href="https://www.cocc.edu/departments/default.aspx">Departments</a></li>
  <li><a href="https://www.cocc.edu/departments/library/default.aspx">Library</a></li>
  <li>Search</li>
</ul>
<?php
include(LIBRARY_INCLUDES . '/buildpage.php');
?>
<div id="top_wrapper" class="row">
  <h1>
    <?php echo SEARCH_NAME; if (!empty($query)) {echo ': <em>' . $query . '</em>';} ?>
  </h1>
  <div id="top_right_menu">
    <a id="get_help" title="Get Help from the Library" href="https://www.cocc.edu/departments/library/help/default.aspx" target="_blank">Get Help</a>
    <a id="atoz" title="A to Z Databases" href="http://guides.cocc.edu/az.php" target="_blank">All Databases</a>
    <!--<a id="feedback" title="Provide feedback about Library Search Beta" href="https://cocc.co1.qualtrics.com/jfe/form/SV_egJ8gP50QEG7Wi9" target="_blank" class="teal">Give Feedback</a>-->
  </div>
</div>
<?php
// Search form
echo print_search_form($tab, $query);

// Did You Mean
echo print_dym();

// Tab contents
if (empty($query)) {
  echo print_tab_contents('intro');
}
else {
  // Tabs
  echo print_tabs($tab, $query);

  // Database Results
  echo print_tab_contents($tab);
}
?>


<?php
include(LIBRARY_INCLUDES . '/footer.php');
?>