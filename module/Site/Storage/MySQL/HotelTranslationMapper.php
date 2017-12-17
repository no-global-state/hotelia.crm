<?php

namespace Site\Storage\MySQL;

final class HotelTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_translation');
    }
}
