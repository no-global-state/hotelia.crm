<?php

namespace Site\Storage\MySQL;

final class FacilitiyItemTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_facilitiy_items_translation');
    }
}
