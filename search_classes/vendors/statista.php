<?php

class Statista extends Search {
  
  function __construct($query, $api) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = true;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'Media':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        $url = 'https://api.statista.com/search/apiKey/' . STATISTA_API_KEY . '/q/' . str_replace('+', '%20', $query);
        
        // Set more link
        $more_url = 'http://www.statista.com/search/?q=' . $query;
        $this->more = $more_url;
        
        // Set database name
        $this->db_name = 'Statista';
        
        // Set zero results message
        $this->zero_results = '<p>No results found in ' . $this->db_name . '.</p>';
        
      break;
    }
    
    // Get and parse response using Search methods
    $response = $this->get_response($url, $headers);
    $converted_response = utf8_decode($response);
    $parsed_data = $this->parse_response($converted_response, $data_type);
    
    // Set result count and transformed data using vendor-specific methods
    $this->result_count = $this->get_count($parsed_data);
    $this->data = $this->transform_data($parsed_data);
    
  }
  
  // Get count of results from parsed data
  function get_count($parsed_data) {
    switch ($this->api) {
      case 'Media':
        $count = (int) $parsed_data->numberOfRecords;
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
      case 'Media':
        $record_objects = $parsed_data->records->record;
        if (count($record_objects) > 0) {
          for ($i = 0; $i < DEFAULT_NUM_RECORDS; $i++) {
            if ($record = $record_objects[$i]) {
              $record_data = $record->recordData;
              $url = (string) $record_data->Link;
              $title_node = (string) $record_data->title;
              $title_array = explode(' | ', $title_node);
              $title = $title_array[0];
              $type = $title_array[1];
              $teaser_image = $record_data->teaserImageUrls->key1->src;
              $description = $record_data->description;
              if (strlen($description) > 200) {
                $description = substr($description, 0, 200) . '...';
              }
              $transformed_data[] = array(
                'url' => $url,
                'text' => $title,
                'subtext' => $description,
                'type' => $type,
                'image' => $teaser_image
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