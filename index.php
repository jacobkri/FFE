<?php
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨
//          😎 Front-end for Wordpress 😎
//
//          Made by Jacob Kristensen (jacobseated.com)
//
//         To-do:
//           Replace REST API with a proper server-sided intigration of Wordpress
//
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨


// Remove trailing slashes (if present), and add one manually.
// Note: This avoids a problem where some servers might add a trailing slash, and others not..
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/") . '/');

define('WP_USE_THEMES', false); // Do not load WP template, since we use our own template-system.
require BASE_PATH . 'wordpress/wp-load.php'; // Make Wordpress functions available

$wp_frontend = new wp_frontend(); // Prepare the front-end


// $wp_frontend->load_content_new(106);

// In the future, we really should query the Database rather than using REST API, as this will be more efficient.
// echo get_field('description', 113); // Custom Field

/*
$args = array('post_type'  => 'kontakt');
$postslist = get_posts( $args );
$thumbnail = get_the_post_thumbnail_url('106');
print_r($postslist);exit(); */


$wp_frontend->show_page(); // Show the requested page




// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨
//          😎 CLASSES 😎
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨

class wp_frontend {

  private $rest_max_results; // (string) Contains a query string when used
  private $API_URL; // (string) Wordpress API URL
  private $api_url_array = array(); // Used for required API endpoints
  private $post_ids = array(); // Once a page is created in wordpress, its ID should be stored here, if needed. I.e. The kontakt page.
  private $completed_URL; // (string) Build from API_URL ... Etc..

  public $HTML_CONTENT = array(); // Content for the main template
  public $req_page; // (string) Requested page name
  
  public function __construct() {
    $this->rest_max_results = '?per_page=100'; // 100 is max.. If more is needed, support for pagination must be added.
    $this->API_URL = 'https://jacobseated.com/kea/FFE2/wordpress/wp-json/wp/v2/';

    // ********************************************
    // Define URLs for Wordpress REST API endpoints and post_ids for static pages
    // ********************************************

    // POST-TYPE endpoints
    $this->api_url_array['region'] = $this->API_URL . 'region' . $this->rest_max_results;
    $this->api_url_array['some_link'] = $this->API_URL . 'some_link' . $this->rest_max_results;

    // Information "blocks" to be loaded into the templates
    $this->post_ids['address_info'] = 106; // Fetch address info from Wordpress infoboks
    $this->api_url_array['website_title'] = $this->API_URL . 'infoboks/111'; // Used in the HTML <titl>

    // Special pages, such as contact and privacy policy.
    // Note. Some of these may have extra dynamic content loaded by other functions in the class
    $this->api_url_array['frontpage_content'] = $this->API_URL . 'pages/113';
    $this->post_ids['kontakt_content'] = 117;

    $this->HTML_CONTENT['main_content'] = ''; // Default value (Some pages might not have static cotnent)
    $this->HTML_CONTENT['description'] = ''; // All pages should have a meta description, this is just a fall-back value.. Do not hard-code a description here!

    $this->requested_page(); // Figure out which page was requested

    // Load content that is used on all pages
    $this->region_links(); // Can be used in the global site-footer
    $this->SoMe_links(); // Can be used in the global site-footer
    $this->load_address(); // Address-box containing the address for the owner/entity
    $this->HTML_CONTENT['website_title'] = get_option('blogname'); // Get the site name from Wordpress options
  }

  private function requested_page() {
    // Get the requested page (if any) and validate the input, otherwise default to false
    if ( (isset($_GET['page'])) && (preg_match("|^[a-z0-9_-]+$|i", $_GET['page'])) ) {
      $this->req_page = $_GET['page'];
    } else {
      $this->req_page = false;
    }

    // Determine what page was requested, and construct REST API endpoint if applicable
    $this->completed_URL = false; // Used to fetch content on individual pages I.e. frontpage and about

    if ($this->req_page!==false) { // If the "page" parameter is NOT empty, use it to fetch content from wordpress
      $this->completed_URL = $this->API_URL . $this->req_page . $this->rest_max_results;
    } else { // If req_page is false, assume the frontpage was requested
      $this->HTML_CONTENT['presenter'] = '<div class="presenter"><h2>Det er helt vildt...</h2></div>';
      $this->req_page = 'frontpage';
      // $this->HTML_CONTENT['main_content']
      $this->load_content('main_content', $this->api_url_array['frontpage_content']);
    }
  }
  
  public function load_address() {
    // Load the address box
    $post_content = $this->load_content_new($this->post_ids['address_info']);
    $this->HTML_CONTENT['address_section'] = $post_content['post_content'];
    $this->HTML_CONTENT['address_bgimage'] = "background-image:url('".$post_content['thumbnail']."');";
  }

  public function region_links() {
    $wp_result = $this->loadJson($this->api_url_array['region']); // Load the "region" links for use in the templates

    require BASE_PATH .'templates/default/region_template.php'; // Load relevant HTML from template
    $placeholders = array("[PLACEHOLDER=title]", "[PLACEHOLDER=url]");
    $html_slices = ''; // A string of updated HTML for later use

    foreach($wp_result as &$unit) { // Update $html_slices with template & data from Wordpress back-end
        $replacements = array($unit['title']['rendered'], $unit['acf']['url']);
        $html_slices .= str_replace($placeholders, $replacements, $region_template); // Add & Replace "Kontakt"
    }
    $for_main_html = str_replace('[REPEATED]', $html_slices, $region_container); // Add the container element

    $this->HTML_CONTENT['region_section'] = $for_main_html; // HTML now ready for placement into main template
  }
  public function load_content($html_name, $url) {
    $wp_result = $this->loadJson($url);
    if (!empty($wp_result['acf']['tekst'])) { // If the post has the "tekst" field, assume ['content']['rendered'] is not used
      $this->HTML_CONTENT["{$html_name}"] = $wp_result['acf']['tekst'];
    } else {
      $this->HTML_CONTENT["{$html_name}"] = $wp_result['content']['rendered']; // Contains content from the Wordpress editor
    }
    if (!empty($wp_result['acf']['description'])) {
      $this->HTML_CONTENT["description"] = $wp_result['acf']['description'];
    }
  }
  public function load_content_new($post_id) {
    // This is the new function to load content from wordpress
    // post_date
// post_date_gmt
// post_content
// post_title
    $returned_content = array('thumbnail' => '');

    $thumbnail = get_the_post_thumbnail_url($post_id); // Maybe better than using ACF, just figure out how to load alt text before using on kontakt
    $post = get_post($post_id);

    $returned_content['post_date']    = $post->post_date; // Needed for HTTP headers - implement this later!
    $returned_content['post_content'] = $post->post_content;

    if ($thumbnail) {
      $returned_content['thumbnail'] = $thumbnail;
    }

    return $returned_content;
  }
  public function SoMe_links() {
    $wp_result = $this->loadJson($this->api_url_array['some_link']);

    require BASE_PATH .'templates/default/some_link_template.php'; // Load relevant HTML from template
    $placeholders = array("[PLACEHOLDER=url]", "[PLACEHOLDER=ikon_url]", "[PLACEHOLDER=alt]");
    $html_slices = ''; // A string of updated HTML for later use

    foreach($wp_result as &$unit) { // Update $html_slices with template & data from Wordpress back-end
        $replacements = array($unit['acf']['url'], $unit['acf']['ikon']['url'], $unit['acf']['ikon']['alt']);
        $html_slices .= str_replace($placeholders, $replacements, $some_link_template); // Add & Replace "Kontakt"
    }
    $for_main_html = str_replace('[REPEATED]', $html_slices, $some_link_container); // Add the container element

    $this->HTML_CONTENT['SoMe_link_section'] = $for_main_html; // HTML now ready for placement into main template
  }

  public function show_page() {
    // $wp_page = $this->loadJson($this->completed_URL); // Attempt to load the requested page

    $wp_result = get_posts(array('post_type'  => 'kontakt', 'numberposts' => '-1')); // Load posts of type "kontakt" 
  
    // Check if the main content was loaded
    if ($wp_result!==false) {

        // First we check if requested page was one of the "special" pages with extra dynamic content
        // Other pages are loaded normally, without adding extra content to the HTML.
        if ($this->req_page == 'kontakt') {
          $post_content = $this->load_content_new($this->post_ids['kontakt_content']);

          $this->HTML_CONTENT['main_content'] = $post_content['post_content'];

          $this->HTML_CONTENT['presenter'] = '<div class="presenter" style="background-image:url('.$post_content['thumbnail'].')"><h2>Sådan kontakter du os</h2></div>';

          require BASE_PATH .'templates/default/kontakt_template.php'; // Load relevant HTML from template
          $placeholders = array("[PLACEHOLDER=navn]", "[PLACEHOLDER=rolle]", "[PLACEHOLDER=telefon]", "[PLACEHOLDER=email]", "[PLACEHOLDER=billede]", "[PLACEHOLDER=billede_alt]");
          $html_slices = ''; // A string of updated HTML for later use
          // echo print_r($wp_result);exit();
          foreach ($wp_result as &$kontakt) {
            $kontakt_fields = get_fields($kontakt->ID, false);
            $image = get_field('billede', $kontakt->ID);
            $attachment = get_post($image);
            $img_src = wp_get_attachment_image_url($image, 'full', false);
            if($img_src !==false) {
              $img_alt = trim(strip_tags(get_post_meta($image, '_wp_attachment_image_alt', true)));
            } else {
              $img_src = 'templates/default/person.png';
              $img_alt = 'Profilbillede ikke tilgængeligt.';
            }
            
            $replacements = array($kontakt_fields['navn'], $kontakt_fields['rolle'], $kontakt_fields['telefon'], $kontakt_fields['email'], $img_src, $img_alt);
            $html_slices .= str_replace($placeholders, $replacements, $kontakt_template); // Add & Replace "Kontakt"
          }

  /*
          foreach($wp_result as &$kontakt) { // Update $html_slices with template & data from Wordpress back-end
              if(empty($kontakt['acf']['billede']['url'])) {
                  $kontakt['acf']['billede']['url'] = 'templates/default/person.png';
              }
              $replacements = array($kontakt['acf']['navn'], $kontakt['acf']['rolle'], $kontakt['acf']['telefon'], $kontakt['acf']['email'], $kontakt['acf']['billede']['url'], $kontakt['acf']['billede']['alt']);
              $html_slices .= str_replace($placeholders, $replacements, $kontakt_template); // Add & Replace "Kontakt"
          } */
          $for_main_html = str_replace('[REPEATED]', $html_slices, $kontakt_container); // Add the container element
  
          $this->HTML_CONTENT['main_content'] .= $for_main_html; // HTML now ready for placement into main template
        }
  
    } else {
      // Either we encountered a 404, or something was wrong with the connection.
      // We show a relevant error message in the browser at this point...
    }
    
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> OUTPUT :-D
    // >>> Show Content >>>>>>>>>>>>>>>>>> OUTPUT :-D
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> OUTPUT :-D
    require BASE_PATH .'templates/default/main_template.php'; // Load and fill out the HTML template
    header('Content-Type: text/html; charset=utf-8'); // Required HTTP header for UTF-8 character set and HTML mime-type
    echo $template; // Send the HTML to the browser
  }
  
  public function loadJson($url) {
    if  (in_array('curl', get_loaded_extensions())) { // Check if cURL is enabled on the server
      
     // ********* cURL GET Request
      // Initialize cURL session
      $ch = curl_init($url);
      // Option to Return the Result, rather than just true/false
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Perform the request, and save content to $result
      $result = curl_exec($ch);
      // Close the cURL resource, and free up system resources!
      curl_close($ch);
     
      // Return the result
      if ($result !== false) {
        return json_decode($result, true);
      } else {
        return false;
      }
    } else { // In case an update to the (potentially shared hosting) disables cURL
      echo 'cURL is not installed, but required for this site to work. Please show this error to your web-developer.';exit();
    }
  }
  
}