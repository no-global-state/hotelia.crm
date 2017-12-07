<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class RegionMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_regions');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all regions
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
