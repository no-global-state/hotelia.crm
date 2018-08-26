<?php

namespace Site\Controller;

use Site\Service\PhotoService;
use Site\Service\ReservationService;
use Site\Gateway\GatewayService;
use Site\Collection\BookingStatusCollection;
use Krystal\Iso\ISO3166\Country;

final class Site extends AbstractSiteController
{
    use MailerTrait;
    use HotelTrait;

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
                $this->request->getBaseUrl() . $this->createUrl('Site:Site@confirmPaymentAction', ['token' => $token])
            );

            return $this->view->disableLayout()->render('gateway', [
                'gateway' => $gateway
            ]);

        } else {
            return false;
        }
    }
    
    /**
     * Create invoice data from booking
     * 
     * @param array $booking
     * @return array
     */
    private function createInvoice(array $booking) : array
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
            'guests' => $details['guests']
        ];
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
     * Confirms payment by its token when payment is done
     * 
     * @return string|boolean
     */
    public function confirmPaymentAction()
    {
        if (!GatewayService::transactionFailed()) {
            $token = $this->request->getQuery('token');

            $bookingService = $this->getModuleService('bookingService');
            $booking = $bookingService->findByToken($token);

            // If found such token
            if ($booking) {
                // Update status as well
                $bookingService->updateStatusById($booking['id'], BookingStatusCollection::STATUS_CONFIRMED);

                $params = $this->createInvoice($booking);

                // Email notifications
                $this->voucherNotify($booking['email'], $params);
                $this->transactionAdminNotify($this->getModuleService('hotelService')->findNameById($booking['hotel_id'], 1));

                // Save successful transaction
                $this->getModuleService('transactionService')->save($booking['hotel_id'], $booking['price_group_id'], $booking['amount']);

                // For voucher
                return $this->view->render('payment-confirm', $params);

            } else {
                // Trigger 404
                return false;
            }
        } else {
            
            return 'Transaction canceled';
        }
    }

    /**
     * Renders feedback form
     * 
     * @return string
     */
    public function feedbackAction() : string
    {
        // Request variable
        $hotelId = $this->request->getQuery('hotel_id');
        $reviewService = $this->getModuleService('reviewService');

        if ($this->request->isPost()) {
            $input = $this->request->getPost();

            // Append the review
            $reviewService->add($this->getCurrentLangId(), $hotelId, $input);

            // Notify about new feedback
            $email = $this->getModuleService('hotelService')->findEmailById($hotelId);
            $this->feedbackNewNotify($email);

            $this->flashBag->set('success', 'Your review has been added successfully');
            $this->response->refresh();
        }

        $hotel = $this->getModuleService('hotelService')->fetchById($hotelId, $this->getCurrentLangId());
        $rates = $reviewService->fetchAverages($hotelId, $this->getCurrentLangId());
        $reviews= $reviewService->fetchAll($hotelId);
        $mapper = $this->createMapper('\Site\Storage\MySQL\ReviewTypeMapper');
        $items = $mapper->fetchAll($this->getCurrentLangId());

        return $this->view->render('feedback', [
            'reviewTypes' => $items,
            'rates' => $rates,
            'hotel' => $hotel,
            'reviews' => $reviews
        ]);
    }

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
                'qty' => $qty
            ]);

        } elseif ($this->request->isPost()) {
            $summary = $this->getModuleService('summaryService')->getSummary();

            $hotelId = $this->request->getQuery('hotel_id');

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
                'amount' => $summary['price']
            ];

            // Grab booking service and insert
            $bs = $this->getModuleService('bookingService');
            $booking = $bs->save($params, $this->request->getPost('guest'), $this->createSummary($hotelId));

            // Save external relation if possible
            $this->getModuleService('externalService')->saveIfPossible($booking['id']);
            
            // Create payment URL for client
            $paymentUrl = $this->request->getBaseUrl() . $this->createUrl('Site:Site@gatewayAction', [$booking['token']]);

            // Send user notification
            $this->paymentConfirmNotify($this->request->getPost('email'), $paymentUrl);

            // Notify owner
            $this->bookingOwnerNotify($this->getModuleService('hotelService')->findEmailById($hotelId));

            // Notify admin
            $this->bookingAdminNotify($this->getModuleService('hotelService')->findNameById($hotelId, 1));

            return $this->view->render('thank-you');

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
