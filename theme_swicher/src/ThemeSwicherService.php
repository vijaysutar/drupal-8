<?php

namespace Drupal\theme_swicher;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;


/**
 * Class ThemeSwicherService.
 *
 * @package Drupal\theme_swicher
 */
class ThemeSwicherService implements ThemeSwicherServiceInterface, ThemeNegotiatorInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {

    }


    /**
     * @param RouteMatchInterface $route_match
     * @return bool
     */
    public function applies(RouteMatchInterface $route_match)
    {
        return $this->negotiateRoute($route_match) ? true : false;
    }

    /**
     * @param RouteMatchInterface $route_match
     * @return null|string
     */
    public function determineActiveTheme(RouteMatchInterface $route_match)
    {
        return $this->negotiateRoute($route_match) ?: null;
    }

    /**
     * Function that does all of the work in selecting a theme
     * @param RouteMatchInterface $route_match
     * @return bool|string
     */
    private function negotiateRoute(RouteMatchInterface $route_match)
    {
        $userRolesArray = \Drupal::currentUser()->getRoles();
        //if ($route_match->getRouteName() == 'user.login') {
          //  return 'seven';
        //}
//        elseif ($route_match->getRouteName() == 'some.other.route') {
//            return 'some_other_theme';
//        } elseif (in_array("administrator", $userRolesArray)) {
//            return 'seven';
//        }
       if (in_array("anonymous", $userRolesArray)) {
            return 'seven';
        }
        return false;
    }

}
