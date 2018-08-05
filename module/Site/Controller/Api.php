<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Krystal\Text\Math;
use Site\Helpers\ApiHelper;

final class Api extends AbstractCrmController
{
    use HotelTrait;

    /**
     * {@inheritDoc}
     */
    protected $authActive = false;

    /**
     * Returns language parameter from query string
     * 
     * @return int
     */
    private function getLang() : int
    {
        return $this->request->getQuery('lang', 1);
    }

    /**
     * Dummy payment action
     * 
     * @return string
     */
    public function payment()
    {
        return $this->json([
            'url' => '#'
        ]);
    }
    
    /**
     * Renders hotel page
     * 
     * @return string
     */
    public function hotel()
    {
        $priceGroupId = $this->request->getQuery('price_group_id', 1);
        $lang = $this->request->getQuery('lang', 1);

        $data = $this->findHotel($priceGroupId, $lang);

        return $this->json($data);
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
        $typeIds = $request['type_id'] ?? [];

        // Main
        $languageId = $request['lang'] ?? 1;
        $priceGroupId = $request['price_group_id'] ?? 1;

        // Append one more key
        $request['facility'] = array_merge($request['facility_id'] ?? [], $request['meals_id'] ?? []);

        // Dates
        $arrival = $request['arrival'];
        $departure = $request['departure'];

        // Collection of rates
        $rates = $request['stars_id'] ?? [];

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
     * Performs a search
     * 
     * @return string
     */
    public function search()
    {
        $priceGroupId = $this->request->getQuery('price_group_id', 1);
        $hotels = $this->searchAll($priceGroupId, $this->getLang());

        return $this->json($hotels);
    }

    /**
     * Returns initial data
     * 
     * @return array
     */
    public function getInitial()
    {
        $data = $this->getModuleService('regionService')->findHotels($this->getLang());

        foreach ($data as &$item) {
            $item['image'] = $this->appendBaseUrl($item['image']);
        }

        return $this->json($data);
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
}
