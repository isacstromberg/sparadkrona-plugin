<?php
require __DIR__ . '/vendor/autoload.php';
use Orhanerday\OpenAi\OpenAi;

/*
Plugin Name: Sparadkrona-plugin
Description: Allows "Spartips" to be published automaticly on WordPress
Version: 1.0
Author: Isac Strömberg
*/

global $wpdb;

// Function to create a new post
function generate_wordpress_article() {
    global $wpdb;
    $open_ai = new OpenAi(MY_API_KEY);
    
    $unsplashApi = MY_API_KEY_2;
    
    $client = new GuzzleHttp\Client();

$response = $client->request('GET', "https://api.unsplash.com/search/photos?query=iceland&client_id={$unsplashApi}");
  $data = json_decode($response->getBody(), true);
$fetchPicture = $data['results'][0]['urls']['thumb'];
	
	$jpg = '.jpg';
$image_url = $fetchPicture . $jpg;
      // Fetch data from the database table
      $result = $wpdb->get_results("SELECT * FROM TestDataSparTips WHERE isPresented = 0 ORDER BY ID ASC LIMIT 1");
   
      if (!$result) {
          return false;
      }
    
      $post_title = $result[0]->Title;
      $post_content = $result[0]->Article;
      $post_id = $result[0]->Id;

      $test = "degådfsdfrbranu";
      $idConvertedToInt = intval($post_id);
     $updateDb = $wpdb->update(
        'TestDataSparTips',
        array(
            'Article' => $test,
            'IsPresented' => 1,
        ),
        array(
            'ID' => $idConvertedToInt,
        ),
        array(
            '%s',// Article is a string
            '%d', // IsPresented is an int
            '%d'  // ID is an integer
        )
    );
     
    
   /*   $prompt = "Ge mig ett spartips kopplat till denna titel {$post_title} som hjälper mig att spara pengar, du får inte skriva mer än 256 tecken";
      $complete = $open_ai->completion([
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'temperature' => 0.7,
        'max_tokens' => 256,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0
        
    ]);


    $gptarr = json_decode($complete, true);
    $generatedArticle = $gptarr['choices'][0]['text']; */

    $generatedArticle = "Skönt att inte slösa api";
    

  // Kontrollera om ett inlägg med samma titel redan finns
  $existing_post = get_page_by_title($post_title, OBJECT, 'post');
  if ($existing_post) {
      return false;
  }
$image_html = '<img src="' . $image_url . '">';
$postarr = array(
    'post_title' => 'Snart är det helg =)',
    'post_content' => $image_html . 'Här är lite text och en bild: ',
	'post_status' => 'draft',
    'post_type' => 'post',
);

// Spara posten och bifoga bilden
$post_id = wp_insert_post($postarr);
// Hämta filnamnet från URL:en
$file_name = basename($image_url);
// Hämta filinnehållet från URL:en
$image_content = file_get_contents($image_url);
// Hämta WordPress uppladdningskatalogen.
$wp_upload_dir = wp_upload_dir();
// Sätt uppladdningsmappens sökväg.
$upload_path = $wp_upload_dir['path'];
// Sätt uppladdningsmappens URL.
$upload_url = $wp_upload_dir['url'];
// Skapa filvägen
$file_path = $upload_path . '/' . $file_name;

// Skapa en fil i uppladdningsmappen
file_put_contents($file_path, $image_content);

// Hämta WordPress uppladdningsfunktionen
$wp_upload_file = wp_upload_bits($file_name, null, $image_content, date("Y-m", strtotime("now")));

// Lägg till bilden i media biblioteket
$wp_filetype = wp_check_filetype($file_name, null );
$attachment = array(
    'post_mime_type' => $wp_filetype['type'],
    'post_title' => sanitize_file_name($file_name),
    'post_content' => '',
    'post_status' => 'inherit'
);
$attachment_id = wp_insert_attachment( $attachment, $wp_upload_file['file'] );	
$updateDb2 = $wpdb->update(
        'TestDataSparTips',
        array(
            'Picture' => $attachment_id,
			'Post' => $post_id,
        ),
        array(
            'ID' => $idConvertedToInt,
        ),
        array(
        
            '%d', // Picture is an int
			'%d', // Post is an int
            '%d'  // ID is an integer
        )
    );		
	
$attachment_data = wp_generate_attachment_metadata( $attachment_id, $wp_upload_file['file'] );
wp_update_attachment_metadata($attachment_id, $attachment_data);
	

	// Skapa en array för posten

	
	
	


// Set the post terms (category)
//wp_set_post_terms($wp_post_id, array('spartips-test'), 'category');

        // Check if the post was created successfully
        if ($post_id == 0) {
            return false;
        }
	

}
function update_wordpress_article() {
global $wpdb; 
$fetchIds = $wpdb->get_results("SELECT * FROM TestDataSparTips WHERE isPresented = 1 ORDER BY ID DESC LIMIT 1");	
// Skapa ett array med de uppdaterade postuppgifterna
$post_data = array(
  'ID' => $fetchIds[0]->Post, // Inläggets ID
  'post_title' => 'Såja', // Uppdaterad titel
   'meta_input' => array(
        '_thumbnail_id' => $fetchIds[0]->Picture// här ersätter du 123 med ID för bilden som du vill bifoga
    ),
);

// Uppdatera inlägget med hjälp av wp_update_post-funktionen
wp_update_post( $post_data );	   
} 

  // Hook the CRON event to our function
  add_action('publish_test', 'generate_wordpress_article');
  add_action('update_test', 'update_wordpress_article');


?>