<?php

namespace Drupal\token_auth\Authentication\Provider;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Authentication\AuthenticationProviderChallengeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\user\UserAuthInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;



/**
 * Class TokenAuth.
 *
 * @package Drupal\token_auth\Authentication\Provider
 */
class TokenAuth implements AuthenticationProviderInterface, AuthenticationProviderChallengeInterface
{
    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;


    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructs a HTTP basic authentication provider object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory.
     * @param \Drupal\user\UserAuthInterface $user_auth
     *   The user authentication service.
     * @param \Drupal\Core\Flood\FloodInterface $flood
     *   The flood service.
     * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
     *   The entity manager service.
     */
    public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager) {
        $this->configFactory = $config_factory;
        $this->entityManager = $entity_manager;
    }

    /**
     * Checks whether suitable authentication credentials are on the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @return bool
     *   TRUE if authentication credentials suitable for this provider are on the
     *   request, FALSE otherwise.
     */
    public function applies(Request $request)
    {
        // If you return TRUE and the method Authentication logic fails,
        // you will get out from Drupal navigation if you are logged in.


        // Check for the presence of the token.
        if($this->hasTokenValue($request)){
            return $this->isCorrectToken($request);
        }else{
            return FALSE;
        }
    }


    /**
     * Check return presence
     *
     * @param Request $request
     * @return bool
     */
    public static function hasTokenValue(Request $request){
        //Check the header.
        $header =  trim($request->headers->get('Authorization','',true));

        if(strpos($header, 'Token_Auth ')!==FALSE){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request)
    {
        // Get token
        $header =  trim($request->headers->get('Authorization','',true));
        $token_arr = explode('Token_Auth ', $header);


        $token = $request->get('token');
        $query = \Drupal::entityQuery('auth_token')
            ->condition('token', $token_arr[1])
            ->condition('status', TRUE);
        $result = $query->execute();

        if(count($result)){

            foreach ($result as $token_value){
                $token = $this->entityManager->getStorage('auth_token')->load($token_value);
            }

            $uid = $token->get('TokenAuthUser');
            $accounts = $this->entityManager->getStorage('user')->loadByProperties(array('uid' => $uid, 'status' => 1));
            $account = reset($accounts);
            if ($account) {
                return $this->entityManager->getStorage('user')->load($uid);
            }


        }else{
            throw new AccessDeniedHttpException();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(Request $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof AccessDeniedHttpException) {
            $event->setException(new UnauthorizedHttpException('Invalid consumer origin.', $exception));
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check token is valid or not.
     *
     * @param Request $request
     * @return bool
     */
    protected function isCorrectToken(Request $request)
    {

        // Get token
        $header =  trim($request->headers->get('Authorization','',true));
        $token_arr = explode('Token_Auth ', $header);

        if($token_arr[1]!=''){
            $query = \Drupal::entityQuery('auth_token')
                ->condition('token', $token_arr[1])
                ->condition('status', TRUE);
            $token_ids = $query->execute();

            if (count($token_ids)) {
                return TRUE;
            }
        }else{
            return FALSE;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function challengeException(Request $request, \Exception $previous) {
        $site_name = $this->configFactory->get('system.site')->get('name');
        $challenge = SafeMarkup::format('Basic realm="@realm"', array(
            '@realm' => !empty($site_name) ? $site_name : 'Access restricted',
        ));
        return new UnauthorizedHttpException((string) $challenge, 'No authentication credentials provided.', $previous);
    }
}
