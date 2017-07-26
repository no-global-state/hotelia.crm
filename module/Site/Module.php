<?php

namespace Site;

use Krystal\Application\Module\AbstractModule;
use Site\Service\UserService;
use Site\Storage\Memory\UserMapper;

final class Module extends AbstractModule
{
    /**
     * Returns routes of this module
     * 
     * @return array
     */
    public function getRoutes()
    {
        return array(
            '/site/captcha/(:var)' => array(
                'controller' => 'Site@captchaAction'
            ),

            '/' => array(
                'controller' => 'Site@indexAction'
            ),

            '/hello/(:var)' => array(
                'controller' => 'Site@helloAction',
            ),

            '/contact' => array(
                'controller' => 'Contact@indexAction'
            ),
            
            '/auth/login' => array(
                'controller' => 'Auth@indexAction'
            ),
            '/auth/logout' => array(
                'controller' => 'Auth@logoutAction'
            ),
            '/register' => array(
                'controller' => 'Register@indexAction'
            ),

            '/architecture' => array(
                'controller' => 'Architecture:Grid@indexAction'
            ),

            '/architecture/view/(:var)' => array(
                'controller' => 'Architecture:Grid@floorAction'
            ),
            
            '/architecture/room/add' => array(
                'controller' => 'Architecture:Room@saveAction'
            ),
            
            '/architecture/room/save' => array(
                'controller' => 'Architecture:Room@saveAction'
            ),
            
            '/architecture/room/edit/(:var)' => array(
                'controller' => 'Architecture:Room@editAction'
            ),

            '/architecture/room/delete/(:var)' => array(
                'controller' => 'Architecture:Room@deleteAction'
            ),

            '/architecture/floor/add' => array(
                'controller' => 'Architecture:Floor@saveAction'
            ),
            
            '/architecture/floor/save' => array(
                'controller' => 'Architecture:Floor@saveAction'
            ),
            
            '/architecture/floor/edit/(:var)' => array(
                'controller' => 'Architecture:Floor@editAction'
            ),

            '/architecture/floor/delete/(:var)' => array(
                'controller' => 'Architecture:Floor@deleteAction'
            ),
            
        );
    }

    /**
     * Returns prepared service instances of this module
     * 
     * @return array
     */
    public function getServiceProviders()
    {
        $authManager = $this->getServiceLocator()->get('authManager');

        $userService = new UserService($authManager, new UserMapper());
        $authManager->setAuthService($userService);

        return array(
            'userService' => $userService
        );
    }
}
