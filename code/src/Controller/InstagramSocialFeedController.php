<?php

/**
 * @file
 * Contains \Drupal\InstagramSocialFeed\Controller\PageExampleController.
 */

namespace Drupal\instagram_social_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\SafeMarkup;


/*
 * Drupal 7 call Ajax by drupal_json_output,  
 * Buy Drupal 8 call a Ajax service by JsonResponse
 * http://optimizely-to-drupal-8.blogspot.hk/2014/07/implementing-ajax-call-on-form.html
*/
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\Core\Ajax\AjaxResponse;


// Call the Controller from  \src\Form\InstagramSocialFeedSettingsForm.php
// Using Link: Drupal\instagram_social_feed\Form\InstagramSocialFeedSettingsForm;


/**
 * Controller routines for page example routes.
 */
class InstagramSocialFeedController extends ControllerBase {

  /**
   * Constructs a page with descriptive content.
   *
   * Our router maps this method to the path 'examples/page_example'.
   */


  public function moderation() {
  //public function moderation($first, $second) {

    /*
    // Make sure you don't trust the URL to be safe! Always check for exploits.
    if (!is_numeric($first) || !is_numeric($second)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }
    */

  //TO-DO  30-10-2015
  //drupal_flush_all_caches();


  $config = \Drupal::configFactory()->getEditable('instagram_social_feed.settings');

  $feed_id = $_GET['feed_id'];
  //$timeout = 60;
  $timeout = 60 * 3600; //60hr per once  記得改返, 試野only!
  //$timeout = $config->get('instagram_social_feed_passive_timeout', '');  //60
  //$timeout = intval($timeout) * 60;  //3600
  
  // Check if new data should be requested.

  //if( $timeout == 0 ){
    /*
    $timeout = 1*60; //1mins
        $config
            ->set('instagram_social_feed_passive_timeout', $timeout)
            ->save();
            */


    $time = $config->get('instagram_social_feed_last_run', time());
    if ((REQUEST_TIME - $time) > $timeout) {
      $this->instagram_social_feed_cron();
    }

  //}

  



  $api = $config->get('instagram_social_feed_api_key');
    if (!$api) {
      drupal_set_message('API Key not properly established. Please see the Manage Settings tab.', 'error');
    }

    //TO-DO  30-10-2015
    //drupal_add_js(drupal_get_path('module', 'instagram_social_feed') . '/js/instagram_social_feed.js');
    $header = array('Thumbnail', 'User', 'Caption', 'Timestamp', array('data' => 'Comment of feed', 'field' => 'comment_of_feed',  'sort' => 'DESC'),   'Publish?');

    $query = db_select('instagram_social_feed_photos', 's')
      ->fields('s', array())
      ->orderBy('instagram_id', 'DESC')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(20); // 10 items per page.;
    //TO-DO  30-10-2015
    //$query = $query->extend('PagerDefault')->limit(20);


    //TO-DO  30-10-2015
    //$feed_id = arg(5);
    if (is_numeric($feed_id)) {
      $query->condition('feed_id', $feed_id, '=');
    }

    $results = $query->execute();
    $rows = array();

    while ($row = $results->fetch()) {

     $image_html = SafeMarkup::format('<img src="@thumbnail" data-approved="@approve" />', ['@thumbnail' => $row->thumbnail, '@approve' => $row->approve]);

      $instagram_link = Url::fromUri($row->instagram_link);
      $instagram_user_link = Url::fromUri('http://instagram.com/' . $row->instagram_user);

      $textarea = array(
            '#type' => 'textarea',
            '#resizable' => 'none',
            '#value' => $row->comment_of_feed,
            '#attributes' => array('name' => $row->instagram_id),
          );
      /*
      $checkbox = array(
            '#type' => 'checkbox',
            '#value' => $row->approve,
            '#attributes' => array('name' => $row->id),
          );
      */

      $rows[] = array(
        $this->l($image_html, $instagram_link, array(
          'html' => TRUE,
          'attributes' => array(
            'target' => '_blank'),
          )
        ),
        $this->l($row->instagram_user, $instagram_user_link, array(
          'attributes' => array(
            'target' => '_blank'),
          )
        ),
        //Unicode::truncate(  utf8_decode($row->caption) , 80, FALSE, TRUE),
        Unicode::truncate( $row->caption, 80, FALSE, TRUE),
        date('Y-m-d G:i', $row->created_time),
        drupal_render($textarea),
        //drupal_render($checkbox),
        //date('Y-m-d g:i a', $row->created_time),
        $row->instagram_id,
        //SafeMarkup::format('<input type="checkbox" name="thumbnail_id" value="@value">', ['@name' => $row->thumbnail, '@value' => $row->id])
        //SafeMarkup::format('<input type="checkbox" name="thumbnail_id" value="@value">', ['@name' => $row->thumbnail, '@value' => $row->id])
     );
    }

    /*
    $count = count($rows);
    $markup = '';
    if ($count > 0) {
      $markup = 'Click here to <a href="/admin/config/services/instagram_social_feed/delete">delete all photos</a>.';
    }
    */

    $feed_select = array();
    $feed_query = db_select('instagram_social_feeds', 's')
      //->fields('s', array())
      ->fields('s')
      ->execute();

    $results = $feed_query->fetchAll();
    //$results_count = $feed_query->fetchCol(0);
    $query_count = count($results);


      if ($query_count > 0) {

        $feed_select[''] = 'ALL';
        foreach ($results as $row) {


          //$selected = ($feed_id == $row->id) ? 'selected' : '';
          //$feed_select = array_merge ($feed_select, array( $row->id => $row->hashTag ));
          $feed_select[ $row->id ] = $row->hashTag;
          //$feed_select .= '<option value="' . $row->id . '" ' . $selected . '>' . $row->name . '</option>';
        }
      }


    // Render elements
    // https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21theme.api.php/group/theme_render/8#elements
    $render_array['page_example_arguments'] = array(
      /*   */
        array(
        '#type' => 'select',
        '#options' => $feed_select,
        //'#empty_option' => 'All',
        //'#empty_value' => '',
        '#title' => $this->t('Hashtag:'),
        '#value' => $feed_id,
        /*
        '#ajax' => array(
            'callback' => array($this, 'addMoreCallback'),
            'wrapper' => 'foo-replace',
            'effect' => 'fade',
          ),
        */
        //'#attributes' => array('onchange' => ''),
      ),
     
      array(
        // The theme function to apply to the #items
        //'#theme' => 'table',
        '#type' => 'table',
        // The list itself.
        '#header' => $header, 
        '#rows' => $rows, 
        'caption' => '',
        'colgroups' => array(),
        'sticky' => FALSE,
        'empty' => 'No photos yet',
        '#attributes' => array( 
          'id' => 'feed-table', 
          ), 
      ),
      // Pager is not an element type, use #theme directly.
      array(
        '#type' => 'pager',
        //'#quantity' => 20
      ),
    );

     $render_array['#attached']['library'][] =  'instagram_social_feed/instagram_social_feed.ajaxApproveFeed';


        $config
            ->set('instagram_social_feed_last_run', time())
            ->save();
    return $render_array;
  }


  public function admin() {

    // Make our links. First the simple page.
    $simple_url = Url::fromRoute('page_example_simple');
    $page_example_simple_link = $this->l($this->t('simple page'), $simple_url);
    // Now the arguments page.
    $arguments_url = Url::fromRoute('page_example_arguments', array('first' => '23', 'second' => '56'));
    $page_example_arguments_link = $this->l($this->t('arguments page'), $arguments_url);

    

    
    // Assemble the markup.
    $build = array(
      '#markup' => $this->t('<p>The Page example module provides two pages, "simple" and "arguments".</p><p>The !simple_link just returns a renderable array for display.</p><p>The !arguments_link takes two arguments and displays them, as in @arguments_url</p>',
        array(
          '!simple_link' => $page_example_simple_link,
          '!arguments_link' => $page_example_arguments_link,
          '@arguments_url' => $arguments_url->toString(),
        )
      ),
    );

    return $build;
  }



  function ajaxApproveFeed( ) {

    if (isset($_GET['instagram_id'])) {
      $id = $_GET['instagram_id'];
    }
    else {
      header("HTTP/1.1 500 Internal Server Error");
      exit();
    }

    $result = db_update('instagram_social_feed_photos')
      ->expression('approve', 'IF(approve=1, 0, 1)')
      ->condition('instagram_id', $id)
      ->execute();

      /*
        Actually, nothing needa respone if your client won't handle Ajax call.  26/11/2015
        $response = new AjaxResponse();
        $response->addCommand( new ReadMessageCommand($message)); 
        return $response;
      */
    return new JsonResponse('Successful');
  //  return new JsonResponse($resultsArray);
  }

  function ajaxCommentsFeed( ) {

    if (isset($_GET['instagram_id'])) {
      $id = $_GET['instagram_id'];
    }
    else {
      header("HTTP/1.1 500 Internal Server Error");
      exit();
    }

    if(isset( $_GET['comment']) ){

      $result = db_update('instagram_social_feed_photos')
        ->fields(array('comment_of_feed' => $_GET['comment']))
        ->condition('instagram_id', $id)
        ->execute();
    }

      /*
        Actually, nothing needa respone.  26/11/2015
        $response = new AjaxResponse();
        $response->addCommand( new ReadMessageCommand($message)); 
        return $response;
      */
    return new JsonResponse('Successful');
  //  return new JsonResponse($resultsArray);
  }


  //public function ajaxGetItems( $feed_id = null, $page_number = 0) {
  public function ajaxGetItems() {

    $showItemsPerTimes = 12;

    // Tag: Pizza
    // https://api.instagram.com/v1/tags/pizza/media/recent?access_token=182837742.4c3a973.37043a0716884c5184a064a8420af44b
    $query = db_select('instagram_social_feed_photos', 's')
        ->fields('s', array('created_time', 'low_resolution', 'standard_resolution', 'caption', 'tags','instagram_link','instagram_user','instagram_user_thumbnail', 'comment_of_feed'))
        ->condition('approve', 1)
        ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
        ->limit($showItemsPerTimes) // 10 items per page.;
        ->orderBy('id', 'DESC');

    if( isset($_GET['feed_id']) ){
        $query->condition('feed_id', $_GET['feed_id']);
    }
    
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(s.created_time), '%Y-%m')", 'created_time');
    $results = $query->execute();
    $resultsArray = $results->fetchAll();
   //dpm($results->fetchAll()); 

  // $items[$key]['attributes']['field_publish_date'] = format_date($date, 'short');


    $feed_query = db_select('instagram_social_feeds', 's')
      //->fields('s', array())
      ->fields('s')
      ->execute();
    $tagsResults = $feed_query->fetchAll();

    return new JsonResponse(array( 'tags' => $tagsResults,'data'=> $resultsArray, 'showItemsPerTimes'=> $showItemsPerTimes ));

  /*
    // Assemble the markup.
    $build = array(
      '#theme' => 'Ajax',
      '#markup' => $results,
      );
    return $build;
      */
  }

protected function instagram_social_feed_get_contents($isblock = FALSE) {


    //$config = InstagramSocialFeedSettingsForm->configFactory->get('instagram_social_feed.settings');

    $timeout = $config->get('instagram_social_feed_passive_timeout', '');
    $timeout = intval($timeout) * 60;

    // Check if new data should be requested.
    if ($timeout > 0) {
      $time = $config->get('instagram_social_feed_last_run', time());
      if ((REQUEST_TIME - $time) > $timeout) {
        instagram_social_feed_cron();
      }
    }

    // If we are dealing with a block we get the feed selection
    if ($isblock == TRUE) {
      $feed_id = $config->get('instagram_social_feed_feed_selection', '');
      $query = db_select('instagram_social_feed_photos', 's')
        ->fields('s')
        ->condition('approve', 1)
        ->condition('feed_id', $feed_id)
        ->orderBy('created_time', 'DESC');
    }
    else {
      $query = db_select('instagram_social_feed_photos', 's')
        ->fields('s')
        ->condition('approve', 1)
        ->orderBy('created_time', 'DESC');
    }

    $limit = $config->get('instagram_social_feed_block_count', 0);
    $limit = intval($limit);
    if ($limit) {
      $query->range(0, $limit);
    }

    $results = $query->execute();
    return $results;
  }

  /**
 * Build database query for panel pane options and return the result.
 */
protected function instagram_social_feed_panel_pane_results($options = array()) {

  $result = db_select('instagram_social_feed_photos', 's')
    ->fields('s')
    ->condition('feed_id', $options['feed'], '=')
    ->orderBy('created_time', 'DESC')
    ->range(0, $options['count'])
    ->execute();

  return $result;
}


/**
 * Implements hook_cron().
 */
protected function instagram_social_feed_cron() {


  //$config = \Drupal::config('instagram_social_feed.settings');
  $config = \Drupal::configFactory()->getEditable('instagram_social_feed.settings');
  $access_token = $config->get('instagram_social_feed_api_key');
  if (!$access_token) {
    drupal_set_message(t('Cron could not run because no access token has been created'), 'error');
    return;
  }

  // Select all feeds from the database.
  $result = db_select('instagram_social_feeds', 'f')
    ->fields('f')
    ->condition('enabled', 1, '=')
    ->execute()
    ->fetchAll();


  // For each record in the database:
  foreach ($result as $row) {

    $feed_id = $row->id;

    $text = $row->hashTag;
    $text = str_replace('#', '', $text);
    $instagram_query = "https://api.instagram.com/v1/tags/$text/media/recent?access_token=$access_token";
    $text = "Instagram feed: hashtag " . $text;
    /*
    // Hashtag search.
    if ($type == INSTAGRAM_SOCIAL_FEED_HASHTAG) {
      $text = $row->search_term;
      $text = str_replace('#', '', $text);
      $instagram_query = "https://api.instagram.com/v1/tags/$text/media/recent?access_token=$access_token";
      $text = "Instagram feed: hashtag " . $text;
    }
    // User feed.
    elseif ($type == INSTAGRAM_SOCIAL_FEED_USER_FEED) {
      $instagram_query = "https://api.instagram.com/v1/users/self/feed?access_token=$access_token";
      $text = "Instagram feed: user feed";
    }
    // User photos.
    else {
      $uid = variable_get('instagram_social_feed_user_id', 0);
      $instagram_query = "https://api.instagram.com/v1/users/" . $uid . "/media/recent?access_token=$access_token";
      $text = "Instagram feed: user's own photos";
    }
    */

    $total = 0;
    $instagram_feed = json_decode($this->instagram_social_feed_api_call($instagram_query));
    //dpm($instagram_feed);


    $table = 'instagram_social_feed_photos';
    if (!isset($instagram_feed->data)) {
      return t('%text No items found.', array('%text' => $text));
    }

    foreach ($instagram_feed->data as $feed) {
      // Check if instagram photo already exists based on unix timestamp.
      $sql = "SELECT instagram_id as number FROM {$table} WHERE instagram_id = '{$feed->id}'";
      $query = db_query($sql);
      $results = $query->fetchCol(0);
      $query_count = count($results);
      //$count = $result->rowCount();

      if ($query_count) {
        continue;
      }

      // Return tags as comma delimited string.
      $tags = implode(',', $feed->tags);

      $caption = '';
      if (isset($feed->caption->text)) {
        //$caption = utf8_encode($feed->caption->text);
        $caption = $feed->caption->text;
        /*
        $caption =  preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $feed->caption->text);
        */
      }

      // Rewrite urls to use https.
      $low_resolution = str_replace('http:', 'https:', $feed->images->low_resolution->url);
      $thumbnail = str_replace('http:', 'https:', $feed->images->thumbnail->url);
      $standard_resolution = str_replace('http:', 'https:', $feed->images->standard_resolution->url);
      $data = array(
        'feed_id' => $feed_id,
        'user_id' => $feed->user->id,
        'tags' => Xss::filter($tags),  
        // The Xss::filter method is used to prevent XSS attack from front-end.
        // https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Xss.php/function/Xss%3A%3Afilter/8
        // Time stored in unix epoch format.
        'created_time' => $feed->created_time,
        'low_resolution' => $low_resolution,
        'thumbnail' => $thumbnail,
        'standard_resolution' => $standard_resolution,
        'caption' => Xss::filter($caption),
        'instagram_id' => $feed->id,
        'instagram_link' => $feed->link,
        'instagram_user' => $feed->user->username,
        'instagram_user_thumbnail' => $feed->user->profile_picture,
        'approve' => $row->auto_publish,
      );

      // Insert data into table.
      $result = db_insert($table)->fields($data)->execute();
      $total++;

    }

    $message = t('%text %total items imported.', array(
      '%text' => $text,
      '%total' => $total,
    ));

    drupal_set_message($message);
    //watchdog(__FUNCTION__, $message);
    \Drupal::logger(__FUNCTION__)->notice($message);
  }


  // Set last run variable for passive updating.
        $config
            ->set('instagram_social_feed_last_run', time())
            ->save();
}


protected function instagram_social_feed_api_call($query) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $query);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($curl, CURLOPT_TIMEOUT, 20);
  $result = curl_exec($curl);
  curl_close($curl);

  return $result;
}



  /**
   * Constructs a simple page.
   *
   * The router _controller callback, maps the path
   * 'examples/page_example/simple' to this method.
   *
   * _controller callbacks return a renderable array for the content area of the
   * page. The theme system will later render and surround the content with the
   * appropriate blocks, navigation, and styling.
   */
  public function pageone() {
    return array(
      '#markup' => '<p>' . $this->t('Simple page: The quick brown fox jumps over the lazy dog.') . '</p>',
    );
  }

  /**
   * A more complex _controller callback that takes arguments.
   *
   * This callback is mapped to the path
   * 'examples/page_example/arguments/{first}/{second}'.
   *
   * The arguments in brackets are passed to this callback from the page URL.
   * The placeholder names "first" and "second" can have any value but should
   * match the callback method variable names; i.e. $first and $second.
   *
   * This function also demonstrates a more complex render array in the returned
   * values. Instead of rendering the HTML with theme('item_list'), content is
   * left un-rendered, and the theme function name is set using #theme. This
   * content will now be rendered as late as possible, giving more parts of the
   * system a chance to change it if necessary.
   *
   * Consult @link http://drupal.org/node/930760 Render Arrays documentation
   * @endlink for details.
   *
   * @param string $first
   *   A string to use, should be a number.
   * @param string $second
   *   Another string to use, should be a number.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the parameters are invalid.
   */
  public function pagetwo($first, $second) {
    // Make sure you don't trust the URL to be safe! Always check for exploits.
    if (!is_numeric($first) || !is_numeric($second)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }

    $list[] = $this->t("First number was @number.", array('@number' => $first));
    $list[] = $this->t("Second number was @number.", array('@number' => $second));
    $list[] = $this->t('The total was @number.', array('@number' => $first + $second));

    $render_array['page_example_arguments'] = array(
      // The theme function to apply to the #items
      '#theme' => 'item_list',
      // The list itself.
      '#items' => $list,
      '#title' => $this->t('Argument Information'),
    );
    return $render_array;
  }

}
