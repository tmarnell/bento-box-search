<?php

class OCLC extends Search {
  
  function __construct($query, $api) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = false;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'Classify':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        $base_url = 'http://classify.oclc.org/classify2/Classify';
        $params = array(
          'summary=true',
          'heading=' . $query
        );
        $url = $this->build_url($base_url, $params);
        
      break;
      case 'FAST':
      
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        $url = 'https://fast.oclc.org/fast/' . $query . '/rdf.xml';
      
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