<?php

class NewsAPI extends Search {
  
  function __construct($query, $api) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = false;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'NewsAPI':
        
        // Set cURL headers and response data type
        $headers = array(
          'X-Api-Key: 2c11d80eeedc4105afd7a846fb975523'
        );
        $data_type = 'json';
        
        // Construct URL
        $base_url = 'https://newsapi.org/v2/everything/' . $database;
        $params = array(
          'sortBy=relevancy',
          'pageSize=' . $this->num_records,
          'q=' . $query
        );
        
        $url = $this->build_url($base_url, $params);
        //echo $url;

        // Set database name, package variable, and recommended alternatives
        $this->db_name = '<a href="https://newsapi.org/" target="_blank">NewsAPI.org</a>';
        $alternatives = $this->build_alternative(
          'Infotrac Newsstand',
          'https://support.gale.com/widgets/search?loc=s9004364&id=stnd&q=' . $query,
          'gale.png',
          'light_blue',
          true
        );
        
        // Set zero results message
        $this->zero_results = '<p>No results found in ' . $this->db_name . '.</p><p>' . $alternative . '</p>';
        
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
      default:
        $count = (int) $parsed_data->totalResults;
      break;
    }
    return $count;
  }
  
  // Transform parsed data into standardized array for printing
  function transform_data($parsed_data) {
    switch ($this->api) {
      case 'NewsAPI':
        $article_objects = $parsed_data->articles;
        if (count($article_objects) > 0) {
          foreach ($article_objects as $article) {
            $url = (string) $article->url;
            $title = (string) $article->title;
            $source = (string) $article->source->name;
            $publishedAt = (string) $article->publishedAt;
            $date = date('F j, Y', strtotime($publishedAt));
            $type = 'Article';
            $image = $article->urlToImage;
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $this->build_subtext(array($source, $date)),
              'type' => $type,
              'image' => $image
            );
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