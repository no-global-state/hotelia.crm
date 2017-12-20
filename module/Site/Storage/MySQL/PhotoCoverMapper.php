<?php

namespace Site\Storage\MySQL;

final class PhotoCoverMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_photos_covers');
    }
}
