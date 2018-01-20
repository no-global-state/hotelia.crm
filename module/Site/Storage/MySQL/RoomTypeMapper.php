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
     * @param int $langId
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $langId, int $hotelId) : array
    {
        // Columns to be selected
        $columns = [
            RoomTypeMapper::getFullColumnName('id'),
            RoomTypeMapper::getFullColumnName('hotel_id'),
            RoomTypeMapper::getFullColumnName('category_id'),
            RoomTypeMapper::getFullColumnName('persons'),
            RoomCategoryTranslationMapper::getFullColumnName('name')
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::getFullColumnName('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::getFullColumnName('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::getFullColumnName('lang_id'), $langId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
