<?php

namespace Site\Storage\MySQL;

final class HotelTypeTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotel_types_translation');
    }
}
