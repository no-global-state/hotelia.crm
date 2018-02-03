<?php

namespace Site\Storage\MySQL;

final class RoomTypeGalleryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_type_gallery');
    }

    /**
     * Fetch all images associated with unique room type
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function fetchAll(int $roomTypeId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('room_type_id', $roomTypeId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
