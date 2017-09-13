<?php

namespace Site;

use Krystal\Application\Module\AbstractModule;
use Krystal\Image\Tool\ImageManager;
use Site\Service\UserService;
use Site\Service\ArchitectureService;
use Site\Service\FacilitiyService;
use Site\Service\PhotoService;

final class Module extends AbstractModule
{
    const PARAM_GALLERY_PATH = '/data/uploads/gallery/';

    /**
     * Returns product image manager
     * 
     * @return \Krystal\Image\Tool\ImageManager
     */
    private function getImageManager()
    {
        $plugins = array(
            'thumb' => array(
                'dimensions' => array(
                    // Administration area
                    array(200, 200)
                )
            ),
            'original' => array(
                'prefix' => 'original'
            )
        );

        return new ImageManager(
            self::PARAM_GALLERY_PATH,
            $this->appConfig->getRootDir(),
            $this->appConfig->getRootUrl(),
            $plugins
        );
    }

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
        return include(__DIR__.'/Config/routes.php');
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
                $this->createMapper('\Site\Storage\MySQL\RoomMapper'),
                $this->createMapper('\Site\Storage\MySQL\RoomTypeMapper')
            ),

            'facilitiyService' => new FacilitiyService(
                $this->createMapper('\Site\Storage\MySQL\FacilitiyCategoryMapper'), 
                $this->createMapper('\Site\Storage\MySQL\FacilitiyItemMapper')
            ),

            'photoService' => new PhotoService(
                $this->createMapper('\Site\Storage\MySQL\PhotoMapper'),
                $this->getImageManager()
            )
        );
    }
}
