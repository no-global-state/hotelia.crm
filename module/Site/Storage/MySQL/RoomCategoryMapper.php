<?php

namespace Site\Storage\MySQL;

final class RoomCategoryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_categories');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return RoomCategoryTranslationMapper::getTableName();
    }

    /**
     * Returns shared columns to be selected
     * 
     * @return array
     */
    private function getColumns() : array
    {
        return [
            self::getFullColumnName('id'),
            self::getFullColumnName('order'),
            RoomCategoryTranslationMapper::getFullColumnName('lang_id'),
            RoomCategoryTranslationMapper::getFullColumnName('name'),
        ];
    }

    /**
     * Find attached IDs by associated hotel ID
     * 
     * @param int $hotelId
     * @return array
     */
    public function findAttachedIds(int $hotelId) : array
    {
        $db = $this->db->select(self::column($this->getPk()))
                        ->from(RoomTypeMapper::getTableName())
                        // Room category relation
                        ->leftJoin(self::getTableName(), [
                            self::getFullColumnName('id') => RoomTypeMapper::getRawColumn('category_id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(RoomTypeMapper::getFullColumnName('hotel_id'), $hotelId);

        return $db->queryAll($this->getPk());
    }

    /**
     * Fetch region by its ID
     * 
     * @param int $id Region ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all room categories
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(RoomCategoryTranslationMapper::getFullColumnName('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
