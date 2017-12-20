<?php

namespace Site\Storage\MySQL;

final class RegionTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_regions_translation');
    }
}
