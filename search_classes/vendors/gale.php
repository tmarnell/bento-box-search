<?php

class Gale extends Search {
  
  function __construct($query, $api, $database) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    
    // Set vendor-specific variables
    $this->proxy_required = true;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'SRU':
        
        // Set cURL headers and response data type
        $headers = array();
        $data_type = 'xml';
        
        // Construct URL
        $base_url = 'http://sru.galegroup.com/' . $database;
        $params = array(
          'startRecord=1',
          'maximumRecords=' . DEFAULT_NUM_RECORDS,
          'operation=searchRetrieve',
          'version=1.1',
          'query=cql.anywhere=' . $query
        );
        
        // Set username based on database
        switch ($database) {
          case 'GVRL':
            $params[] = 'x-username=' . GALE_LOCAL;
          break;
          default: 
            $params[] = 'x-username=' . GALE_STATE;
          break;
        }
        
        $url = $this->build_url($base_url, $params);

        // Set database name, package variable, and recommended alternatives
        switch ($database) {
          case 'GVRL':
            $this->db_name = 'Gale Virtual Reference Library';
            $location = GALE_LOCAL;
            $alternative = $this->build_alternative(
              'Credo Reference',
              'http://search.credoreference.com/search/all?searchPhrase=' . $query,
              'credo.png',
              'orange',
              true
            );
          break;
          case 'OVIC':
            $this->db_name = 'Opposing Viewpoints in Context';
            $location = GALE_STATE;
            $alternative = $this->build_alternative(
              'Points of View Reference Center',
              'http://search.ebscohost.com/login.aspx?direct=true&scope=site&type=1&site=ehost-live&lang=en&profile=pov&bquery=' . $query,
              'pov_reference_center.gif',
              'orange',
              true
            );
          break;
          case 'STND':
            $this->db_name = 'InfoTrac Newsstand';
            $location = GALE_STATE;
            $alternative = $this->build_alternative(
              "America's News",
              'https://infoweb.newsbank.com/resources/search/nb?p=NewsBank&b=results&action=search&t=country%3AUSA%21USA&fld0=alltext&val0=' . $query . '&bln1=AND&fld1=YMD_date&val1=&sort=YMD_date%3AD',
              'newsbank-small.png',
              'yellow',
              $query,
              true
            );
          break;
        }
        
        // Set more link
        $more_url = 'https://support.gale.com/widgets/search?loc=' . $location . '&id=' . $database . '&q=' . $query;
        $this->more = $more_url;
        
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
      case 'SRU':
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
      case 'SRU':
        $record_objects = $parsed_data->records->record;
        if (count($record_objects) > 0) {
          foreach ($record_objects as $record) {
            $record_data = $record->recordData;
            $url = (string) $record_data->dc->identifier;
            $title = (string) $record_data->dc->title;
            $relation = (string) $record_data->dc->relation;
            $raw_date = (string) $record_data->dc->date;
            $date = substr($raw_date, 0, 4);
            $type = (string) $record_data->dc->type;
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $this->build_subtext(array($relation, $date)),
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