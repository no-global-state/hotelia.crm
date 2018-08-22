<?php

namespace Site\Controller;

use Site\Service\ReservationService;
use Site\Service\PhotoService;
use Site\Service\BedService;

trait HotelTrait
{
    /**
     * Appends base URL
     * 
     * @param string $target
     * @return string
     */
    protected function appendBaseUrl($target)
    {
        if (!empty($target)){
            return sprintf('%s/%s/%s', $this->request->getBaseUrl(), '/module/Site/View/Template/site/', $target);
        }
    }

    /**
     * Appends base URL
     * 
     * @param string $target
     * @return mixed
     */
    protected function appendUploadUrl($target)
    {
        if (!empty($target)){
            return sprintf('%s/%s', $this->request->getBaseUrl(), $target);
        }
    }

    /**
     * Normalizes image collection
     * 
     * @param array $collection
     * @param string $key
     * @return array
     */
    protected function normalizeImagePath(array $collection, string $key) : array
    {
        foreach ($collection as &$item) {
            if (isset($item[$key])) {
                $item[$key] = $this->appendUploadUrl($item[$key]);
            }
        }

        return $collection;
    }

    /**
     * Tweaks paginator's instance
     * 
     * @param \Krystal\Paginate\PaginatorInterface $paginator
     * @return void
     */
    protected function tweakPaginator($paginator)
    {
        $placeholder = '(:var)';

        $url =  '/search/?'.$this->request->buildQuery(array('page' => $placeholder));
        $url = str_replace(rawurlencode($placeholder), $placeholder, $url);

        $paginator->setUrl($url);
    }

    /**
     * Search hotels
     * 
     * @param int $priceGroupId
     * @param int $langId
     * @return array
     */
    protected function searchAll(int $priceGroupId, int $langId) : array
    {
        // Request variables
        $regionId = $this->request->getQuery('region_id');
        $typeIds = $this->request->getQuery('type', []);
        $facilityIds = $this->request->getQuery('facility', []);
        $pricesIds = $this->request->getQuery('prices', []);
        $arrival = $this->request->getQuery('arrival', ReservationService::getToday());
        $departure = $this->request->getQuery('departure', ReservationService::addOneDay(ReservationService::getToday()));
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
            $region = $this->getModuleService('regionService')->fetchById($regionId, $langId);
        } else {
            $region = null;
        }

        $hotels = $this->getModuleService('hotelService')->findAll($langId, $priceGroupId, $this->request->getQuery(), $sort);
        $hotels = $this->getModuleService('facilitiyService')->appendFacilityMapToHotels($hotels);

        foreach ($hotels as &$hotel) {
            $hotel['cover'] = $this->appendUploadUrl($hotel['cover']);

            // Dummy
            $hotel['penality_enabled'] = true;
            $hotel['has_free_rooms'] = true;
        }

        // Paginator instance
        $paginator = $this->getModuleService('hotelService')->getPaginator();
        $this->tweakPaginator($paginator);

        return [
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
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchAllWithCount($langId),
            'hotels' => $hotels,
            'regions' => $this->getModuleService('regionService')->fetchList($langId),
            'facilities' => $this->getModuleService('facilitiyService')->getItemList(null, $langId, true)
        ];
    }

    /**
     * Finds hotel data
     * 
     * @param int $priceGroupId
     * @param int $langId
     * @return array
     */
    protected function findHotel(int $priceGroupId, int $langId)
    {
        // Hotel ID is a must
        if (!$this->request->hasQuery('hotel_id')) {
            return false;
        }

        // Request variables
        $arrival = $this->request->getQuery('arrival', ReservationService::getToday());
        $departure = $this->request->getQuery('departure', ReservationService::addOneDay(ReservationService::getToday()));
        $hotelId = $this->request->getQuery('hotel_id'); // Hotel ID
        $typeId = $this->request->getQuery('type_id', null);
        $rooms = $this->request->getQuery('rooms', 1);
        $adults = $this->request->getQuery('adults', 1);
        $kids = $this->request->getQuery('kids', 0);

        // Find hotel
        $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $langId, $priceGroupId);

        if (isset($hotel['cover'])) {
            $hotel['cover'] = $this->appendUploadUrl($hotel['cover']);
        }

        $photoService = $this->getModuleService('photoService');
        $roomTypeService = $this->getModuleService('roomTypeService');

        // Find available rooms
        $availableRooms = $roomTypeService->findAvailableTypes($arrival, $departure, $priceGroupId, $langId, $hotelId, $typeId, true);
        $availableRooms = $this->normalizeImagePath($availableRooms, 'cover');

        // Append beds
        foreach ($availableRooms as &$availableRoom) {
            $availableRoom['beds'] = $this->getModuleService('bedService')->fetchRelation($availableRoom['id'], $langId, true);
        }

        $types = $roomTypeService->fetchList($langId, $hotelId);

        return [
            'roomTypes' => $this->getModuleService('roomTypeService')->fetchList($langId, $hotelId),

            // Renders variables
            'typeId' => $typeId,
            'arrival' => $arrival,
            'departure' => $departure,
            'rooms' => $rooms,
            'adults' => $adults,
            'kids' => $kids,

            'regions' => $this->getModuleService('regionService')->fetchList($langId),
            'availableRooms' => $availableRooms,
            'types' => $types,
            'hotel' => $hotel,

            // Similar hotels
            'reviewTypes' => $this->getModuleService('reviewService')->findTypes($langId),
            'reviews' => $this->getModuleService('reviewService')->fetchAll($hotelId),

            'hotelId' => $hotelId,
            'regionId' => $hotel['region_id'],

            'facilities' => $this->getModuleService('facilitiyService')->getCollection($langId, true, $hotelId, true),
            'facilityMap' => $this->getModuleService('facilitiyService')->fetchSingleRelation($hotelId),

            // Hotel images
            'images' => [
                'large' => $this->normalizeImagePath($photoService->fetchAll($hotelId, PhotoService::PARAM_IMAGE_SIZE_LARGE), 'file'),
                'small' => $this->normalizeImagePath($photoService->fetchAll($hotelId, PhotoService::PARAM_IMAGE_SIZE_SMALL), 'file')
            ]
        ];
    }
}
