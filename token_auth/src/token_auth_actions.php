<?php
/**
 * Created by PhpStorm.
 * User: vijayvasantsutar
 * Date: 15/3/17
 * Time: 3:50 PM
 */

namespace Drupal\token_auth;


use Drupal\Component\Utility\Crypt;
use Drupal\token_auth\Entity\AuthToken;

class token_auth_actions
{
    /**
     * Create Auth Token
     *
     * @param $uid
     * @return \Drupal\Core\Entity\EntityInterface|static
     */
    public function createAuthToken($uid){
        $entity = AuthToken::create(
            [
                'label' => 'token',
                'id' => time(),
                'status' => true,
                'TokenAuthUser'=> $uid,
                "token" => Crypt::randomBytesBase64(),
                "created" => time()
            ]
        );
        $entity->save();
        return $entity;
    }
}