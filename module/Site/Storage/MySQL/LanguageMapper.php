<?php

namespace Site\Storage\MySQL;

final class LanguageMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_languages');
    }

    /**
     * Checks whether language code is valid
     * 
     * @param string $code
     * @return boolean
     */
    public function exists(string $code) : bool
    {
        $row = $this->db->select()
                        ->count($this->getPk())
                        ->from(self::getTableName())
                        ->whereEquals('code', $code)
                        ->queryScalar();

        return $row > 0;
    }

    /**
     * Fetch all languages
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->queryAll();
    }
}
