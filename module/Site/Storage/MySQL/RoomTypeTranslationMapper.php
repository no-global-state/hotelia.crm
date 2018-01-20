<?php

namespace Site\Storage\MySQL;

final class RoomTypeTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_types_translations');
    }
}
