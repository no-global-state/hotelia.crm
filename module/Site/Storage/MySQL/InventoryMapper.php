<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class InventoryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_inventory');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all inventory
     * 
     * @param integer $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
