<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;
use Krystal\Db\Sql\RawSqlFragment;

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
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
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
