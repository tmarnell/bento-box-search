# bento-box-search
A bento-box-style federated search of common research databases for library websites.

This application will not work "out of the box," so to speak. After installing on a server, local defintions for API keys, MySQL connections, vendor customer IDs, etc. must be added to the search_definitons.php file. Tab and Vendor definitions will also need adjustment for each institution's subscription packages and links.

For documentation of the APIs used by the Bento Box Search, see [Federated Search API Documentation](https://docs.google.com/document/d/1bcnZhbUAcOIi_gtmxPCJAPTpF9mqJ5MmUC-IHW_D2qs/edit?usp=sharing).

Use the following description of each directory or file in the application to customize your Bento Box Search.

- [search_includes.php](search_includes.php) requires definitions, function files, and classes.
- [search_functions.php](search_functions.php) contains general functions for logging, including class files, processing queries, and printing elements of the user interface (tabs, search form, Did You Mean).
- [search_definitions.php](search_definitions.php) contains COCC-specific constants that are used by other scripts, such as API keys provided by vendors.
- [index.php](index.php) is the user interface for the search. It is styled with search.css, and results are populated by search.js.
- [images](images) contains all image files used in the interface.
- [recommended_resources](recommended_resources) contains the files related to providing recommended resources for a keyword search.
  - [recommendation_functions.php](recommended_resources/recommendation_functions.php) defines the functions that determine the LC classification for a query and return recommended resources for it.
  - [recommendations.php](recommended_resources/recommendations.php) defines the class Recommendations, which is used to return recommendations for an LC classification from the MySQL database.
- [results](results) contains files called asynchronously to populate elements of the user interface.
  - [credo_results.php](results/credo_results.php) populates the Topic Page result specifically. (The Reference Articles box is populated instead by search_results.php.)
  - [dym_results.php](results/dym_results.php) populates the Did You Mean suggestion.
  - [libguides_results.php](results/libguides_results.php) populates the Database suggestion and the Guides box.
  - [recommendation_results.php](results/recommendation_results.php) populates the Recommended Resources box.
  - [search_results.php](results/search_results.php) populates the main boxes for database results from vendors.
- [search_classes](search_classes) defines the objects used to return database results from vendors.
  - [search.php](search_classes/search.php) defines the parent Search class, with properties and methods shared by all vendors.
  - [vendors](search_classes/vendors) contains files that define child classes that inherit the Search class, with properties and methods specific to vendors. (The URL of the API, the function to transform the data from XML/JSON into a printed result, etc.)
- [tab_contents](tab_contents) contains the files included in the interface depending on the tab selected by the user.
