<?php

class Infobase extends Search {
  
  function __construct($query, $api, $database) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = true;
    
    // Remove all punctuation from query for URL structure
    $url_query = urlencode(preg_replace('/[^A-Za-z0-9\s]/', '', urldecode($query)));
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'ISVS':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        switch ($database) {
          case 'FOD':
            $product_id = '105';
          break;
        }
        $url = 'http://api.infobase.com/msearch/' . INFOBASE_ID . '/13/' . $product_id . '/' . $url_query . '/xml';
        
        // Set more link
        $more_url = 'http://fod.infobase.com/portalplaylists.aspx?wID=' . INFOBASE_ID . '&rd=a&q=' . $query;
        $this->more = $more_url;
        
        // Set database name and alternatives
        switch ($database) {
          case 'FOD':
            $this->db_name = 'Films on Demand';
            $alternatives = $this->build_alternative(
              'Kanopy',
              'https://cocc.kanopy.com/s?query=' . $query,
              'gale.png',
              'orange',
              false
            );
          break;
        }
        
        // Set zero results message
        $this->zero_results = '<p>No results found in ' . $this->db_name . '.</p><p>' . $alternatives . '</p>';
        
      break;
    }
    
    // Get and parse response using Search methods
    $response = $this->get_response($url, $headers);
    $parsed_data = $this->parse_response($response, $data_type);
    
    // Set result count and transformed data using vendor-specific methods
    $this->result_count = $this->get_count($parsed_data);
    $this->data = $this->transform_data($parsed_data);
    
  }
  
  // Get count of results from parsed data
  function get_count($parsed_data) {
    switch ($this->api) {
      case 'ISVS':
        $count = (int) $parsed_data->total;
      break;
      default:
        $count = 0;
      break;
    }
    return $count;
  }
  
  // Transform parsed data into standardized array for printing
  function transform_data($parsed_data) {
    switch ($this->api) {
      case 'ISVS':
        $record_objects = $parsed_data->Record;
        if (count($record_objects) > 0) {
          for ($i = 0; $i < DEFAULT_NUM_RECORDS; $i++) {
            if ($record = $record_objects[$i]) {
              $url = (string) $record->RecordURL;
              $title = (string) $record->Title;
              $producer = (string) $record->Producer;
              $date = (string) $record->copyrightYear;
              $type = (string) $record->AssetType;
              $image = $record->imageURL;
              $subtext = $this->build_subtext(array($producer, $date));
              $transformed_data[] = array(
                'url' => $url,
                'text' => $title,
                'subtext' => $subtext,
                'type' => $type,
                'image' => $image
              );
            }
          }
        }
      break;
      default:
        $transformed_data = array();
      break;
    }
    return $transformed_data;
  }
  
}

?>