<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class RegionMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_regions');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return RegionTranslationMapper::getTableName();
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
            self::getFullColumnName('image'),
            RegionTranslationMapper::getFullColumnName('lang_id'),
            RegionTranslationMapper::getFullColumnName('name'),
        ];
    }

    /**
     * Find all hotels with their count by related region
     * 
     * @param int $languageId
     * @return array
     */
    public function findHotels(int $languageId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('image'),
            RegionTranslationMapper::column('name'),
        ];

        $db = $this->db->select($columns)
                       ->count(HotelMapper::column('region_id'), 'hotel_count')
                       ->from(self::getTableName())
                       // Translation relation
                       ->leftJoin(RegionTranslationMapper::getTableName(), [
                            RegionTranslationMapper::column('id') => self::column('id'),
                            RegionTranslationMapper::column('lang_id') => new RawSqlFragment(intval($languageId))
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            self::column('id') => HotelMapper::column('region_id'),
                            HotelMapper::column('active') => new RawSqlFragment('1')
                       ])
                       ->groupBy([
                            self::column('id'), 
                            RegionTranslationMapper::column('name')
                        ])
                       ->orderBy(self::column('order'));

        return $db->queryAll();
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
     * Fetch all regions
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(RegionTranslationMapper::getFullColumnName('lang_id'), $langId)
                    ->orderBy(self::getFullColumnName('order'))
                    ->queryAll();
    }
}
