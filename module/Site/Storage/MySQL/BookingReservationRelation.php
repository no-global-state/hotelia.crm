<?php

namespace Site\Storage\MySQL;

final class BookingReservationRelation extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_reservations');
    }
}
