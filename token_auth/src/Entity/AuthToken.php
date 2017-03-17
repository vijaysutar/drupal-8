<?php

namespace Drupal\token_auth\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\token_auth\AuthTokenInterface;

/**
 * Defines the Authentication Token entity.
 *
 * @ConfigEntityType(
 *   id = "auth_token",
 *   label = @Translation("Authentication Token"),
 *   handlers = {
 *     "list_builder" = "Drupal\token_auth\AuthTokenListBuilder",
 *     "form" = {
 *       "add" = "Drupal\token_auth\Form\AuthTokenForm",
 *       "edit" = "Drupal\token_auth\Form\AuthTokenForm",
 *       "delete" = "Drupal\token_auth\Form\AuthTokenDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\token_auth\AuthTokenHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "auth_token",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/system/auth_token/{auth_token}",
 *     "add-form" = "/admin/config/system/auth_token/add",
 *     "edit-form" = "/admin/config/system/auth_token/{auth_token}/edit",
 *     "delete-form" = "/admin/config/system/auth_token/{auth_token}/delete",
 *     "collection" = "/admin/config/system/auth_token"
 *   }
 * )
 */
class AuthToken extends ConfigEntityBase implements AuthTokenInterface {
  /**
   * The Authentication Token ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Authentication Token label.
   *
   * @var string
   */
  protected $label;

    /**
     * Is the given auth token enabled or not.
     *
     * @var bool
     */
    protected $status;


    /**
     * .The user id
     *
     * @var int
     */
    protected $TokenAuthUser;

    /**
     * .The created time
     *
     * @var timestatmp
     */
    protected $created;

    /**
     * {@inheritdoc}
     */
    public function status() {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function id() {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function label() {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function token() {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function TokenAuthUser() {
        return $this->TokenAuthUser;
    }

    /**
     * {@inheritdoc}
     */
    public function created() {
        return $this->created;
    }


}
