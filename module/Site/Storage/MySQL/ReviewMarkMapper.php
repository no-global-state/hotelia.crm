<?php

namespace Site\Storage\MySQL;

final class ReviewMarkMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews_marks');
    }
}
