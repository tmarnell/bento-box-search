<?php

class Springshare extends Search {
  
  function __construct($query, $api) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = false;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'widget':
        
        // Set cURL headers
        $headers = array();
        
        // Construct URL
        $base_url = 'https://lgapi-us.libapps.com/widgets.php';
        $params = array(
          'site_id=' . SPRINGSHARE_SITE_ID,
          'widget_type=1',
          'search_terms=' . $query,
          'search_match=2',
          'sort_by=relevance',
          'list_format=1',
          'drop_text=Select+a+Guide...',
          'output_format=1',
          'load_type=2',
          'enable_description=1',
          'enable_group_search_limit=0',
          'enable_subject_search_limit=0',
          'widget_embed_type=2',
          'num_results=3',
          'enable_more_results=0',
          'window_target=2',
          'config_id=1533584391607'
        );
        $url = $this->build_url($base_url, $params);
        
        // Get HTML response
        $this->data = $this->get_response($url, $headers);
        
      break;
      case 'atoz':
        
        // Set cURL headers
        $headers = array();
        
        // Construct URL
        $base_url = 'https://lgapi-us.libapps.com/widgets.php';
        $params = array(
          'site_id=' . SPRINGSHARE_SITE_ID,
          'widget_type=2',
          'search_terms=' . $query,
          'search_match=1',
          'sort_by=name',
          'list_format=1',
          'drop_text=Select+a+Database...',
          'output_format=1',
          'load_type=2',
          'enable_description=1',
          'widget_embed_type=2',
          'num_results=1',
          'enable_more_results=0',
          'window_target=2',
          'config_id=1538498285604'
        );
        $url = $this->build_url($base_url, $params);
        
        // Get HTML response
        $this->data = $this->get_response($url, $headers);
        
        
      break;
    }
    
  }

}

?>