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
            )
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
