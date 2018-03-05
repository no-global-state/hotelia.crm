<?php

namespace Site\Storage\MySQL;

final class DictionaryTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_dictionary_translation');
    }
}
