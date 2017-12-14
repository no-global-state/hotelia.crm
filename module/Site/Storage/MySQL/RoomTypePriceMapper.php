<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class RoomTypePriceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_type_prices');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }
}
