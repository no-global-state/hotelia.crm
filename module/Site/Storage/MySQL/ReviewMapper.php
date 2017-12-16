<?php

namespace Site\Storage\MySQL;

final class ReviewMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews');
    }

    /**
     * Fetch all reviews
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
