<?php

namespace Site\Service;

use Site\Storage\MySQL\BookingMapper;
use Site\Storage\MySQL\BookingGuestsMapper;
use Site\Storage\MySQL\BookingRoomMapper;

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
     * Saves a booking
     * 
     * @param array $params Booking parameters
     * @param array $guests Guests 
     * @param array $rooms Rooms
     * @return boolean
     */
    public function save(array $params, array $guests, array $rooms) : bool
    {
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
