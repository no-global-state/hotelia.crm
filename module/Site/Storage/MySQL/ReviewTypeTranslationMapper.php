<?php

namespace Site\Storage\MySQL;

final class ReviewTypeTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews_types_translation');
    }
}
