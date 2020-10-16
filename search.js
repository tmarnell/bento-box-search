
$(document).ready(function() {
  
  // Autocomplete
  $('#bento-search input[name="q"]').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "https://en.wikipedia.org/w/api.php",
        dataType: "jsonp",
        data: {
          'action': "opensearch",
          'format': "json",
          'search': request.term
        },
        success: function(data) {
          var suggestions = new Array();
          var titles = data[1];
          var blurbs = data[2];
          for (i = 0; i < titles.length; i++) {
            if (blurbs[i].indexOf('may refer to') == -1) {
              suggestions.push(titles[i]);
            }
          }
          response(suggestions);
        }
      });
    },
    select: function(event, ui) {
      $('#bento-search input[name="q"]').val(ui.item.value).parent().submit();
    }
  });
  
  // Populate Did You Mean
  if ($('#dym').length > 0) {
    print_dym(query, tab);
  }
  
  // Check for A to Z database
  if ($('#db_by_name').length > 0) {
    print_atoz(query);
  }
  
  // Populate topics from Credo
  if ($('#credo_topics').length > 0) {
    print_credo_topics(query);
  }
  
  // Populate recommended resources
  if ($('#recs_and_guides').length > 0) {
    print_recommended_resources(query);
  }
  
  // Print database results
  print_boxes(query);
  
});

// Get Did You Mean suggestions from Ex Libris XService API
function print_dym(query, tab) {
  ajax_results('dym', {q: query, t: tab}, 'results/dym_results.php', 'No Did You Mean suggestions for this keyword.');
}

// Check for database name matches in LibGuides
function print_atoz(query) {
  ajax_results('db_by_name', {q: query, a: 'atoz'}, 'results/libguides_results.php', 'No results match the request.');
}

// Get topics pages from Credo
function print_credo_topics(query) {
  ajax_results('credo_topics', {q: query}, 'results/credo_results.php', 'No topics found for this keyword.');
}

// Get recommended resources and guides
function print_recommended_resources(query) {
  
  // Populate recommendations
  ajax_results('db_recommendations', {q: query}, 'results/recommendation_results.php', 'No topics found for this keyword.');
  
  // Populate guides
  ajax_results('libguides', {q: query, a: 'widget'}, 'results/libguides_results.php', 'No results match the request.');
  
}

// AJAX recommendation and guide scripts, display results or remove box
function ajax_results (div_id, params, url, no_results) {
  $.ajax({
    url: url,
    data: params,
    method: 'POST',
    dataType: 'html'
  }).done(function(data) {
    if (data.indexOf(no_results) == -1) {
      $('#' + div_id).html(data).parent('.box').show();
    }
  });
}

// Populate database results
// Repeat for every div with class search_results on the page
function print_boxes() {
  $('.search_results').each(function() {
    var result_div = $(this).attr('id');
    var vendor = $(this).data('vendor');
    var api = $(this).data('api');
    var db = $(this).data('db');
    var format = $(this).data('format');
    var loc = $(this).data('location');
    var type = $(this).attr('data-type');
    print_db_results(vendor, api, db, format, loc, type, query, result_div);
  });
}

// Get database results
function print_db_results(vendor, api, db, format, loc, type, query, result_div) {
  $.ajax({
    url: 'results/search_results.php',
    data: {vendor: vendor, api: api, db: db, format: format, loc: loc, type: type, q: query},
    method: 'POST',
    dataType: 'html'
  }).done(function(data) {
    $('#' + result_div).html(data);
  });
}