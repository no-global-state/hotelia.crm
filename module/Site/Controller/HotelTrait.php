<?php

namespace Site\Controller;

use Site\Service\ReservationService;
use Site\Service\PhotoService;
use Site\Service\BedService;
use Site\Gateway\GatewayService;
use Site\Collection\BookingStatusCollection;
use Closure;

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
     * Create invoice data from booking
     * 
     * @param array $booking
     * @return array
     */
    final protected function createInvoice(array $booking) : array
    {
        // Grab hotel information
        $hotel = $this->getModuleService('hotelService')->fetchById($booking['hotel_id'], $booking['lang_id'], $booking['price_group_id']);

        // Normalize full cover path
        $hotel['cover'] = $this->appendUploadUrl($hotel['cover']);

        $details = $this->getModuleService('bookingService')->findDetails($booking['id'], $booking['lang_id']);

        // Shared params for email and view
        return [
            'hotel' => $hotel,
            'booking' => $details['booking'],
            'rooms' => $details['rooms'],
            'guests' => $details['guests'],
            'cancelUrl' => $this->request->getBaseUrl() . $this->createUrl('Site:Site@cancelAction', [$booking['token']])
        ];
    }

    /**
     * Confirms a payment
     * 
     * @param string $template Template name to be rendered on success
     * @return mixed
     */
    final protected function confirm(string $template)
    {
        if (!GatewayService::transactionFailed()) {
            $token = $this->request->getQuery('token');

            $bookingService = $this->getModuleService('bookingService');
            $booking = $bookingService->findByToken($token);

            // If found such token
            if ($booking) {
                // Update status as well
                $bookingService->updateStatusById($booking['id'], BookingStatusCollection::STATUS_NEW);

                $params = $this->createInvoice($booking);

                // Email notifications
                $this->voucherNotify($booking['email'], $params);

                // Do send in default language
                $this->inDefaultLanguage(function() use ($booking){
                    $hotelService = $this->getModuleService('hotelService');
                    $name = $hotelService->findNameById($booking['hotel_id'], 1); // Hotel name

                    // Notify administration
                    $this->bookingAdminNotify($name);
                    $this->transactionAdminNotify($name);

                    // Notify owner
                    $this->bookingOwnerNotify($hotelService->findEmailById($booking['hotel_id']));
                });

                // Save successful transaction
                $this->getModuleService('transactionService')->save($booking['hotel_id'], $booking['price_group_id'], $booking['amount']);

                // Handle coupon on demand
                $coupon = $this->getModuleService('couponService');

                if ($coupon->appliedCoupon()) {
                    $coupon->afterOrder();
                }

                // For voucher
                return $this->view->render($template, $params);

            } else {
                // Trigger 404
                return false;
            }
        } else {
            return $this->view->render('payment-canceled');
        }
    }

    /**
     * Renders gateway by its token
     * 
     * @param string $token
     * @param string $callback Back controller action
     * @return string|boolean
     */
    final protected function renderGateway(string $token, string $callback)
    {
        // Grab booking service
        $bookingService = $this->getModuleService('bookingService');
        $booking = $bookingService->findByToken($token);

        if ($booking) {
            // Create payment gateway
            $gateway = GatewayService::factory(
                $booking['id'],
                $booking['price_group_id'],
                $booking['amount'],
                // Payment fields of target hotel
                $this->getModuleService('paymentFieldService')->findAllByHotelId($booking['hotel_id']),
                // URL on successful payment
                $this->request->getBaseUrl() . $this->createUrl($callback, ['token' => $token])
            );

            return $this->view->disableLayout()->render('gateway', [
                'gateway' => $gateway
            ]);

        } else {
            return false;
        }
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
        $arrival = $this->request->getQuery('arrival');
        $departure = $this->request->getQuery('departure');
        $rate = $this->request->getQuery('rate', 0);
        $priceStart = $this->request->getQuery('price-start', 10);
        $priceStop = $this->request->getQuery('price-stop', 100);
        $rooms = $this->request->getQuery('rooms', 1);
        $adults = $this->request->getQuery('adults', 1);
        $kids = $this->request->getQuery('kids', 0);
        $stars = $this->request->getQuery('stars', []);

        if (!$arrival) {
            $arrival = ReservationService::getToday();
        }

        if (!$departure) {
            $departure = ReservationService::addOneDay(ReservationService::getToday());
        }

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

        // Append room types
        $hotels = $this->getModuleService('roomService')->appendFreeRoomTypes($hotels, $this->getCurrentLangId(), $priceGroupId, $arrival, $departure, $adults, $kids);

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
