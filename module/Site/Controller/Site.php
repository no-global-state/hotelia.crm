<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Site\Gateway\GatewayService;
use Site\Collection\BookingStatusCollection;
use Krystal\Iso\ISO3166\Country;
use Krystal\Text\Math;

final class Site extends AbstractSiteController
{
    use MailerTrait;
    use HotelTrait;

    /**
     * Validates coupon code
     * 
     * @return string
     */
    public function couponAction()
    {
        $summary = $this->getModuleService('summaryService')->getSummary();

        if ($summary) {
            // Returns query data
            $data = $this->request->getQuery();
            $data['people_count'] = $summary['guests'];
            $data['price_group_id'] = $this->getPriceGroupId();

            $response = $this->getModuleService('couponService')->apply($data);

            return $this->json($response);
        }
    }

    /**
     * Changes currency
     * 
     * @param string $currency
     * @return void
     */
    public function currencyAction(string $currency)
    {
        $exchangeService = $this->getModuleService('exchangeService');

        if ($exchangeService->saveCurrency($currency)) {
            $this->response->redirectToPreviousPage();
        } else {
            // Invalid currency
        }
    }

    /**
     * Redirects to payment gateway
     * 
     * @param string $token
     * @return string|boolean
     */
    public function gatewayAction(string $token)
    {
        return $this->renderGateway($token, 'Site:Site@confirmPaymentAction');
    }

    /**
     * Renders invoice by its token
     * 
     * @param string $token
     * @return mixed
     */
    public function invoiceAction(string $token)
    {
        $bookingService = $this->getModuleService('bookingService');
        $booking = $bookingService->findByToken($token);

        // If found such token
        if ($booking) {
            $params = $this->createInvoice($booking);

            return $this->view->render('payment-confirm', $params);
        } else {
            // Trigger 404
            return false;
        }
    }

    /**
     * Renders feedback form
     * 
     * @param int $hotelId
     * @return string
     */
    public function reviewsAction(int $hotelId) : string
    {
        $reviewService = $this->getModuleService('reviewService');

        $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId());
        $rates = $reviewService->fetchAverages($hotelId, $this->getCurrentLangId());
        $reviews= $reviewService->fetchAll($hotelId);
        $items = $this->createMapper('\Site\Storage\MySQL\ReviewTypeMapper')->fetchAll($this->getCurrentLangId());

        return $this->view->render('feedback', [
            'reviewTypes' => $items,
            'rates' => $rates,
            'hotel' => $hotel,
            'reviews' => $reviews,
            'form' => false
        ]);
    }

    /**
     * Leaves a review by token
     * 
     * @param string $token
     * @return string
     */
    public function leaveReviewAction(string $token) : string
    {
        $bookingService = $this->getModuleService('bookingService');
        $booking = $bookingService->findByToken($token);

        if ($booking) {
            $hotelId = $booking['hotel_id'];
            $allowed = boolval($booking['can_leave_review']); // Whether posted review before?

            $reviewService = $this->getModuleService('reviewService');

            // Add 
            if ($this->request->isPost()) {
                $input = $this->request->getPost();

                // Append the review
                $reviewService->add($this->getCurrentLangId(), $hotelId, $input);

                // Update NULL with just added review ID
                $bookingService->updateReviewId($booking['id'], $reviewService->getLastId());

                // Notify about new feedback
                $email = $this->getModuleService('hotelService')->findEmailById($hotelId);
                $this->feedbackNewNotify($email);

                $this->flashBag->set('success', 'Your review has been added successfully');
                $this->response->refresh();
            }

            $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId());
            $rates = $reviewService->fetchAverages($hotelId, $this->getCurrentLangId());
            $reviews= $reviewService->fetchAll($hotelId);
            $items = $this->createMapper('\Site\Storage\MySQL\ReviewTypeMapper')->fetchAll($this->getCurrentLangId());

            return $this->view->render('feedback', [
                'reviewTypes' => $items,
                'rates' => $rates,
                'hotel' => $hotel,
                'reviews' => $reviews,
                'form' => true,
                'allowed' => $allowed
            ]);

        } else {
            // Invalid token provided
            return false;
        }
    }

    /**
     * Switches a language
     * 
     * @param string $code
     * @return void
     */
    public function languageAction(string $code)
    {
        $this->setLanguage($code);
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

        $summary = $this->getModuleService('summaryService');

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

        $output = $summary->getFormattedSummary($discount, $this->getModuleService('exchangeService'), $this->getPriceGroupId() == 1);

        return $this->json($output);
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
        $summary = $this->getModuleService('summaryService')->getData();

        return $this->getModuleService('roomTypeService')->createSummary($summary, $this->getPriceGroupId(), $hotelId, $this->getCurrentLangId());
    }

    /**
     * Confirms payment by its token when payment is done
     * 
     * @return string|boolean
     */
    public function confirmPaymentAction()
    {
        return $this->confirm('payment-confirm');
    }

    /**
     * Renders payment page
     * 
     * @return string
     */
    public function paymentAction()
    {
        // Validate request
        if ($this->request->hasQuery('type_id', 'hotel_id', 'arrival', 'departure') && !$this->request->isPost()) {

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
                'selectedSummary' => $this->getModuleService('summaryService')->getSummary(),
                'hotel' => $hotel,
                'summary' => ReservationService::calculateStayPrice($arrival, $departure, $room['price']),
                'qty' => $qty,
                'disablePriceGroups' => true
            ]);

        } elseif ($this->request->isPost()) {
            $summary = $this->getModuleService('summaryService')->getSummary();

            $hotelId = $this->request->getQuery('hotel_id');
            $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId(), $this->getPriceGroupId());

            // Request parameters
            $params = [
                'hotel_id' => $hotelId,
                'price_group_id' => $this->getPriceGroupId(),
                'lang_id' => $this->getCurrentLangId(),
                'arrival' => $this->request->getQuery('arrival'),
                'departure' => $this->request->getQuery('departure'),
                'phone' => $this->request->getPost('phone'),
                'email' => $this->request->getPost('email'),
                'comment' => $this->request->getPost('comment'),
                'near_preferred' => $this->request->getPost('near_preferred'),
                'amount' => $summary['price']
            ];

            // Append discount on demand
            $params['discount'] = (int) $this->getModuleService('couponService')->appliedCoupon();

            // If there's active applied discount, then save discount price
            if ($this->getModuleService('couponService')->appliedCoupon()) {
                // Apply discount
                $params['amount'] = Math::getDiscount($params['amount'], $hotel['discount']);
            }

            // Grab booking service and insert
            $bs = $this->getModuleService('bookingService');
            $booking = $bs->save($params, $this->request->getPost('guest'), $this->createSummary($hotelId));

            // Create payment URL for client
            $paymentUrl = $this->request->getBaseUrl() . $this->createUrl('Site:Site@gatewayAction', [$booking['token']]);

            return $this->view->render('thank-you', [
                'paymentUrl' => $paymentUrl
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
     * Search action
     * 
     * @return string
     */
    public function searchAction()
    {
        $hotels = $this->searchAll($this->getPriceGroupId(), $this->getCurrentLangId());

        return $this->view->render('search', $hotels);
    }

    /**
     * Renders hotel information
     * 
     * @return string
     */
    public function hotelAction()
    {
        $params = $this->findHotel($this->getPriceGroupId(), $this->getCurrentLangId());

        if ($params === false) {
            return false;
        } else {
            // Clear previous summary if any
            $summary = $this->getModuleService('summaryService');
            $summary->clear();

            // Grab similar hotels
            $similar = $this->getModuleService('hotelService')->findSimilar(
                $params['hotelId'], 
                $this->getCurrentLangId(),
                $this->getPriceGroupId(),
                $params['regionId']
            );

            // And append them to template
            $params['hotels'] = $similar;

            return $this->view->render('hotel', $params);
        }
    }

    /**
     * Renders cancel
     * 
     * @param string $token
     * @return string
     */
    public function cancelAction(string $token)
    {
        $booking = $this->getModuleService('bookingService')->findByToken($token);

        // In case invalid token provided
        if (!$booking) {
            return false;
        } else {
            $hotel = $this->getModuleService('hotelService')->fetchById($booking['hotel_id'], $this->getCurrentLangId(), $this->getPriceGroupId());

            return $this->view->render('booking-cancel-info', [
                'booking' => $booking,
                'token' => $token,
                'hotel' => $hotel
            ]);
        }
    }

    /**
     * Confirms canceling reservation
     * 
     * @param string $token
     * @return string
     */
    public function cancelConfirmAction(string $token) : string
    {
        $result = $this->getModuleService('bookingService')->cancel($token);

        // In case couldn't cancel
        return $this->view->render('booking-cancel-status', [
            'success' => $result
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
            'adults' => 2,
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
