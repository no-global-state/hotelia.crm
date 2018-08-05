<?php

namespace Site\Controller;

use Site\Service\ReservationService;
use Site\Service\PhotoService;

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

        $types = $roomTypeService->fetchList($langId, $hotelId);

        // Grab similar hotels
        $similar = $this->getModuleService('hotelService')->findAll($langId, $priceGroupId, ['region_id' => $hotel['region_id']], 5);
        $similar = $this->normalizeImagePath($similar, 'cover');

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
            'hotels' => $similar,
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
