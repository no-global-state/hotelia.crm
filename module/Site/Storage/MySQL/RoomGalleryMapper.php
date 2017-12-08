<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class RoomGalleryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_gallery');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all images
     * 
     * @param int $roomId
     * @return array
     */
    public function fetchAll(int $roomId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('room_id', $roomId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
