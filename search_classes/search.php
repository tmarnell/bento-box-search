<?php
class Search {

  /* Constants to be defined during construction of vendor children */
  public $query;
  public $api;
  public $proxy_required;
  
  /* Variables set during function calls in vendor children */
  public $db_name;
  public $data;
  public $more;
  public $result_count;
  public $zero_results;
  
  // Use parent construction to set universal constants
  function __construct($query, $api) {
    $this->query = $query;
    $this->api = $api;
  }
  
  // Return a URL to search a vendor database
  // Takes a base URL and array of parameters
  function build_url($base_url, $params) {
    $url = $base_url;
    if (!empty($params)) {
      $url .= '?' . implode('&', $params);
    }
    return $url;
  }
    
  // Return raw response from the search
  // Takes a URL and array of headers
  function get_response($url, $headers) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }
  
  // Parse the JSON or XML response from the search
  // Takes raw data from get_response() and a data type of XML or JSON
  function parse_response($raw_data, $data_type) {
    if ($data_type == 'json') {
      $parsed_data = json_decode($raw_data);
    }
    else if ($data_type == 'xml') {
      $namespace_pattern = '/([<\/\s])[a-z]+\:/';
      $clean_data = preg_replace($namespace_pattern, '\1', $raw_data);
      if ($clean_data[0] == '<') {
        $parsed_data = simplexml_load_string($clean_data);
      }
      else {
        $parsed_data = false;
      }
    }
    return $parsed_data;
  }
  
  // Return data
  // (used in recommendation functions)
  function get_data() {
    return $this->data;
  }
  
  // Build subtext for results
  // Takes array of strings
  function build_subtext($subtext_values) {
    $subtext_pieces = array();
    foreach ($subtext_values as $value) {
      if (!empty($value)) {
        $subtext_pieces[] = $value;
      }
    }
    return implode('; ', $subtext_pieces);
    $subtext_pieces = array();
  }
  
  // Build link to alternative resource
  function build_alternative($name, $url, $image, $color, $proxy_required) {
    $link = '<a class="button ' . $color . '" href="';
    if ($proxy_required) {
      $link .= PROXY_PREFIX;
    }
    $link .= $url . '" target="_blank"><span class="icon"><img src="' . LIBRARY_SEARCH_URL . '/images/' . $image . '" alt="' . $name . ' Logo" /></span><span class="text">Search ' . $name . ' for <em>' . urldecode($this->query) . '</em> &raquo;</span></a>';
    return $link;
  }
  
  // Print ordered list of links
  function print_list() {
    if (!empty($this->data)) {
      $ol = '<ol>';
      foreach ($this->data as $result) {
        
        // Add proxy prefixto URL if necessary
        if ($this->proxy_required) {
          $url = PROXY_PREFIX . $result['url'];
        }
        else {
          $url = $result['url'];
        }
        
        // Add list item
        $ol .= '<li>';
        if (!empty($result['image'])) {
          $ol .= '<img src="' . $result['image'] . '" alt="' . $result['text'] . '" />';
        }
        $ol .= '<a href="' . $url . '" target="_blank">' . $result['text'] . '</a>';
        if (!empty($result['type'])) {
          $ol .= ' (<span class="nowrap">' . $result['type'] . '</span>)';
        }
        $ol .= '<span class="subtext">' . $result['subtext'] . '</span>';
        $ol .= '</li>';
      }
      $ol .= '</ol>';
      return $ol;
    }
    else {
      return false;
    }
  }
  
  // Print more link
  function print_more() {
    if (!empty($this->more)) {
      if ($this->proxy_required) {
        $more_link = PROXY_PREFIX . $this->more;
      }
      else {
        $more_link = $this->more;
      }
      return '<a class="button" href="' . $more_link . '" target="_blank" title="' . $this->db_name . '"><span class="text">More from ' . $this->db_name . ' &raquo;</span></a>';
    }
    else {
      return 'Powered by ' . $this->db_name;
    }
  }
  
  // Print total count of results
  function print_count() {
    $span = '<span class="count">' . number_format($this->result_count) . '</span> result';
    if ($this->result_count > 1) {
      $span .= 's';
    }
    return $span;
  }
  
  // Comprehensive print results function
  function print_results() {
    if ($list = $this->print_list()) {
      echo '<p>' . $this->print_count() . '</p>';
      echo $list;
      echo '<p class="more">' . $this->print_more() . '</p>';
    }
    else {
      echo $this->zero_results;
    }
  }
  
}
?>