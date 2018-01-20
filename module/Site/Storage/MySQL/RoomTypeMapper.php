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
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return RoomTypeTranslationMapper::getTableName();
    }

    /**
     * Returns shared columns
     * 
     * @return array
     */
    private function getColumns() : array
    {
        return [
            self::getFullColumnName('id'),
            RoomTypeTranslationMapper::getFullColumnName('lang_id'),
            self::getFullColumnName('hotel_id'),
            self::getFullColumnName('category_id'),
            self::getFullColumnName('persons'),
            RoomTypeTranslationMapper::getFullColumnName('description'),
        ];
    }

    /**
     * Fetch region by its ID
     * 
     * @param int $id Room type id
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
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
        $columns = array_merge($this->getColumns(), [
            RoomCategoryTranslationMapper::getFullColumnName('name')
        ]);

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::getFullColumnName('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id'),
                        ])
                        // Translation relation
                        ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::getFullColumnName(self::PARAM_COLUMN_ID) => RoomTypeTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID),
                            RoomTypeTranslationMapper::getFullColumnName('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
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
