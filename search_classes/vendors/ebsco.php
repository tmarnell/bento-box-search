<?php

class Ebsco extends Search {
  
  function __construct($query, $api, $database) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = true;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'EBSCOhost':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        $base_url = 'https://eit.ebscohost.com/Services/SearchService.asmx/Search';
        $params = array(
          'prof=' . EBSCO_PROF,
          'pwd=' . EBSCO_PWD,
          'sort=relevance',
          'numrec=' . DEFAULT_NUM_RECORDS,
          'db=' . $database,
          'query=' . $query
        );
        $url = $this->build_url($base_url, $params);
        //echo $url;
        
        // Set database name and alternatives
        switch ($database) {
          case 'aph':
            $this->db_name = 'Academic Search Premier';
            $profile = 'ehost';
            $alternatives = $this->build_alternative(
              'Academic One File',
              'https://support.gale.com/widgets/search?loc=' . GALE_STATE . '&id=aone&q=' . $query,
              'gale.png',
              'light_blue',
              true
            );
          break;
          case 'pwh':
            $this->db_name = 'Points of View Reference Center';
            $profile = 'pov';
            $alternatives = $this->build_alternative(
              'Opposing Viewpoints in Context',
              'https://support.gale.com/widgets/search?loc=' . GALE_STATE . '&id=ovic&q=' . $query,
              'gale.png',
              'light_blue',
              true
            );
          break;
        }
        
        // Set more link
        $more_url = 'https://search.ebscohost.com/login.aspx?direct=true&scope=site&type=1&site=ehost-live&lang=en&profile=' . $profile . '&db=' . $database . '&bquery=' . $query;
        $this->more = $more_url;
        
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
      case 'EBSCOhost':
        $count = (int) $parsed_data->Hits;
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
      case 'EBSCOhost':
        $record_objects = $parsed_data->SearchResults->records->rec;
        if (count($record_objects) > 0) {
          foreach ($record_objects as $record) {
            $control_info = $record->header->controlInfo;
            $url = (string) $record->plink;
            $title = (string) $control_info->artinfo->tig->atl;
            $journal = (string) $control_info->jinfo->jtl;
            $date = (string) $control_info->pubinfo->dt['year'];
            $type = (string) $control_info->artinfo->doctype;
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $this->build_subtext(array($journal, $date)),
              'type' => $type
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