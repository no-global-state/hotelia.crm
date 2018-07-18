<?php

namespace Site\Service;

use Krystal\Text\TextUtils;
use Site\Storage\MySQL\BookingMapper;
use Site\Storage\MySQL\BookingGuestsMapper;
use Site\Storage\MySQL\BookingRoomMapper;

final class BookingService
{
    /* Statuses */
    const STATUS_NEW = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_REJECTED = 2;

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
     * Count rows by status code
     * 
     * @param int $status Status code
     * @return int
     */
    public function countByStatus(int $status) : int
    {
        return $this->bookingMapper->countByStatus($status);
    }

    /**
     * Find rows by status
     * 
     * @param int $status Status code
     * @return array
     */
    public function findByStatus(int $status) : array
    {
        return $this->bookingMapper->findByStatus($status);
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
     * Saves a booking
     * 
     * @param array $params Booking parameters
     * @param array $guests Guests 
     * @param array $rooms Rooms
     * @return boolean
     */
    public function save(array $params, array $guests, array $rooms) : bool
    {
        // Append a token
        $params['token'] = TextUtils::uniqueString();

        // Append current date and time
        $params['datetime'] = date('Y-m-d H:i:s');

        // Insert new booking
        $this->bookingMapper->persist($params);

        // Get last booking ID
        $bookingId = $this->bookingMapper->getMaxId();

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

        return true;
    }
}
