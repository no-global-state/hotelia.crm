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
     * Fetch all rooms types
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
