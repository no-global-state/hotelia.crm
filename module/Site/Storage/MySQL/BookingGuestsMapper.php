<?php

namespace Site\Storage\MySQL;

final class BookingGuestsMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_guests');
    }

    /**
     * Find rows by attached booking ID
     * 
     * @param int $bookingId
     * @return array
     */
    public function findByBookingId(int $bookingId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('booking_id', $bookingId)
                        ->queryAll();
    }
}
