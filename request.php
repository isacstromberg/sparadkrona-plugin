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
    $open_ai = new OpenAi('');
    
    
      // Fetch data from the database table
      $result = $wpdb->get_results("SELECT * FROM TestDataSparTips WHERE isPresented = 0 ORDER BY ID ASC LIMIT 1");
      //$result = $wpdb->get_results("SELECT * FROM TestDataSparTips WHERE Id = 5");
      if (!$result) {
          return false;
      }
    
      $post_title = $result[0]->Title;
      $post_content = $result[0]->Article;
      $post_id = $result[0]->Id;

      $test = "kulattkriga";
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
     
    
     /* $prompt = "Ge mig ett spartips kopplat till denna titel {$post_title} som hjälper mig att spara pengar, du får inte skriva mer än 256 tecken";
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
    $generatedArticle = $gptarr['choices'][0]['text'];*/

    $generatedArticle = "Skönt att inte slösa api";
     

  // Kontrollera om ett inlägg med samma titel redan finns
  $existing_post = get_page_by_title($post_title, OBJECT, 'post');
  if ($existing_post) {
      return false;
  }

        // Create the new post
        $post_id = wp_insert_post(array(
            'post_title' => $post_title,
            'post_content' => $generatedArticle,
            'post_status' => 'publish',
            'post_type' => 'post'
        ));

        // Check if the post was created successfully
        if ($post_id == 0) {
            return false;
        }
}
    
/*function schedule_cron_event() {
  /*  wp_clear_scheduled_hook('publish_test');
    if ( ! wp_next_scheduled( 'publish_test' ) ) {
        wp_schedule_single_event( time(), 'publish_test' );
    }*/

 // Get the current time
 /*$current_time = current_time('timestamp');
    
 // Set the desired time of day for the event
 $event_time = strtotime('7:17pm', $current_time);
 
 // Schedule the event to occur at the desired time every day
 wp_schedule_event($event_time, 'daily', 'publish_test');


  }*/
  




  // Hook the CRON event to our function
  add_action('publish_test', 'generate_wordpress_article');
  
  // Schedule the CRON event
  //schedule_cron_event();

?>