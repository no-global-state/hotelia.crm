<?php

namespace Site\Storage\MySQL;

final class RoomCategoryTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_categories_translations');
    }
}
