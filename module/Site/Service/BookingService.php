<?php

namespace Site\Service;

use Krystal\Date\TimeHelper;
use Krystal\Text\TextUtils;
use Site\Storage\MySQL\BookingMapper;
use Site\Storage\MySQL\BookingGuestsMapper;
use Site\Storage\MySQL\BookingRoomMapper;
use Site\Collection\BookingStatusCollection;

final class BookingService
{
    /**
     * Any compliant booking mapper
     * 
     * @var \Site\Storage\MySQL\BookingMapper
     */
    private $bookingMapper;

    /**
     * Booking guests mapper
     * 
     * @var \Site\Storage\MySQL\BookingGuestsMapper
     */
    private $bookingGuestsMapper;

    /**
     * Booking room mapper
     * 
     * @var \Site\Storage\MySQL\BookingRoomMapper
     */
    private $bookingRoomMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\BookingMapper $bookingMapper
     * @param \Site\Storage\MySQL\BookingGuestsMapper $bookingGuestsMapper
     * @param \Site\Storage\MySQL\BookingRoomMapper $bookingRoomMapper
     * @return void
     */
    public function __construct(BookingMapper $bookingMapper, BookingGuestsMapper $bookingGuestsMapper, BookingRoomMapper $bookingRoomMapper)
    {
        $this->bookingMapper = $bookingMapper;
        $this->bookingGuestsMapper = $bookingGuestsMapper;
        $this->bookingRoomMapper = $bookingRoomMapper;
    }

    /**
     * Cancels booking by its token
     * 
     * @param string $token
     * @return boolean
     */
    public function cancel(string $token) : bool
    {
        $data = $this->bookingMapper->findCancelationDataByToken($token);

        // Invalid token checking
        if (!$data) {
            return false;
        }

        // Don't let to cancel twice, if already canceled
        if ($data['status'] == BookingStatusCollection::STATUS_REFUND_IN_PROGRESS || $data['status'] == BookingStatusCollection::STATUS_REFUNED) {
            return false;
        }

        // Finally process
        $service = new CancelationService($data['datetime'], $data['arrival']);

        $canCancel = $service->canCancel($data['penality_not_taken_after'], $data['penality_not_later_arrival']);

        if ($canCancel) {
            // Update status to in progress
            $this->bookingMapper->updateStatusByToken($token, BookingStatusCollection::STATUS_REFUND_IN_PROGRESS, TimeHelper::getNow());
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns last booking ID
     * 
     * @return int
     */
    public function getLastId()
    {
        return $this->bookingMapper->getMaxId();
    }

    /**
     * Inserts relation
     * 
     * @param int $bookingId
     * @param array $ids Reservation IDs
     * @return boolean
     */
    public function insertRelation(int $bookingId, array $ids) : bool
    {
        return $this->bookingMapper->insertRelation($bookingId, $ids);
    }

    /**
     * Create reservation details
     * 
     * @param int $id Booking ID
     * @param array $guests
     * @return array|boolean
     */
    public function createReservationDetails(int $id, array $guests)
    {
        $booking = $this->findById($id);

        // If booking ID is valid 
        if ($booking) {
            $reservations = [];

            foreach ($guests as $guest) {
                $reservations[] = [
                    'hotel_id' => $booking['hotel_id'],
                    'room_id' => $guest['room_id'],
                    'price_group_id' => $booking['price_group_id'],
                    'payment_system_id' => 1,
                    'full_name' => $guest['full_name'],
                    'gender' => $guest['gender'],
                    'country' => $guest['country'],
                    'status' => 2,
                    'purpose' => 1,
                    'source' => 1,
                    'legal_status' => 1,
                    'phone' => $booking['phone'],
                    'email' => $booking['email'],
                    'passport' => '',
                    'comment' => $booking['comment'],
                    'tax' => 0,
                    'price' => $booking['amount'],
                    'arrival' => $booking['arrival'],
                    'departure' => $booking['departure']
                ];
            }

            return [
                'reservations' => $reservations,
                'booking' => $booking
            ];

        } else {
            return false;
        }
    }

    /**
     * Deletes booking info by its ID
     * 
     * @param int $id Booking ID
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->bookingMapper->deleteByPk($id);
    }

    /**
     * Find details by booking ID
     * 
     * @param int $id Booking ID
     * @param int $langId
     * @return array|boolean
     */
    public function findDetails(int $id, int $langId)
    {
        $booking = $this->bookingMapper->findById($id);

        if ($booking) {
            // The rest
            $guests = $this->bookingGuestsMapper->findByBookingId($id);
            $rooms = $this->bookingRoomMapper->findDetailsByBookingId($id, $langId);

            // Append nights count
            $booking['nights'] = ReservationService::getDaysDiff($booking['arrival'], $booking['departure']);

            return [
                'booking' => $booking,
                'guests' => $guests,
                'rooms' => $rooms
            ];

        } else {
            return false;
        }
    }

    /**
     * Count rows by status code
     * 
     * @param int $hotelId Attached hotel ID
     * @param int $status Status code
     * @return int
     */
    public function countByStatus(int $hotelId, int $status) : int
    {
        return $this->bookingMapper->countByStatus($hotelId, $status);
    }

    /**
     * Find booking row by its associated ID
     * 
     * @param int $id Booking ID
     * @return array
     */
    public function findById(int $id)
    {
        return $this->bookingMapper->findById($id);
    }

    /**
     * Find booking row by its associated token
     * 
     * @param string $token
     * @return array
     */
    public function findByToken(string $token)
    {
        return $this->bookingMapper->findByToken($token);
    }

    /**
     * Find all booking rows
     * 
     * @param int $hotelId Attached hotel ID
     * @return array
     */
    public function findAll(int $hotelId) : array
    {
        return $this->bookingMapper->findAll($hotelId);
    }

    /**
     * Find rows by status
     * 
     * @param int $hotelId Attached hotel ID
     * @param int $status Status code
     * @return array
     */
    public function findByStatus(int $hotelId, int $status) : array
    {
        return $this->bookingMapper->findByStatus($hotelId, $status);
    }

    /**
     * Updates status by booking ID
     * 
     * @param int $id
     * @param int $status
     * @return boolean Depending on success
     */
    public function updateStatusById(int $id, int $status) : bool
    {
        return $this->bookingMapper->updateStatusById($id, $status);
    }

    /**
     * Updates status by token
     * 
     * @param string $token
     * @param int $status
     * @return boolean Depending on success
     */
    public function updateStatusByToken(string $token, int $status) : bool
    {
        return $this->bookingMapper->updateStatusByToken($token, $status);
    }

    /**
     * Saves a booking and returns its last token
     * 
     * @param array $params Booking parameters
     * @param array $guests Guests 
     * @param array $rooms Rooms
     * @return array
     */
    public function save(array $params, array $guests, array $rooms) : array
    {
        // Append a token
        $params['token'] = TextUtils::uniqueString();

        // Append current date and time
        $params['datetime'] = date('Y-m-d H:i:s');

        // Insert new booking
        $row = $this->bookingMapper->persistRow($params);

        // Get last booking ID
        $bookingId = $this->getLastId();

        // Append booking ID to guests and insert them
        foreach ($guests as $guest) {
            $guest['booking_id'] = $bookingId;
            $this->bookingGuestsMapper->persist($guest);
        }

        // Append rooms
        foreach ($rooms as $roomTypeId => $room) {
            $this->bookingRoomMapper->persist([
                'booking_id' => $bookingId,
                'room_type_id' => $roomTypeId,
                'guests' => $room['guests'],
                'qty' => $room['qty']
            ]);
        }

        return [
            'id' => $row['id'],
            'token' => $row['token']
        ];
    }
}
