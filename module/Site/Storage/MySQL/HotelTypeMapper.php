<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class HotelTypeMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotel_types');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return HotelTypeTranslationMapper::getTableName();
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
            HotelTypeTranslationMapper::getFullColumnName('name'),
            HotelTypeTranslationMapper::getFullColumnName('lang_id'),
        ];
    }

    /**
     * Fetch all hotel types with their corresponding hotel count
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAllWithCount(int $langId) : array
    {
        $langId = (int) $langId;

        // Columns to be selected
        $columns = [
            self::getFullColumnName('id'),
            HotelTypeTranslationMapper::getFullColumnName('name')
        ];

        $db = $this->db->select($columns)
                       ->count(HotelMapper::getFullColumnName('id'), 'count')
                       ->from(self::getTableName())
                       // Translate relation
                       ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                            HotelTypeTranslationMapper::getFullColumnName('id') => self::getRawColumn('id')
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::getFullColumnName('type_id') => self::getRawColumn('id'),
                            HotelMapper::getFullColumnName('active') => new RawSqlFragment(1)
                       ])
                       // Language ID constraint
                       ->whereEquals(HotelTypeTranslationMapper::getFullColumnName('lang_id'), new RawSqlFragment($langId))
                       ->groupBy($columns);

        return $db->queryAll();
    }

    /**
     * Fetch hotel type by its ID
     * 
     * @param int $id Hotel Type ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all hotel types
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(HotelTypeTranslationMapper::getFullColumnName('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
