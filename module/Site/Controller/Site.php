<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Site\Service\SummaryService;
use Krystal\Iso\ISO3166\Country;

final class Site extends AbstractSiteController
{
    /**
     * Switches a language
     * 
     * @param string $code
     * @return void
     */
    public function languageAction(string $code)
    {
        $id = $this->getModuleService('languageService')->findIdByCode($code);

        if ($id) {
            $this->request->getCookieBag()->set(self::PARAM_COOKIE_LANG_ID, $id);
            $this->request->getCookieBag()->set(self::PARAM_COOKIE_LANG_CODE, $code);
        }

        $this->response->redirectToPreviousPage();
    }

    /**
     * Sets price group
     * 
     * @param int $priceGroupId
     * @return void
     */
    public function priceGroupAction(int $priceGroupId) : void
    {
        $this->setPriceGroupId($priceGroupId);
        $this->response->redirectToPreviousPage();
    }

    /**
     * Performs a calculation
     * 
     * @return string
     */
    public function calculate()
    {
        $params = $this->request->getQuery();

        $price = $this->getModuleService('roomTypeService')->countPrice(
            $params['uniq-id'], 
            $params['qty'], 
            $params['arrival'], 
            $params['departure']
        );

        $summary = new SummaryService($this->sessionBag);

        // If to remove
        if ($params['qty'] == 0) {
            $summary->remove($params['uniq-id']);
        } else {
            // Otherwise append
            $summary->append($params['room-type-id'], $params['uniq-id'], $params['qty'], $params['capacity'], $price);
        }

        // Get hotel data
        $hotel = $this->getModuleService('hotelService')->fetchById($params['hotel_id'], $this->getCurrentLangId(), $this->getPriceGroupId());
        $discount = $hotel['discount'];

        return $this->json($summary->getSummary($discount));
    }

    /**
     * Leaves a review
     * 
     * @return void
     */
    public function reviewAction() : void
    {
        $data = $this->request->getPost();

        // Review service
        $reviewService = $this->getModuleService('reviewService');
        $reviewService->add($this->getCurrentLangId(), $this->getHotelId(), $data);

        $this->flashBag->set('success', 'Your review has been posted');
        $this->response->redirectToPreviousPage();
    }

    /**
     * Create rooms
     * 
     * @param int $hotelId
     * @return array
     */
    private function createSummary(int $hotelId)
    {
        // Clear previous summary if any
        $summary = (new SummaryService($this->sessionBag))->getData();

        return $this->getModuleService('roomTypeService')->createSummary($summary, $this->getPriceGroupId(), $hotelId, $this->getCurrentLangId());
    }

    /**
     * Renders payment page
     * 
     * @return string
     */
    public function paymentAction()
    {
        // Validate request
        if ($this->request->hasQuery('type_id', 'hotel_id', 'arrival', 'departure')) {

            // Request variables
            $typeId = $this->request->getQuery('type_id');
            $hotelId = $this->request->getQuery('hotel_id');
            $arrival = $this->request->getQuery('arrival');
            $departure = $this->request->getQuery('departure');
            $rooms = $this->request->getQuery('rooms', 1);
            $adults = $this->request->getQuery('adults', 1);
            $kids = $this->request->getQuery('kids', 0);
            $qty = $this->request->getQuery('qty', 1);

            $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId(), $this->getPriceGroupId());

            // Room details
            $room = $this->getModuleService('roomTypeService')->findByTypeId($typeId, $this->getPriceGroupId(), $hotelId, $this->getCurrentLangId());

            return $this->view->render('payment', [
                'countries' => (new Country)->getAll(),
                'rooms' => $rooms,
                'adults' => $adults,
                'kids' => $kids,
                'arrival' => $arrival,
                'departure' => $departure,
                'room' => $room,
                'selectedRooms' => $this->createSummary($hotelId),
                'selectedSummary' => (new SummaryService($this->sessionBag))->getSummary(),
                'hotel' => $hotel,
                'summary' => ReservationService::calculateStayPrice($arrival, $departure, $room['price']),
                'qty' => $qty
            ]);

        } else {
            // Invalid request
            die('Invalid');
        }
    }

    /**
     * Renders booking page
     * 
     * @return string
     */
    public function bookAction()
    {
        // Request variables
        $typeId = $this->request->getQuery('type_id');
        $hotelId = $this->request->getQuery('hotel_id');
        $arrival = $this->request->getQuery('arrival');
        $departure = $this->request->getQuery('departure');
        $rooms = $this->request->getQuery('rooms', 1);
        $adults = $this->request->getQuery('adults', 1);
        $kids = $this->request->getQuery('kids', 0);

        $room = $this->getModuleService('roomTypeService')->findByTypeId($typeId, $this->getPriceGroupId(), $hotelId, $this->getCurrentLangId());
        $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId(), $this->getPriceGroupId());

        // Find all attached room photos
        $gallery = $this->getModuleService('roomTypeGalleryService')->fetchAll($typeId);

        // If not explicitly provided images, fall back to defaults
        if (empty($gallery)) {
            $gallery = $this->getModuleService('photoService')->fetchAll($hotelId, PhotoService::PARAM_IMAGE_SIZE_LARGE, 5);
        }

        // Extra available rooms
        $availableRooms = $this->getModuleService('roomTypeService')->findAvailableTypes($arrival, $departure, $this->getPriceGroupId(), $this->getCurrentLangId(), $hotelId);

        return $this->view->render('book', [
            // Request variables
            'rooms' => $rooms,
            'adults' => $adults,
            'kids' => $kids,
            'hotelId' => $hotelId,
            'typeId' => $typeId,
            'arrival' => $arrival,
            'departure' => $departure,
            'hotel' => $hotel,
            'room' => $room,
            'gallery' => $gallery,
            'summary' => ReservationService::calculateStayPrice($arrival, $departure, $room['price']),
            'availableRooms' => $availableRooms
        ]);
    }

    /**
     * Tweaks paginator's instance
     * 
     * @param \Krystal\Paginate\PaginatorInterface $paginator
     * @return void
     */
    private function tweakPaginator($paginator)
    {
        $placeholder = '(:var)';

        $url =  '/search/?'.$this->request->buildQuery(array('page' => $placeholder));
        $url = str_replace(rawurlencode($placeholder), $placeholder, $url);

        $paginator->setUrl($url);
    }

    /**
     * Search action
     * 
     * @return string
     */
    public function searchAction()
    {
        // Request variables
        $regionId = $this->request->getQuery('region_id');
        $typeIds = $this->request->getQuery('type', []);
        $facilityIds = $this->request->getQuery('facility', []);
        $pricesIds = $this->request->getQuery('prices', []);
        $arrival = $this->request->getQuery('arrival');
        $departure = $this->request->getQuery('departure');
        $rate = $this->request->getQuery('rate', 0);
        $priceStart = $this->request->getQuery('price-start', 10);
        $priceStop = $this->request->getQuery('price-stop', 100);
        $rooms = $this->request->getQuery('rooms', 1);
        $adults = $this->request->getQuery('adults', 1);
        $kids = $this->request->getQuery('kids', 0);
        $stars = $this->request->getQuery('stars', []);

        // Sorting param
        $sort = $this->request->getQuery('sort', 'discount');

        // Create region data based on its ID
        if ($regionId) {
            $region = $this->getModuleService('regionService')->fetchById($regionId, $this->getCurrentLangId());
        } else {
            $region = null;
        }

        $hotels = $this->getModuleService('hotelService')->findAll($this->getCurrentLangId(), $this->getPriceGroupId(), $this->request->getQuery(), $sort);
        $hotels = $this->getModuleService('facilitiyService')->appendFacilityMapToHotels($hotels);

        // Paginator instance
        $paginator = $this->getModuleService('hotelService')->getPaginator();
        $this->tweakPaginator($paginator);

        return $this->view->render('search', [
            'region' => $region,

            // Request variables
            'stars' => $stars,
            'regionId' => $regionId,
            'typeIds' => $typeIds,
            'facilityIds' => $facilityIds,
            'priceIds' => $pricesIds,
            'arrival' => $arrival,
            'departure' => $departure,
            'rate' => $rate,
            'priceStart' => $priceStart,
            'priceStop' => $priceStop,
            'rooms' => $rooms,
            'adults' => $adults,
            'kids' => $kids,
            'sort' => $sort,

            'paginator' => $paginator,
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchAllWithCount($this->getCurrentLangId()),
            'hotels' => $hotels,
            'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
            'facilities' => $this->getModuleService('facilitiyService')->getItemList(null, $this->getCurrentLangId(), true)
        ]);
    }

    /**
     * Renders hotel information
     * 
     * @return string
     */
    public function hotelAction()
    {
        // Hotel ID is a must
        if (!$this->request->hasQuery('hotel_id')) {
            return false;
        }

        // Clear previous summary if any
        $summary = new SummaryService($this->sessionBag);
        $summary->clear();

        // Request variables
        $arrival = $this->request->getQuery('arrival', ReservationService::getToday());
        $departure = $this->request->getQuery('departure', ReservationService::addOneDay(ReservationService::getToday()));
        $hotelId = $this->request->getQuery('hotel_id'); // Hotel ID
        $typeId = $this->request->getQuery('type_id', null);
        $rooms = $this->request->getQuery('rooms', 1);
        $adults = $this->request->getQuery('adults', 1);
        $kids = $this->request->getQuery('kids', 0);

        $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId(), $this->getPriceGroupId());

        $photoService = $this->getModuleService('photoService');
        $roomTypeService = $this->getModuleService('roomTypeService');

        $availableRooms = $roomTypeService->findAvailableTypes($arrival, $departure, $this->getPriceGroupId(), $this->getCurrentLangId(), $hotelId, $typeId, true);
        $types = $roomTypeService->fetchList($this->getCurrentLangId(), $hotelId);

        return $this->view->render('hotel', [
            'roomTypes' => $this->getModuleService('roomTypeService')->fetchList($this->getCurrentLangId(), $hotelId),

            // Renders variables
            'typeId' => $typeId,
            'arrival' => $arrival,
            'departure' => $departure,
            'rooms' => $rooms,
            'adults' => $adults,
            'kids' => $kids,

            'regions' => $this->getModuleService('regionService')->fetchList($this->getCurrentLangId()),
            'availableRooms' => $availableRooms,
            'types' => $types,
            'hotel' => $hotel,
            // Similar hotels
            'hotels' => $this->getModuleService('hotelService')->findAll($this->getCurrentLangId(), $this->getPriceGroupId(), ['region_id' => $hotel['region_id']], 5),
            'reviewTypes' => $this->getModuleService('reviewService')->findTypes(),
            'reviews' => $this->getModuleService('reviewService')->fetchAll($hotelId),

            'hotelId' => $hotelId,
            'regionId' => $hotel['region_id'],

            'facilities' => $this->getModuleService('facilitiyService')->getCollection($this->getCurrentLangId(), true, $hotelId, true),
            'facilityMap' => $this->getModuleService('facilitiyService')->fetchSingleRelation($hotelId),

            // Hotel images
            'images' => [
                'large' => $photoService->fetchAll($hotelId, PhotoService::PARAM_IMAGE_SIZE_LARGE),
                'small' => $photoService->fetchAll($hotelId, PhotoService::PARAM_IMAGE_SIZE_SMALL)
            ]
        ]);
    }

    /**
     * Renders home page
     * 
     * @return string
     */
    public function homeAction()
    {
        return $this->view->render('home', [
            // Defaults
            'adults' => 1,
            'kids' => 0,
            'rooms' => 1,
            
            'home' => true,
            'regions' => $this->getModuleService('regionService')->findHotels($this->getCurrentLangId())
        ]);
    }

    /**
     * Renders a CAPTCHA
     * 
     * @return void
     */
    public function captchaAction()
    {
        $this->captcha->render();
    }

    /**
     * This action gets executed when a request to non-existing route has been made
     * 
     * @return string
     */
    public function notFoundAction()
    {
        return $this->view->render('404');
    }
}
