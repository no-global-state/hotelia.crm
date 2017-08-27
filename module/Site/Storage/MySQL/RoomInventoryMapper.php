<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class RoomInventoryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('hotelia_room_inventory');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch inventories by room
     * 
     * @param int $roomId
     * @return array
     */
    public function fetchAll($roomId)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('room_id'),
            self::getFullColumnName('inventory_id'),
            self::getFullColumnName('code'),
            self::getFullColumnName('qty'),
            self::getFullColumnName('comment'),
            InventoryMapper::getFullColumnName('name') => 'inventory',
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Inventory relation
                        ->innerJoin(InventoryMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('inventory_id'),
                            InventoryMapper::getRawColumn('id')
                        )
                        ->whereEquals('room_id', $roomId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}