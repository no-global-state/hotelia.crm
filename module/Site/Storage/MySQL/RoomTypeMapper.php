<?php

namespace Site\Storage\MySQL;

final class RoomTypeMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_types');
    }

    /**
     * Fetch all entities
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        // Hotel ID constraint
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
