<?php
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨
//          😎 Front-end for Wordpress 😎
//
//          Made by Jacob Kristensen (jacobseated.com)
//
//         To-do:
//           Dynamically load posts on the frontpage
//           Load by slug instead of post IDs
//           Add error pages and dynamic loading of normal Wordpress posts
//
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨

// Remove trailing slashes (if present), and add one manually.
// Note: This avoids a problem where some servers might add a trailing slash, and others not..
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/") . '/');

define('WP_USE_THEMES', false); // Do not load WP template, since we use our own template-system.
require BASE_PATH . 'wordpress/wp-load.php'; // Make Wordpress functions available

$wp_frontend = new wp_frontend(); // Prepare the front-end


$wp_frontend->show_page(); // Show the requested page




// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨
//          😎 CLASSES 😎
// ✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨✨

class wp_frontend {
  private $post_ids = array(); // Once a page is created in wordpress, its ID should be stored here, if needed. I.e. The kontakt page.
  private $completed_URL; // (string) Build from API_URL ... Etc..

  public $HTML_CONTENT = array(); // Content for the main template
  public $req_page; // (string) Requested page name
  
  public function __construct() {
    // Information "blocks" to be loaded into the templates
    $this->post_ids['address_info'] = 106; // Fetch address info from Wordpress infoboks

    // Special pages used by the front-end, such as contact and privacy policy.
    // These are protected against deletion in the "Code Snippets" (kodestykker) Wordpress Plugin
    // Note. Some of these pages may have extra content loaded, such as dynamically loading people on the "kontakt" page
    $this->post_ids['frontpage_content'] = 113;
    $this->post_ids['kontakt_content'] = 117;
    // $this->post_ids['400_bad_request'] = '';

    $this->HTML_CONTENT['main_content'] = ''; // Default value (Some pages might not have static cotnent)
    $this->HTML_CONTENT['description'] = ''; // All pages should have a meta description, this is just a fall-back value.. Do not hard-code a description here!

    $this->requested_page(); // Figure out which page was requested

    // Load content that is used on all pages
    $this->region_links(); // Can be used in the global site-footer
    $this->SoMe_links(); // Can be used in the global site-footer
    $this->load_address(); // Address-box containing the address for the owner/entity
    $this->build_navigation(); // Create the navigation menu using the "pages" post-type from wordpress back-end
    $this->HTML_CONTENT['website_title'] = get_option('blogname'); // Get the site name from Wordpress options
  }

  private function requested_page() {
    // Get the requested page (if any) and validate the input, otherwise default to false
    if (isset($_GET['page'])) {
      if (preg_match("|^[a-z0-9_-]+$|i", $_GET['page'])) {
        $this->req_page = $_GET['page'];
      } else {
        $this->req_page = '400'; // 400 - Bad Request
      }
    } else {
      $this->req_page = 'frontpage'; // Assume the frontpage is requested if the page-parameter is empty
    }
  }
  private function load_content_by_name($post_name, $post_type='page') {
    // Wordpress does not seem to have a simple way to fetch a page by post_name
    $post = null;

    if ( $posts = get_posts( array( 
        'name' => $post_name, 
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 1
    ) ) ) $post = $posts[0];
    
    // Now, we can do something with $found_post
    if ($post!==null) {
      return $this->return_found_post($post);
    } else {
      return false;
    }
  }
  private function load_content($post_id) {
    // Here's some $post properties: post_date * post_date_gmt * post_content * post_title
    $post = get_post($post_id);


    // If a post was found, return the content
    if ($post!==null) {
      return $this->return_found_post($post);
    } else {
      return false;
    }

  }
  private function SoMe_links() {
    $wp_result = get_posts(array(
      'post_type'   => 'some_link',
      'numberposts' => '-1')); // Load posts of type "kontakt"

    require BASE_PATH .'templates/default/some_link_template.php'; // Load relevant HTML from template
    $placeholders = array("[PLACEHOLDER=url]", "[PLACEHOLDER=ikon_url]", "[PLACEHOLDER=alt]");
    $html_slices = ''; // A string of updated HTML for later use

    if($wp_result!==false) {
      foreach($wp_result as $unit) {
        $custom_fields = get_fields($unit->ID, false);

        $image = get_field('ikon', $unit->ID);
        if($image==false) {
          $image['url'] = '';
          $image['alt'] = 'Manglende SoMe ikon.';
        }
        
        $replacements = array($custom_fields['url'], $image['url'], $image['alt']);
        $html_slices .= str_replace($placeholders, $replacements, $some_link_template); // Add & Replace "Kontakt"
     }
     $for_main_html = str_replace('[REPEATED]', $html_slices, $some_link_container); // Add the container element

     $this->HTML_CONTENT['SoMe_link_section'] = $for_main_html; // HTML now ready for placement into main template
    } else {
     $this->HTML_CONTENT['SoMe_link_section'] = ''; // If no SoMe links found, this should be empty!
    }
  }
  private function build_navigation() {
    $candidates = get_pages(array('exclude' => array(3))); // Exclude Privacy Policy (3)


    require BASE_PATH .'templates/default/navigation_template.php'; // Load relevant HTML from template
    $placeholders = array("[PLACEHOLDER=post_name]", "[PLACEHOLDER=link_titel]", "[PLACEHOLDER=thumbnail]");
    $html_slices = ''; // A string of updated HTML for later use

    // First identify which pages should be included, using post_name as key
    foreach($candidates as &$candidate) {
      $include_in_navigation_field = get_field('navigation', $candidate->ID);
      $link_titel_field = get_field('link_titel', $candidate->ID);

      // If the page should be included in the navigation
      if((!empty($include_in_navigation_field[0])) && ($this->req_page !== $candidate->post_name)) {
        // Fetch thumbnail (if any)
        $thumbnail = get_the_post_thumbnail_url($candidate->ID);
        if ($thumbnail) {
          $thumbnail = $thumbnail;
        } else {$thumbnail = '';}

        // Add short title if available
        if (!empty($link_titel_field)) {
          $link_titel = $link_titel_field;
        } else {
          $link_titel = $candidate->post_title;
        }

        // Include the link
        $replacements = array($candidate->post_name, $link_titel, $thumbnail);
        $html_slices .= str_replace($placeholders, $replacements, $navigation_template);
     }
    }
    $for_main_html = str_replace('[REPEATED]', $html_slices, $navigation_container); // Add the container element

    $this->HTML_CONTENT['navigation'] = $for_main_html;
  }
  private function region_links() {
    $wp_result = get_posts(array(
      'post_type'   => 'region',
      'numberposts' => '-1')); // Load posts of type "kontakt"

    if ($wp_result!==false) {
      require BASE_PATH .'templates/default/region_template.php'; // Load relevant HTML from template
      $placeholders = array("[PLACEHOLDER=title]", "[PLACEHOLDER=url]");
      $html_slices = ''; // A string of updated HTML for later use

      foreach($wp_result as &$unit) { // Update $html_slices with template & data from Wordpress back-end
        $region_link = get_field('url', $unit->ID);
        $replacements = array($unit->post_title, $region_link);
        $html_slices .= str_replace($placeholders, $replacements, $region_template);
      }
      $for_main_html = str_replace('[REPEATED]', $html_slices, $region_container); // Add the container element

      $this->HTML_CONTENT['region_section'] = $for_main_html; // HTML now ready for placement into main template
    } else {
      $this->HTML_CONTENT['region_section'] = '';
    }
  }
  private function load_address() {
    // Load the address box
    $post_content = $this->load_content($this->post_ids['address_info']);
    $this->HTML_CONTENT['address_section'] = $post_content['post_content'];
    $this->HTML_CONTENT['address_bgimage'] = "background-image:url('".$post_content['thumbnail']."');";
  }
  private function load_presenter($post_content) {
    $this->HTML_CONTENT['presenter_title'] = $post_content['post_title'];
    $this->HTML_CONTENT['presenter_image'] = $post_content['thumbnail'];

    // The Presenter typically contains a background-image and a h1 heading with the title of the page
    require BASE_PATH .'templates/default/presenter_template.php';
    $this->HTML_CONTENT['presenter'] = $presenter_template;
  }
  public function show_page() {
        // First we check if requested page was one of the "special" pages with extra dynamic content
        // Other pages are loaded normally, without adding extra content to the HTML.
        if ($this->req_page == 'kontakt') {

          $wp_result = get_posts(array(
            'post_type'   => 'kontakt',
            'orderby'     => 'date',
            'order'       => 'ASC',
            'numberposts' => '-1')); // Load posts of type "kontakt"
          
          $post_content = $this->load_content($this->post_ids['kontakt_content']);

          $this->HTML_CONTENT['title'] = $post_content['post_title'] . ' | ' . $this->HTML_CONTENT['website_title'];
          $this->HTML_CONTENT['main_content'] = $post_content['post_content'];
          $this->HTML_CONTENT['description'] = get_field('description', $this->post_ids['kontakt_content']);

          $this->load_presenter($post_content); // Load the presenter template with $post_content

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
          $for_main_html = str_replace('[REPEATED]', $html_slices, $kontakt_container); // Add the container element
  
          $this->HTML_CONTENT['main_content'] .= $for_main_html; // HTML now ready for placement into main template
        } else if ($this->req_page == 'frontpage') {
          $post_content = $this->load_content($this->post_ids['frontpage_content']);

          $this->HTML_CONTENT['title'] = $post_content['post_title'] . ' | ' . $this->HTML_CONTENT['website_title'];
          $this->HTML_CONTENT['description'] = get_field('description', $this->post_ids['frontpage_content']);
          $this->HTML_CONTENT['main_content'] = $post_content['post_content'];

          $this->load_presenter($post_content); // Load the presenter template with $post_content
        } else if ($this->req_page == 400) {
          // $post_content = $this->load_content($this->post_ids['400_bad_request']);
          // $this->load_presenter($post_content);
          http_response_code(400); // Set the response code
          $this->HTML_CONTENT['title'] = '400 - Ugyldig Anmodning | ' . $this->HTML_CONTENT['website_title'];
          $this->HTML_CONTENT['description'] = 'Anmodningen var enten ugyldig eller ikke accepteret.';
          $this->HTML_CONTENT['main_content'] = '<p>Ugyldig Anmodning</p>';
        } else {
          $post_content = $this->load_content_by_name($this->req_page);

          $this->HTML_CONTENT['title'] = $post_content['post_title'] . ' | ' . $this->HTML_CONTENT['website_title'];
          $this->HTML_CONTENT['main_content'] = $post_content['post_content'];
          $this->HTML_CONTENT['description'] = get_field('description', $post_content['ID']);
          $this->load_presenter($post_content); // Load the presenter template with $post_content
        }

    
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> OUTPUT :-D
    // >>> Show Content >>>>>>>>>>>>>>>>>> OUTPUT :-D
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> OUTPUT :-D
    require BASE_PATH .'templates/default/main_template.php'; // Load and fill out the HTML template
    header('Content-Type: text/html; charset=utf-8'); // Required HTTP header for UTF-8 character set and HTML mime-type
    echo $template; // Send the HTML to the browser
  }

  private function return_found_post($post) {
    $post_id = $post->ID;

    $returned_content = array('thumbnail' => '');
    $returned_content['ID'] = $post->ID;
    $returned_content['post_title'] = $post->post_title;
    $returned_content['post_date']    = $post->post_date; // Needed for HTTP headers - implement this later!
    $returned_content['post_content'] = $post->post_content;

    $thumbnail = get_the_post_thumbnail_url($post_id); // Maybe better than using ACF, just figure out how to load alt text before using on kontakt
    if ($thumbnail) {
      $returned_content['thumbnail'] = $thumbnail;
    }
    return $returned_content;
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