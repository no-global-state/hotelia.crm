<?php

namespace Site\Storage\MySQL;

final class RoomInventoryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_inventory');
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
            self::column('id'),
            self::column('room_id'),
            self::column('inventory_id'),
            self::column('code'),
            self::column('qty'),
            self::column('comment'),
            InventoryMapper::column('name') => 'inventory',
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Inventory relation
                        ->innerJoin(InventoryMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('inventory_id'),
                            InventoryMapper::getRawColumn('id')
                        )
                        ->whereEquals('room_id', $roomId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
