<?php

namespace Site\Storage\MySQL;

final class HotelUserRelationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_users');
    }
}
