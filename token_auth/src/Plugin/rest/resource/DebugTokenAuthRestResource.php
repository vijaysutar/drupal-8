<?php

namespace Drupal\token_auth\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "debug_token_auth_rest_resource",
 *   label = @Translation("Debug token auth rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/token_auth/debug"
 *   }
 * )
 */
class DebugTokenAuthRestResource extends ResourceBase {
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

//      print_r($current_user);
//      die;

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
      $container->get('logger.factory')->get('token_auth'),
      $container->get('current_user')
    );
  }
  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('restful get debug_token_auth_rest_resource')) {
      throw new AccessDeniedHttpException();
    }

    $uid = $this->currentUser->id();
    $user = User::load($uid);

    // remove cache
    $build = array(
          '#cache' => array(
              'max-age' => 0,
          ),
      );
    return (new ResourceResponse($user))->addCacheableDependency($build);
  }

}
