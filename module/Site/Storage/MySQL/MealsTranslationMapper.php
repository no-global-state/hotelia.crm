<?php

namespace Site\Storage\MySQL;

final class MealsTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_meals_translations');
    }
}
