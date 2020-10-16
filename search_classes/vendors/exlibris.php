<?php

class ExLibris extends Search {
  
  function __construct($query, $api, $format, $location) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = false;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'Primo':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'json';
        
        // Construct URL parts
        $base_url = 'https://api-na.hosted.exlibrisgroup.com/primo/v1/search';
        $params = array(
          'apikey=' . API_KEY_PRIMO,
          'vid=COCC',
          'tab=default_tab',
          'limit=' . DEFAULT_NUM_RECORDS,
          'q=any,contains,' . $query
        );
        
        // FACETS
        $multifacets = array();
        
        // Set scope and facets by format
        $scope = 'cocc_alma_summit';
        $context = 'L';
        if (!empty($format)) {
          $pci_formats = array(
            'reference_entrys',
            'newspaper_articles'
          );
          if (in_array($format, $pci_formats)) {
            $scope = 'primo_central';
            $context = 'PC';
            $multifacets[] = 'facet_rtype,include,' . $format;
            $multifacets[] = 'facet_tlevel,include,online_resources';
          }
          else {
            $multifacets[] = 'facet_rtype,include,' . $format;
          }
        }
        
        // Add facet by location
        if (!empty($location)) {
          $locations = array(
            'clerc' => 'CLERC%20-%20Main%20Floor$$ICOCC',
            'govdocs' => 'Government%20Documents%20-%20Main%20Floor$$ICOCC',
            'periodicals' => 'Periodicals%20-%20Journals,%20Magazines,%20%26%20Newspapers$$ICOCC'
          );
          if (isset($locations[$location])) {
            $multifacets[] = 'facet_local3,include,' . $locations[$location];
          }
        }
        
        $params[] = 'scope=' . $scope;
        $params[] = 'multiFacets=' . implode('%7C,%7C', $multifacets);
        
        // Build URL
        $url = $this->build_url($base_url, $params);
        
        // Set more link
        $more_url = 'https://alliance-primo.hosted.exlibrisgroup.com/primo-explore/search?vid=' . PRIMO_VID . '&tab=default_tab&sortby=rank&search_scope=' . $scope . '&mode=basic';
        if (!empty($format)) {
          $more_url .= '&facet=rtype,include,' . $format;
        }
        if (!empty($location) && isset($locations[$location])) {
          $more_url .= '&facet=local3,include,' . $locations[$location];
        }
        $more_url .= '&query=any,contains,' . $query;
        $this->more = $more_url;
        
        // Set database name
        $this->db_name = 'the Barber Library &amp; Summit Catalog';
        
        // Set alternative
        $alternative = $this->build_alternative(
          'WorldCat',
          $this->build_wc_link($format),
          'worldcat.png',
          'dark_teal',
          false
        );
        
        // Set zero results message
        $this->zero_results = '<p>No results found in ' . $this->db_name . '.</p><p>' . $alternative . '</p>';
        
      break;
      case 'XService':
      
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL parts
        $base_url = 'http://alliance-primo.hosted.exlibrisgroup.com/PrimoWebServices/xservice/search/brief';
        $params = array(
          'institution=COCC',
          'indx=1',
          'bulkSize=1',
          'dym=true',
          'loc=local,scope:(E-COCC),scope:(P),scope:(COCC)',
          'query=any,contains,' . $query
        );
        
        // Build URL
        $url = $this->build_url($base_url, $params);
      
      break;
    }
    
    // Get and parse response using Search methods
    $response = $this->get_response($url, $headers);
    $parsed_data = $this->parse_response($response, $data_type);
    
    // Set result count and transformed data using vendor-specific methods
    $this->result_count = $this->get_count($parsed_data);
    $this->data = $this->transform_data($parsed_data, $scope, $context);
  }
  
  // Get count of results from parsed data
  function get_count($parsed_data) {
    switch ($this->api) {
      case 'Primo':
        $count = (int) $parsed_data->info->total;
      break;
      case 'XService':
        if (is_array($parsed_data)) {
          $count = count($parsed_data->JAGROOT->RESULT->QUERYTRANSFORMS->QUERYTRANSFORM);
        }
      break;
      default:
        $count = 0;
      break;
    }
    return $count;
  }
  
  // Transform parsed data into standardized array for printing
  function transform_data($parsed_data, $scope, $context) {
    switch ($this->api) {
      case 'Primo':
      
        $types = array(
          'annual' => 'Annual Report',
          'audiocd' => 'Audio CD',
          'book' => 'eBook',
          'database' => 'Database',
          'dvdvideo' => 'DVD',
          'kit' => 'Kit',
          'journal' => 'Journal',
          'map' => 'Map',
          'newspaper' => 'Newspaper',
          'reference_entry' => 'Reference Entry',
          'score' => 'Music Score',
          'pbook' => 'Print Book',
          'threedobject' => '3D Object',
          'video' => 'Streaming Video'
        );
      
        $result_objects = $parsed_data->docs;
        if (count($result_objects) > 0) {
          foreach ($result_objects AS $result) {
            $record_id = (string) $result->pnx->control->recordid[0];
            $title = (string) $result->pnx->display->title[0];
            $creator = (string) $result->pnx->display->creator[0];
            if (stristr($creator, '$$Q')) {
              $creator = substr($creator, 0, strpos($creator, '$$Q'));
            }
            $is_part_of = (string) $result->pnx->display->ispartof[0];
            $date = (string) $result->pnx->display->creationdate[0];
            $raw_type = (string) $result->pnx->display->type[0];
            //$type = $raw_type;
            $type = $types[$raw_type];
           
            $transformed_data[] = array(
              'url' => 'https://alliance-primo.hosted.exlibrisgroup.com/primo-explore/fulldisplay?docid=' . $record_id . '&context=' . $context . '&vid=COCC&search_scope=' . $scope . '&tab=default_tab&lang=en_US',
              'text' => $title,
              'subtext' => $this->build_subtext(array($creator, $is_part_of, $date)),
              'type' => $type
            );
          }
        }
      break;
      case 'XService':
        if (isset($parsed_data->JAGROOT->RESULT->QUERYTRANSFORMS->QUERYTRANSFORM)) {
          foreach ($parsed_data->JAGROOT->RESULT->QUERYTRANSFORMS->QUERYTRANSFORM as $transformation) {
            if ($transformation["ACTION"] == 'did_u_mean') {
              $transformed_data[] = array(
                'dym' => (string) $transformation["QUERY"]
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
  
  // Build WorldCat links
  function build_wc_link($format) {
    
    // Set format strings for WorldCat queries
    $wc_formats = array(
      'pbooks' => 'format=Book&subformat=Book%3A%3Abook_printbook',
      'books' => 'format=Book&subformat=Book%3A%3Abook_digital',
      'dvd_videos' => 'format=Video&subformat=Video%3A%3Avideo_dvd',
      'media' => 'format=Video&subformat=Video%3A%3Avideo_digital',
      'audio_CDs' => 'format=Music&subformat=Music%3A%3Amusic_cd'
    );
    
    // Set params
    $params[] = 'queryString=' . $this->query;
    if (!empty($format)) {
      $params[] = $wc_formats[$format];
    }
    
    // Return URL
    return 'https://centraloregoncc.on.worldcat.org/search?' . implode('&', $params);
  }
  
}

?>