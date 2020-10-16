<?php

class Credo extends Search {
  
  private $type;
  
  function __construct($query, $api, $type) {
    
    // Run Search construction to set universal constants
    parent::__construct($query, $api);
    $this->type = $type;
    
    // Set vendor-specific variables
    $this->proxy_required = true;
    
    // Set search URL, headers, data type, parameters and more link for the API
    switch ($api) {
      case 'Credo':
        
        // Set cURL headers and response data type
        $headers = array(
          'Accept: application/vnd.api+json',
          'Authorization: Token ' . CREDO_AUTHORIZATION
        );
        $data_type = 'json';
        
        // Construct URL
        $base_url = 'https://search.credoreference.com/api/v2/' . $type;
        $params = array(
          'filter%5Bsearch%5D=' . $query
        );
        switch ($type) {
          case 'topics':
            $params[] = 'page%5Blimit%5D=2';
            $params[] = 'include=images';
          break;
          case 'entries':
            $params[] = 'page%5Blimit%5D=' . DEFAULT_NUM_RECORDS;
            $params[] = 'include=title';
          break;
        }
        $url = $this->build_url($base_url, $params);
        
        // Set more link
        $more_url = 'https://search.credoreference.com/search/all?searchPhrase=' . $query;
        $this->more = $more_url;
        
        // Set database name and alternatives
        $this->db_name = 'Credo Reference';
        $alternatives = $this->build_alternative(
          'Gale Virtual Reference Library',
          'https://support.gale.com/widgets/search?loc=' . GALE_LOCAL . '&id=gvrl&q=' . $query,
          'gale.png',
          'light_blue',
          true
        );
        
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
    switch ($this->type) {
      case 'topics':
        $count = count($parsed_data->data);
      break;
      case 'entries':
        $count = count($parsed_data->data);
      break;
      default:
        $count = 0;
      break;
    }
    return $count;
  }
  
  // Transform parsed data into standardized array for printing
  function transform_data($parsed_data) {
    switch ($this->type) {
      case 'topics':
        $data = $parsed_data->data;
        $included = $parsed_data->included;
        if (count($data) > 0) {
          foreach ($data as $record) {
            $url = (string) $record->links->html;
            $title = (string) $record->attributes->name;
            $snippet = (string) $record->attributes->snippet;
            $image_id = (string) $record->relationships->images->data[0]->id;
            $thumbnail_image = '';
            // Get thumbnail associated with image ID
            if ($image_id) {
              foreach ($included as $image_info) {
                if ($image_info->id == $image_id) {
                  $thumbnail_image = (string) $image_info->attributes->{'thumbnail-path'};
                }
              }
            }
            // Set transformed data
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $snippet . '...',
              'type' => 'Topic',
              'image' => $thumbnail_image
            );
          }
        }
      break;
      case 'entries':
        $data = $parsed_data->data;
        $included = $parsed_data->included;
        if (count($data) > 0) {
          foreach ($data as $record) {
            $url = (string) $record->links->html;
            $title = (string) $record->attributes->heading;
            $word_count = (string) $record->attributes->{'word-count'};
            $title_id = (string) $record->relationships->title->data->id;
            // Get title associated with ID
            if ($title_id) {
              foreach ($included as $title_info) {
                if ($title_info->id == $title_id) {
                  $parent_title = (string) $title_info->attributes->title;
                }
              }
            }
            $subtext = $this->build_subtext(array($parent_title, $word_count . ' words '));
            // Set transformed data
            $transformed_data[] = array(
              'url' => $url,
              'text' => $title,
              'subtext' => $subtext,
              'type' => 'Reference Entry'
            );
          }
          
        }
      break;
    }

    return $transformed_data;
  }
  
}

?>