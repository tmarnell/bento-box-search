<?php

class ProQuest extends Search {
  
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
        $base_url = 'http://fedsearch.proquest.com/search/sru/' . $database;
        $params = array(
          'operation=searchRetrieve',
          'version=1.1',
          'maximumRecords=' . DEFAULT_NUM_RECORDS,
          'query=cql.serverChoice="' . $query . '"'
        );
        
        $url = $this->build_url($base_url, $params);

        // Set database name, package variable, and recommended alternatives
        switch ($database) {
          case 'hnpnewyorktimes':
            $this->db_name = 'Historical New York Times';
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
        $more_url = 'https://search.proquest.com/hnpnewyorktimes?accountid=38141';
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
            
            // Set blank defaults
            $url = '';
            $title = '';
            $source = '';
            $date = '';
            
            // Get data from record
            $record_data = $record->recordData;
            foreach ($record_data->record->datafield as $datafield) {
              // URL
              if ((string) $datafield['tag'] == '856' && (string) $datafield['ind2'] == '0') {
                foreach ($datafield->subfield as $subfield) {
                  if ((string) $subfield['code'] == 'u') {
                    $url = (string) $subfield;
                  }
                }
              }
              // Title
              else if ((string) $datafield['tag'] == '245') {
                $title = (string) $datafield->subfield[0];
              }
              // Date
              else if ((string) $datafield['tag'] == '045') {
                $raw_date = (string) $datafield->subfield[0];
                $date = date('F j, Y', strtotime(substr($raw_date, 1)));
              }
            }
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $this->build_subtext(array($date)),
              'type' => 'Article'
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