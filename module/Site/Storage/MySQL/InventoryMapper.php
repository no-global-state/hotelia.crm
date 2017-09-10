<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

class InventoryMapper extends AbstractMapper
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
    public function fetchAll($hotelId)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->queryAll();
    }
}
