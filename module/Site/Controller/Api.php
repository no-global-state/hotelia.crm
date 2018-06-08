<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Krystal\Text\Math;
use Site\Helpers\ApiHelper;

final class Api extends AbstractCrmController
{
    /**
     * {@inheritDoc}
     */
    protected $authActive = false;

    /**
     * Appends base URL
     * 
     * @param string $target
     * @return mixed
     */
    private function appendBaseUrl($target)
    {
        if (!empty($target)) {
            return sprintf('%s/%s/%s', $this->request->getBaseUrl(), '/module/Site/View/Template/site/', $target);
        }
    }

    /**
     * Appends base URL
     * 
     * @param string $target
     * @return mixed
     */
    private function appendUploadUrl($target)
    {
        if (!empty($target)) {
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
    private function normalizeImagePath(array $collection, string $key) : array
    {
        foreach ($collection as &$item) {
            if (isset($item[$key])) {
                $item[$key] = $this->appendUploadUrl($item[$key]);
            }
        }

        return $collection;
    }

    /**
     * Returns shared filter data
     * 
     * @param int $lang
     * @param int $priceGroupId
     * @return array
     */
    private function getSharedFilter($lang, $priceGroupId) : array
    {
        // Services
        $facilitiyService = $this->getModuleService('facilitiyService');
        $dictionaryService = $this->getModuleService('dictionaryService');

        return [
            'prices' => ApiHelper::getPriceRanges($_ENV['prices'], $priceGroupId),
            'sorting' => ApiHelper::getSortingOptions($dictionaryService, $lang),
            'meals' => $facilitiyService->getItems($lang, 15),
            'rates' => ApiHelper::createStarRates($dictionaryService, $lang),
            'hotelTypes' => $this->getModuleService('hotelTypeService')->fetchAll($lang),
            'facilities' => $facilitiyService->getItemList(null, $lang, true, false, false)
        ];
    }
    
    /**
     * Returns filter parameters
     * 
     * @return array
     */
    public function getFilter()
    {
        // Request vars
        $lang = $this->request->getQuery('lang', 1);
        $priceGroupId = $this->request->getQuery('price_group_id', 1);

        $data = $this->getSharedFilter($lang, $priceGroupId);

        return $this->json($data);
    }

    /**
     * Performs a filter
     * 
     * @return array
     */
    public function filter()
    {
        $request = $this->request->getJsonBody();

        // Request variables
        $regionId = $request['region_id'] ?? null;
        $typeIds = $request['type'] ?? [];

        // Main
        $languageId = $request['lang'] ?? 1;
        $priceGroupId = $request['price_group_id'] ?? 1;

        // Append one more key
        $request['facility'] = array_merge($request['facilities'] ?? [], $request['meals'] ?? []);

        // Dates
        $arrival = $request['arrival'];
        $departure = $request['departure'];

        // Collection of rates
        $rates = $request['stars'] ?? [];

        // Counter data
        $rooms = $request['rooms'] ?? 1;
        $adults = $request['adults'] ?? 1;
        $kids = $request['kids'] ?? 0;

        // Sorting param
        $sort = $request['sort'] ?? 'discount';

        // Create region data based on its ID
        if ($regionId) {
            $region = $this->getModuleService('regionService')->fetchById($regionId, $languageId);
            $region['image'] = $this->appendBaseUrl($region['image']);
        } else {
            $region = null;
        }

        $hotels = $this->getModuleService('hotelService')->findAll($languageId, $priceGroupId, $request, $sort);

        foreach ($hotels as &$hotel) {
            $hotel['cover'] = $this->appendUploadUrl($hotel['cover']);

            // Dummy
            $hotel['has_free_rooms'] = true;
            $hotel['card_required'] = false;
        }

        $regions = $this->getModuleService('regionService')->fetchAll($languageId);

        return $this->json([
            'filter' => $this->getSharedFilter($languageId, $priceGroupId),
            'sort' => $sort,
            'region' => $region,
            'hotels' => $hotels,
            'regions' => $regions,
        ]);
    }

    /**
     * Checks whether login is already registered
     * 
     * @param string $login
     * @return int
     */
    public function available(string $login) : int
    {
        return !$this->getModuleService('userService')->loginExists($login) ? 1 : 0;
    }

    /**
     * Registers a new user
     * 
     * @return string
     */
    public function register() : string
    {
        if ($this->request->hasPost('email', 'phone', 'name', 'login', 'password')) {
            $hotelService = $this->getModuleService('hotelService');

            return json_encode([
                'success' => $hotelService->register($this->request->getPost())
            ]);

        } else {
            return 0;
        }
    }

    /**
     * View hotel info by its ID
     * 
     * @return string
     */
    public function details()
    {
        if ($this->request->hasQuery('lang_id', 'hotel_id')) {
            // Get request parameters
            $langId = $this->request->getQuery('lang_id');
            $hotelId = $this->request->getQuery('hotel_id');

            // Services
            $hotelService = $this->getModuleService('hotelService');
            $photoService = $this->getModuleService('photoService');
            $facilitiyService = $this->getModuleService('facilitiyService');
            $roomService = $this->getModuleService('roomService');

            // Data
            $hotel = $hotelService->fetchById($hotelId, $langId);
            $gallery = $photoService->fetchList($hotelId);
            $facilities = $facilitiyService->getItemList($hotelId, $langId);
            $rooms = $roomService->findAvailableRooms($hotel['id']);

            foreach ($gallery as &$image) {
                $image = $this->request->getBaseUrl() . $image;
            }

            $hotel['start_price_with_discount'] = Math::getDiscount($hotel['start_price'], $hotel['discount']);

            $data = [
                'hotel' => $hotel,
                'facilities' => $facilities,
                'gallery' => $gallery,
                'rooms' => $rooms
            ];

            return json_encode($data);
        }
    }

    /**
     * Renders all hotels
     * 
     * @return array
     */
    public function all()
    {
        if ($this->request->hasQuery('lang_id')) {
            // Get request parameters
            $langId = $this->request->getQuery('lang_id');

            $hotelService = $this->getModuleService('hotelService');
            $facilitiyService = $this->getModuleService('facilitiyService');
            $photoService = $this->getModuleService('photoService');

            $hotels = $hotelService->fetchAll($langId);

            foreach ($hotels as &$hotel) {
                $hotel['start_price_with_discount'] = Math::getDiscount($hotel['start_price'], $hotel['discount']);

                // Append facilities
                $hotel['facilities'] = $facilitiyService->getItemList($hotel['id'], $langId);
                $hotel['cover'] = $this->request->getBaseUrl() . $photoService->createImagePath($hotel['cover_id'], $hotel['cover'], PhotoService::PARAM_IMAGE_SIZE_LARGE);
            }

            $data = [
                'hotels' => $hotels,
                'regions' => $this->getModuleService('regionService')->fetchList($langId),
                'facilities' => $facilitiyService->getCollection($langId, false)
            ];

            return json_encode($data);
        }
    }
}
