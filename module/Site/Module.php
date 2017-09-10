<?php

namespace Site;

use Krystal\Application\Module\AbstractModule;
use Site\Service\UserService;
use Site\Service\ArchitectureService;

final class Module extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    public function getTranslations($language)
    {
        return include(__DIR__ . sprintf('/Translations/%s.php', $language));
    }

    /**
     * Returns routes of this module
     * 
     * @return array
     */
    public function getRoutes()
    {
        return include(__DIR__.'/config/routes.php');
	}

    /**
     * Returns prepared service instances of this module
     * 
     * @return array
     */
    public function getServiceProviders()
    {
        $authManager = $this->getServiceLocator()->get('authManager');

        $userService = new UserService($authManager, $this->createMapper('\Site\Storage\MySQL\UserMapper'));
        $authManager->setAuthService($userService);

        return array(
            'userService' => $userService,
            'architectureService' => new ArchitectureService(
                $this->createMapper('\Site\Storage\MySQL\FloorMapper'), 
                $this->createMapper('\Site\Storage\MySQL\RoomMapper')
            )
        );
    }
}
