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
            self::column('id'),
            self::column('order'),
            HotelTypeTranslationMapper::column('name'),
            HotelTypeTranslationMapper::column('lang_id'),
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
            self::column('id'),
            HotelTypeTranslationMapper::column('name')
        ];

        $db = $this->db->select($columns)
                       ->count(HotelMapper::column('id'), 'count')
                       ->from(self::getTableName())
                       // Translate relation
                       ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                            HotelTypeTranslationMapper::column('id') => self::getRawColumn('id')
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('type_id') => self::getRawColumn('id'),
                            HotelMapper::column('active') => new RawSqlFragment(1)
                       ])
                       // Language ID constraint
                       ->whereEquals(HotelTypeTranslationMapper::column('lang_id'), new RawSqlFragment($langId))
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
                    ->whereEquals(HotelTypeTranslationMapper::column('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
