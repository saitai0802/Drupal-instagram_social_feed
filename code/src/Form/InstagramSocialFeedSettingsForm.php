<?php

namespace Drupal\instagram_social_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Site\Settings;
use Drupal\Core\Form\FormStateInterface;
//For creating system configuration forms like the one found at admin/config/system/site-information.

use Drupal\Core\Url;

/**
 * Defines a form to configure module settings.
 */
// instagram_social_feed.schema.yml
class InstagramSocialFeedSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'instagramsocialfeed_settings';
  }

  public function getInstagramSocialFeedSettings() {
    return $this->configFactory->get('instagram_social_feed.settings');
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['instagram_social_feed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  // \core\lib\Drupal\Core\Form
  public function buildForm(array $form, FormStateInterface $form_state) {

  	// configFactory API
  	//https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Config%21ConfigFactory.php/class/ConfigFactory/8
    $config = $this->configFactory->get('instagram_social_feed.settings');
	$config_variables = $this->configFactory->getEditable('instagram_social_feed.settings');
	$access_key = $config_variables->get('instagram_social_feed_api_key');

    //$config = $this->config('instagram_social_feed.settings');
    //$settings = $config->get();
    /*
    $form['settings'] = array(
      '#tree' => TRUE,
    );
    */


		  if (isset($_GET['code']) && $_GET['code'] != '') {

		      $url = "https://api.instagram.com/oauth/access_token";
		      $fields = array(
		        "client_id" => $config->get('instagram_social_feed_client_id'),
		        "client_secret" => $config->get('instagram_social_feed_client_secret'),
		        "grant_type" => "authorization_code",
		        "redirect_uri" => $config->get('instagram_social_feed_redirect_uri'),
		        "code" => $_GET['code'],
		      );


		      $fields_string = '';
		      foreach ($fields as $key => $value) {
		        $fields_string .= $key . '=' . $value . '&';
		      }
		      rtrim($fields_string, '&');


		      // Request access token.
		      $ch = curl_init();
		      curl_setopt($ch, CURLOPT_URL, $url);
		      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		      curl_setopt($ch, CURLOPT_POST, count($fields));
		      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

		      $output = curl_exec($ch);
		      if(curl_exec($ch) === false)
				{
				    echo 'Curl error: ' . curl_error($ch);
				     ksm( curl_error($ch));
				}

		      curl_close($ch);

		      $auth = json_decode($output);


		      if (empty($auth->error_message)) {

		      	//The variables will be stored in the database during the installation of the module. We define the schema for the variables in config/schema/*.settings.yml:

		        $config_variables
			      ->set('instagram_social_feed_api_key', $auth->access_token)
			      ->set('instagram_social_feed_user_id', $auth->user->id)
			      ->set('instagram_social_feed_username', $auth->user->username)
			      ->save();
			      
		        $access_key = $auth->access_token;
		        drupal_set_message(t('Instagram authentication successful'));
		      }
		      else {
		        drupal_set_message($auth->error_message, 'error');
		      }
		  } elseif (array_key_exists('code', $_GET) && $_GET['code'] == '') {

			    // Remove api key for re-authentication.
			    $config_variables->clear('instagram_social_feed_api_key')->save();
			    // Unset variable for form.
			    $access_key = '';
		}


	
	if( $access_key == '' ){


	    $form['instagram_social_feed_client_id'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Instagram Client ID'),
	      '#default_value' =>  $config->get('instagram_social_feed_client_id'),
	      '#size' => 60,
	      '#maxlength' => 255,
	      '#description' => t('You must register an Instagram client key to use this module. You can register a client by <a href="http://instagram.com/developer/clients/manage/" target="_blank">clicking here</a>.'),
	    );
	    $form['instagram_social_feed_client_secret'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Instagram Client Secret'),
	      '#default_value' =>  $config->get('instagram_social_feed_client_secret'),
	      '#size' => 60,
	      '#maxlength' => 255,
	      '#description' => t('The client secret can be found after creating an Instagram client in the API console.'),
	    );
	    $form['instagram_social_feed_redirect_uri'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Instagram Redirect URI'),
	      '#default_value' =>  $config->get('instagram_social_feed_redirect_uri'),
	      '#size' => 60,
	      '#maxlength' => 255,
	      '#description' => t('Set the redirect URI to :url', array(
	        ':url' => 'http://' . $_SERVER['SERVER_NAME'] . '/admin/config/services/instagram_social_feed/settings',
	      )),
	    );

	    //It will call back us a code
	    $url = Url::fromUri( 'https://api.instagram.com/oauth/authorize/?client_id=' . $config->get('instagram_social_feed_client_id') . '&redirect_uri=' . $config->get('instagram_social_feed_redirect_uri') . '&response_type=code');

	    if ($config->get('instagram_social_feed_client_id') != '' && $config->get('instagram_social_feed_redirect_uri') != '') {
	      $form['authenticate'] = array(
	        '#markup' => \Drupal::l( t('Click here to authenticate via Instagram and create an access token') , $url)
	      );
	    }
	}else{


	    // Authenticated user settings form.
	    $form['instagram_social_feed_api_key'] = array(
	      '#type' => 'textfield',
	      '#title' => t('Instagram API Key'),
	      '#default_value' => $config->get('instagram_social_feed_api_key'),
	      '#size' => 60,
	      '#maxlength' => 255,
	      '#disabled' => TRUE,
	      '#description' => t('Stored access key for accessing the API key'),
	    );

	    $url = Url::fromUserInput('/admin/config/media/instagram/admin/settings', array( 'query' => array('code' => '') ));

	    $form['authenticate'] = array(
	      '#markup' => \Drupal::l( t('Click here to remove the access key and re-authenticate via Instagram'),   $url )
	    );
	      // array(
	          //'query' => array('code' => ''),
	        //)
	}


    return parent::buildForm($form, $form_state);
  }

  /**
   * Compares the submitted settings to the defaults and unsets any that are equal. This was we only store overrides.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  	// Get config factory
  	// Getting config from \dev\modules\instagram_social_feed\config\schema 

  	$config = $this->config('instagram_social_feed.settings');
  	$access_key = $config->get('instagram_social_feed_api_key');


  	if( $access_key == ''){

  		$form_clientID = $form_state->getValue('instagram_social_feed_client_id');
  		$form_secret = $form_state->getValue('instagram_social_feed_client_secret');
  		$form_uri = $form_state->getValue('instagram_social_feed_redirect_uri');

	    $config
	      ->set('instagram_social_feed_client_id', $form_clientID)
	      ->set('instagram_social_feed_client_secret', $form_secret)
	      ->set('instagram_social_feed_redirect_uri', $form_uri)
	      ->save();
    }

	//dpm($form_state->getValue('instagram_social_feed_client_id'));
    parent::submitForm($form, $form_state);

  }
}