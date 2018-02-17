<?php

namespace Site;

use Krystal\Application\Module\AbstractModule;
use Krystal\Image\Tool\ImageManager;
use Site\Service\UserService;
use Site\Service\RoomService;
use Site\Service\FacilitiyService;
use Site\Service\FacilityItemDataService;
use Site\Service\PhotoService;
use Site\Service\RoomTypeGalleryService;
use Site\Service\RoomTypeService;
use Site\Service\HotelService;
use Site\Service\RegionService;
use Site\Service\DistrictService;
use Site\Service\DiscountService;
use Site\Service\PaymentSystemService;
use Site\Service\HotelTypeService;
use Site\Service\ServiceManager;
use Site\Service\ReservationServiceManager;
use Site\Service\RoomCategoryService;
use Site\Service\PaymentFieldService;
use Site\Service\ReviewService;

final class Module extends AbstractModule
{
    const PARAM_GALLERY_PATH = '/data/uploads/gallery/';
    const PARAM_ROOM_GALLERY_PATH = '/data/uploads/room-gallery/';
    const PARAM_DEFAULT_IMAGE = '/data/no-image.png';

    /**
     * Returns room gallery service
     * 
     * @return \Site\Service\PhotoService
     */
    private function createRoomTypeGalleryService()
    {
        // Create image service
        $imageManager = new ImageManager(self::PARAM_ROOM_GALLERY_PATH, $this->appConfig->getRootDir(), $this->appConfig->getRootUrl(), [
            'thumb' => [
                'dimensions' => [
                    // Administration area
                    [850, 450],
                    [80, 50]
                ]
            ],
            'original' => [
                'prefix' => 'original'
            ]
        ]);
        
        // Create mapper
        $mapper = $this->createMapper('\Site\Storage\MySQL\RoomTypeGalleryMapper');

        return new RoomTypeGalleryService($mapper, $imageManager);
    }

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
                    array(850, 450),
                    array(80, 50)
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
        $userMapper = $this->createMapper('\Site\Storage\MySQL\UserMapper');

        $userService = new UserService($authManager, $userMapper);
        $authManager->setAuthService($userService);

        return array(
            'userService' => $userService,

            'roomService' => new RoomService(
                $this->createMapper('\Site\Storage\MySQL\RoomMapper'),
                $this->createMapper('\Site\Storage\MySQL\RoomTypeMapper')
            ),

            'facilitiyService' => new FacilitiyService(
                $this->createMapper('\Site\Storage\MySQL\FacilitiyCategoryMapper'), 
                $this->createMapper('\Site\Storage\MySQL\FacilitiyItemMapper')
            ),

            'facilityItemDataService' => new FacilityItemDataService(
                $this->createMapper('\Site\Storage\MySQL\FacilityItemDataMapper')
            ),

            'photoService' => new PhotoService(
                $this->createMapper('\Site\Storage\MySQL\PhotoMapper'),
                $this->getImageManager()
            ),

            'roomTypeService' => new RoomTypeService(
                $this->createMapper('\Site\Storage\MySQL\RoomTypeMapper'),
                $this->createMapper('\Site\Storage\MySQL\RoomTypePriceMapper')
            ),

            'hotelService' => new HotelService(
                $this->createMapper('\Site\Storage\MySQL\HotelMapper'),
                $userMapper
            ),

            'roomTypeGalleryService' => $this->createRoomTypeGalleryService(),

            'regionService' => new RegionService(
                $this->createMapper('\Site\Storage\MySQL\RegionMapper')
            ),

            'discountService' => new DiscountService(
                $this->createMapper('\Site\Storage\MySQL\DiscountMapper')
            ),

            'paymentSystemService' => new PaymentSystemService(
                $this->createMapper('\Site\Storage\MySQL\PaymentSystemMapper')
            ),

            'hotelTypeService' => new HotelTypeService(
                $this->createMapper('\Site\Storage\MySQL\HotelTypeMapper')
            ),

            'serviceManager' => new ServiceManager(
                $this->createMapper('\Site\Storage\MySQL\ServiceMapper'),
                $this->createMapper('\Site\Storage\MySQL\ServicePriceMapper')
            ),
            
            'reservationServiceManager' => new ReservationServiceManager(
                $this->createMapper('\Site\Storage\MySQL\ReservationServiceMapper')
            ),

            'roomCategoryService' => new RoomCategoryService(
                $this->createMapper('\Site\Storage\MySQL\RoomCategoryMapper')
            ),

            'districtService' => new DistrictService(
                $this->createMapper('\Site\Storage\MySQL\DistrictMapper')
            ),

            'paymentFieldService' => new PaymentFieldService(
                $this->createMapper('\Site\Storage\MySQL\PaymentSystemFieldMapper'),
                $this->createMapper('\Site\Storage\MySQL\PaymentSystemFieldDataMapper')
            ),

            'reviewService' => new ReviewService(
                $this->createMapper('\Site\Storage\MySQL\ReviewMapper'),
                $this->createMapper('\Site\Storage\MySQL\ReviewTypeMapper'),
                $this->createMapper('\Site\Storage\MySQL\ReviewMarkMapper')
            )
        );
    }
}
