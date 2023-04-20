<?php
require __DIR__ . '/vendor/autoload.php';
use Orhanerday\OpenAi\OpenAi;

global $wpdb;

// Function to create a new post
function create_new_post() {
    global $wpdb;
    $open_ai = new OpenAi('');
    

    
      // Fetch data from the database table
      $result = $wpdb->get_results("SELECT * FROM TestDataSparTips WHERE Id = 1");
      if (!$result) {
          return false;
      }
 
      $post_title = $result[0]->Title;
      $post_content = $result[0]->Article;
      $post_id = $result[0]->ID;

      //$prompt = $_GET['prompt'];
      $prompt = "Ge mig ett tips om hur jag kan spara pengar på odla egen mat, du får inte skriva mer än 256 tecken";
      
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
    echo $generatedArticle;
    //var_dump($complete);
      
     

     // Uppdatera posttitel och innehåll i databasen
     $wpdb->update(
         'TestDataSparTips',
         array(
             'Gpttitle' => $post_title,
             'Gptarticle' => $generatedArticle,
         ),
         array( 'ID' => $post_id ),
         array( '%s', '%s' ),
         array( '%d' )
     );


    }

  // Kontrollera om ett inlägg med samma titel redan finns
  $existing_post = get_page_by_title($post_title, OBJECT, 'post');
  if ($existing_post) {
      return false;
  }

        // Create the new post
        $post_id = wp_insert_post(array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => 'publish',
            'post_type' => 'post'
        ));

        // Check if the post was created successfully
        if ($post_id == 0) {
            return false;
        }
    
// Avschemalägg CRON-händelsen
wp_clear_scheduled_hook('publish_post_once_event');

    return true;
}

function schedule_cron_event() {
  if ( ! wp_next_scheduled( 'publish_post_once_event' ) ) {
      wp_schedule_single_event( time(), 'publish_post_once_event' );
  }
}

// Hook the CRON event to our function
add_action('publish_post_once_event', 'create_new_post');

// Schedule the CRON event
schedule_cron_event();

/*$open_ai = new OpenAi('');
//$prompt = $_GET['prompt'];
$prompt = "Ge mig ett tips om hur jag kan spara pengar på odla egen mat, du får inte skriva mer än 256 tecken";

$complete = $open_ai->completion([
    'model' => 'text-davinci-003',
    'prompt' => $prompt,
    'temperature' => 0.7,
    'max_tokens' => 256,
    'top_p' => 1,
    'frequency_penalty' => 0,
    'presence_penalty' => 0
    
]);

$testarr = json_decode($complete, true);
$test2 = $testarr['choices'][0]['text'];
echo $test2;
//var_dump($complete);*/




?>