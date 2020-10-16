<?php

class LC extends Search {
  
  function __construct($query, $api) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = false;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'LDS':
        
        // Set cURL headers and response data type
        $headers = array(
          'Accept: application/rdf+xml',
          'User-Agent: 0'
        );
        $data_type = 'xml';
        
        // Construct URL
        $url = 'http://id.loc.gov/authorities/subjects/' . $query;
        
      break;
    }
    
    // Get and parse response using Search methods
    $response = $this->get_response($url, $headers);
    $parsed_data = $this->parse_response($response, $data_type);
    
    // Set data to parsed data for use in recommendation functions
    $this->data = $parsed_data;
    
  }
  
}

?>