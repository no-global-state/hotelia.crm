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
}
