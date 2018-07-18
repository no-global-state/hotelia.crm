<?php

namespace Site\Storage\MySQL;

final class BookingMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings');
    }
}
