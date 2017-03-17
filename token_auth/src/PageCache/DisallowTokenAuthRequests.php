<?php

namespace Drupal\token_auth\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy for pages served from basic auth.
 *
 * This policy disallows caching of requests that use basic_auth for security
 * reasons. Otherwise responses for authenticated requests can get into the
 * page cache and could be delivered to unprivileged users.
 */
class DisallowTokenAuthRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {

    $token_auth = $request->headers->get('Authorization');

    if (isset($token_auth)) {
      return self::DENY;
    }
  }

}
