<?php

namespace Site\Storage\MySQL;

final class BookingRoomMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_rooms');
    }
}
