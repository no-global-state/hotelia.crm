<?php

namespace Site\Storage\MySQL;

final class RoomTypeBedRelationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_type_beds_relation');
    }
}
