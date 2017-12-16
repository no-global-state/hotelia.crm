<?php

namespace Site\Storage\MySQL;

final class FacilitiyCategoryTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_facilitiy_categories_translation');
    }
}
