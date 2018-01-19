<?php

namespace Site\Storage\MySQL;

final class DistrictTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_regions_districts_translation');
    }
}
