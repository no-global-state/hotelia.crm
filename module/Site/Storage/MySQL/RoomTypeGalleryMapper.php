<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class RoomTypeGalleryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_type_gallery');
    }

    /**
     * Updates a cover
     * 
     * @param int $roomTypeId
     * @param int $photoId
     * @return boolean
     */
    public function updateCover(int $roomTypeId, int $photoId)
    {
        return $this->syncWithJunction(RoomTypeCoverMapper::getTableName(), $roomTypeId, [$photoId]);
    }

    /**
     * Fetch all images associated with unique room type
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function fetchAll(int $roomTypeId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('room_type_id'),
            self::column('file'),
            self::column('order'),
            new RawSqlFragment(sprintf('(%s = %s) AS cover', RoomTypeCoverMapper::column('master_id'), self::column('room_type_id')))
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            RoomTypeMapper::column('id') => self::getRawColumn('room_type_id')
                        ])
                        // Room type cover
                        ->leftJoin(RoomTypeCoverMapper::getTableName(), [
                            RoomTypeCoverMapper::column('slave_id') => self::getRawColumn('id')
                        ])
                        ->whereEquals('room_type_id', $roomTypeId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
