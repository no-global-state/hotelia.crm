<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Site\Service\SummaryService;
use Site\Service\Dictionary;
use Site\Helpers\ApiHelper;
use Site\Service\ExternalService;
use Krystal\Text\Math;
use Krystal\Stdlib\ArrayUtils;

final class Api extends AbstractCrmController
{
    use HotelTrait;
    use MailerTrait;

    /**
     * {@inheritDoc}
     */
    protected $authActive = false;

    /**
     * {@inheritDoc}
     */
    protected function bootstrap($action)
    {
        $this->view->addVariables([
            'dictionary' => $this->createDictionary()
        ]);
    }

    /**
     * Creates dictionary instance
     * 
     * @return \Site\Service\Dictionary
     */
    private function createDictionary()
    {
        static $dictionary;

        if (is_null($dictionary)) {
            $dictionary = new Dictionary($this->getModuleService('dictionaryService'), $this->getLang());
        }

        return $dictionary;
    }

    /**
     * Returns language parameter from query string
     * 
     * @return int
     */
    private function getLang() : int
    {
        $value = $this->request->getQuery('lang', 1);

        return ExternalService::externalLangId($value);
    }

    /**
     * Returns price group ID from query
     * 
     * @return int
     */
    private function getPriceGroup() : int
    {
        return (int) $this->request->getQuery('price_group_id', 1);
    }

    /**
     * Save external user ID
     * 
     * @param int $userId External user ID
     * @return void
     */
    public function saveExternal(int $userId)
    {
        $bookingId = $this->getModuleService('bookingService')->getLastId();

        return $this->json([
            'success' => $this->getModuleService('externalService')->saveIfPossible($bookingId, $userId)
        ]);
    }

    /**
     * Render all bookings (without details)
     * 
     * @return array
     */
    public function bookings() : string
    {
        // Request variables
        $id = $this->request->getQuery('user_id'); // External user ID

        $bookings = $this->getModuleService('externalService')->findTotalByExternalId($id, $this->getLang());

        return $this->json($bookings);
    }

    /**
     * Run batch mailer
     * 
     * @return string
     */
    public function receivers()
    {
        $rows = $this->getModuleService('bookingService')->fetchTodayReceivers();

        $this->notifyReceivers($rows);

        return 'DONE';
    }

    /**
     * Returns statistic by user ID
     * 
     * @return string
     */
    public function statistic() : string
    {
        // Request variables
        $id = $this->request->getQuery('user_id'); // External user ID

        // Find ever reserved hotels by external user ID
        $bookings = $this->getModuleService('externalService')->findHotelsByExternalId($id, $this->getLang(), $this->createDictionary());

        return $this->json($bookings);
    }

    /**
     * Saves booking inquiry and returns payment link
     * 
     * @return string
     */
    public function payment() : string
    {
        // Get request variables from POST JSON request
        $request = $this->request->getJsonBody();

        // Create request vars
        $rooms = $request['rooms']; // Raw
        $rooms = ArrayUtils::arrayPartition($rooms, 'room_type_id', false); // Parsed

        $priceGroupId = $request['price_group_id'];
        $langId = ExternalService::internalLangId($request['lang']);
        $hotelId = $request['hotel_id'];
        $arrival = $request['arrival'];
        $departure = $request['departure'];

        $client = $request['client'];

        // Booking parameters
        $params = [
            'hotel_id' => $hotelId,
            'price_group_id' => $priceGroupId,
            'lang_id' => $langId,
            'arrival' => $arrival,
            'departure' => $departure,
            'phone' => $client['phone'],
            'email' => $client['email'],
            'comment' => '',
            'near_preferred' => 1,
            'amount' => SummaryService::parseRawData($rooms)['price']
        ];

        // We don't save email and phone for guests, but for booking only
        unset($client['email'], $client['phone']);

        // Room data to be save
        $data = $this->getModuleService('roomTypeService')->createSummary($rooms, $priceGroupId, $hotelId, $langId);

        // Save booking
        $booking = $this->getModuleService('bookingService')->save($params, [$client], $data);

        if (isset($request['user_id'])) {
            // Save external relation if possible
            $this->getModuleService('externalService')->saveIfPossible($booking['id'], $request['user_id']);
        }

        // Create payment URL for client
        $paymentUrl = $this->request->getBaseUrl() . $this->createUrl('Site:Site@gatewayAction', [$booking['token']]);

        return $this->json([
            'url' => $paymentUrl
        ]);
    }

    /**
     * Renders hotel page
     * 
     * @return string
     */
    public function hotel()
    {
        $data = $this->findHotel($this->getPriceGroup(), $this->getLang());
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
     * Get bookings by external user ID
     * 
     * @return string
     */
    public function getBookings() : string
    {
        // External user ID
        $id = $this->request->getQuery('id');
        $langId = $this->getLang();

        if ($id && $langId) {
            $bookings = $this->getModuleService('externalService')->findAllByExternalId($id, $langId);

            // Append invoice URL
            foreach ($bookings as &$booking) {
                $booking['invoice'] = $this->request->getBaseUrl() . $this->createUrl('Site:Site@invoiceAction', [$booking['token']]);
            }

            return $this->json($bookings);
        } else {
            return $this->json([
                'error' => 'Not enough request parameters'
            ]);
        }
    }

    /**
     * Returns filter parameters
     * 
     * @return array
     */
    public function getFilter()
    {
        $data = $this->getSharedFilter($this->getLang(), $this->getPriceGroup());

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
        $languageId = isset($request['lang']) ? ExternalService::externalLangId($request['lang']) : 1;
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
        $hotels = $this->searchAll($this->getPriceGroup(), $this->getLang());
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
