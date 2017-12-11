<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class PriceGroupMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_price_groups');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all price groups
     * 
     * @param boolean $sort Whether to sort
     * @return array
     */
    public function fetchAll($sort = false) : array
    {
        $query = $this->db->select('*')
                          ->from(self::getTableName());

        if ($sort == true) {
            $query->orderBy('order');
        } else {
            $query->orderBy($this->getPk())
                  ->desc();
        }

        return $query->queryAll();
    }
}
