<?php
class Recommendations {
  
  public $resources;
  
  function __construct($classification) {
    
    // Extract cutter from the LC classification
    $cutter = $classification;
    if (stristr($classification, '.')) {
      $cutter = substr($classification, 0, strpos($classification, '.'));
    }
    
    // From most to least specific classification, search database for recommendations
    $resources = $this->get_resources_by_cutter($cutter);
    
    // Save resources to property
    $this->resources = $resources;
    
  }
  
  // Search database by first letters, truncating and repeating if no results found
  function get_resources_by_cutter($cutter) {
    $resources = array();
    if (strlen($cutter) > 0) {
      $resources = $this->get_recommended_resources($cutter);
      if (!empty($resources)) {
        return $resources;
      }
      else {
        $new_cutter = substr($cutter, 0, strlen($cutter)-1);
        return $this->get_resources_by_cutter($new_cutter);
      }
    }
    return false;
  }
  
  // Search MySQL database for recommend resources for the LC classification
  function get_recommended_resources($cutter) {
    $recommendations = array();
    if ($mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_SEARCH)) {
      $recommendation_result = $mysqli->query('SELECT resources.resource_id, resources.resource_name, resources.resource_text, resources.resource_url, recommendations.recommendation_rank FROM (recommendations LEFT JOIN resources ON recommendations.resource_id = resources.resource_id) WHERE recommendations.lc_classification = "' . $cutter . '" ORDER BY recommendations.recommendation_rank ASC');
      if ($recommendation_result->num_rows > 0) {
        while ($recommendation = $recommendation_result->fetch_assoc()) {
          $recommendations[$recommendation['recommendation_rank']] = array(
            'id' => $recommendation['resource_id'],
            'name' => $recommendation['resource_name'],
            'text' => $recommendation['resource_text'],
            'url' => $recommendation['resource_url']
          );
        }
      }
      $mysqli->close();
    }
    return $recommendations;
  }
  
  // Return resources
  function get_resources() {
    return $this->resources;
  }
 
}
?>