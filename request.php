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

$response = $client->request('GET', "https://api.unsplash.com/search/photos?query=göteborg&client_id={$unsplashApi}");
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
     
    
      $prompt = "Ge mig ett spartips kopplat till denna titel {$post_title} som hjälper mig att spara pengar, du får inte skriva mer än 256 tecken";
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
    $generatedArticle = $gptarr['choices'][0]['text']; 

   // $generatedArticle = "Skönt att inte slösa api";
    

  // Kontrollera om ett inlägg med samma titel redan finns
  $existing_post = get_page_by_title($post_title, OBJECT, 'post');
  if ($existing_post) {
      return false;
  }


//Skapa det nya inlägget och inkludera kategorin
$post_id = wp_insert_post(array(
    'post_title' => $post_title,
    'post_content' => $generatedArticle,
    'post_status' => 'draft',
    'post_type' => 'post',

));

// Tilldela en bild till inlägget

$upload_dir = wp_upload_dir(); // Hämta uppladdningsmappen
$image_data = file_get_contents($image_url); // Hämta bildens data
$filename = basename($image_url); // Hämta filnamnet
$unique_file_name = wp_unique_filename( $upload_dir['path'], $filename ); // Generera ett unikt filnamn
$file_path = $upload_dir['path'] . '/' . $unique_file_name; // Generera sökvägen till den uppladdade filen
file_put_contents( $file_path, $image_data ); // Ladda upp bilden till servern
$wp_filetype = wp_check_filetype( $filename, null ); // Hämta filtypen för bilden
$attachment = array(
'post_mime_type' => $wp_filetype['type'],
'post_title' => $filename,
'post_content' => '',
'post_status' => 'inherit'
);
$attachment_id = wp_insert_attachment( $attachment, $file_path, $post_id ); // Lägg till bild som bilaga till inlägget
$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path ); // Generera metadata för bilagan
wp_update_attachment_metadata( $attachment_id, $attachment_data ); // Uppdatera metadata för bilagan
set_post_thumbnail( $post_id, $attachment_id ); // Tilldela bilden som utvald bild för inlägget



// Set the post terms (category)
wp_set_post_terms($post_id, array('spartips-test'), 'category');

        // Check if the post was created successfully
        if ($post_id == 0) {
            return false;
        }
}
    

  // Hook the CRON event to our function
  add_action('publish_test', 'generate_wordpress_article');
  


?>