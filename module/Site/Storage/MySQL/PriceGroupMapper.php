<?php

namespace Site\Storage\MySQL;

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
