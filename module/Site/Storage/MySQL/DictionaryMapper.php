<?php

namespace Site\Storage\MySQL;

final class DictionaryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_dictionary');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return DictionaryTranslationMapper::getTableName();
    }
}
