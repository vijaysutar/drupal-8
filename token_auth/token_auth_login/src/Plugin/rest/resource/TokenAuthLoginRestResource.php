<?php

namespace Drupal\token_auth_login\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\token_auth\token_auth_actions;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "token_auth_login_rest_resource",
 *   label = @Translation("Token auth login rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/token_auth/token/login",
 *     "https://www.drupal.org/link-relations/create" = "/api/token_auth/token/login"
 *   }
 * )
 */
class TokenAuthLoginRestResource extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('token_auth_login'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data=[]) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('restful post token_auth_login_rest_resource')) {
      throw new AccessDeniedHttpException();
    }

    $access_token = $data['access_token'];
    $login_method = $data['login_method'];

    if($login_method=='facebook'){
        $message = $this->facebookLogin($access_token);
    }

    return new ResourceResponse($message);
  }

    /**
     * Login with facebook
     *
     * @param $access_token
     * @return array
     */
  public function facebookLogin($access_token){
      $fb = new \Facebook\Facebook([
          'app_id' => '1190743397662151',
          'app_secret' => '4165e9409d9c7a7e8bf0675ec33b2d94',
          'default_graph_version' => 'v2.8'
      ]);

      try {
          // Get the \Facebook\GraphNodes\GraphUser object for the current user.
          // If you provided a 'default_access_token', the '{access-token}' is optional.
          $response = $fb->get('/me?fields=name,email,location,gender,birthday,hometown', $access_token);
      } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          $message=array(
              'message' => 'Graph returned an error: ' . $e->getMessage(),
              'status' => 0
          );
          return $message;
      } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          $message=array(
              'message' => 'Facebook SDK returned an error: ' . $e->getMessage(),
              'status' => 0
          );
          return $message;
      }

      // Get user details from facebook.
      $me = $response->getGraphUser();



      if($me['name']!='' && $me['email']!=''){

          // Check mail is present or not.
          $query = \Drupal::entityQuery('user');
          $query->condition('mail', $me['email']);
          $result = $query->execute();

          if (count($result)) {
              //Load user.
              $user =  user_load_by_mail($me['email']);
          }else{
              // Register user
              $me['profile_picture'] = $this->getFacebookProfilePicture($me);
              $user =  $this->createUser($me);
          }
          // create token.
          $auth_token = token_auth_actions::createAuthToken($user->id());

          //return response
          $message = array(
              'token_type' => 'Token_Auth',
              'access_token' => $auth_token->get('token'),
              'status' => 1
          );
          return $message;
      }else{
          $message=array(
              'message' => 'Require name and email address of user, please accept permission to fetch details from Facebook.',
              'status' => 0
          );
          return $message;
      }
  }

    /**
     * Create User.
     *
     * @param $me
     * @return \Drupal\Core\Entity\EntityInterface|static
     */
  public function createUser($me){
      $user = User::create();

      //Mandatory settings
      $user->setPassword(time());
      $user->enforceIsNew();
      $user->setEmail($me['email']);
      $user->setUsername($me['email']); //This username must be unique and accept only a-Z,0-9, - _ @ .
      $user->addRole('parent');

      //Optional settings
      $user->set("field_full_name", $me['name']);
      if($me['birthday']!=''){
          $user->set("field_dob", date('Y-m-d',strtotime($me['birthday'])));
      }
      if(!empty($me['location'])){
          $user->set("field_city", $me['location']['name']);
      }
      if(!empty($me['gender'])){
          $gender = 1;
          if($me['gender']=='female'){
              $gender = 2;
          }
          $user->set("field_gender", $gender);
      }
      $user->set("field_profile_picture", $me['profile_picture']);

      $user->activate();
      $res = $user->save();

      return $user;
  }

    /**
     * Get facebook profile picture.
     *
     * @param $me
     * @return int|null|string
     */
  public function getFacebookProfilePicture($me){

      $img = file_get_contents('https://graph.facebook.com/'.$me['id'].'/picture?type=large');
      $file_url = sys_get_temp_dir().'/'.time().'.jpg';
      file_put_contents($file_url, $img);

      $handle = fopen($file_url, 'r');
      $file = file_save_data($handle, 'public://',FILE_EXISTS_RENAME);

      return $file->id();
  }


}
