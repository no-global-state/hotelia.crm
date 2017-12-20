<?php

namespace Site\Storage\MySQL;

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
            RegionTranslationMapper::getFullColumnName('lang_id'),
            RegionTranslationMapper::getFullColumnName('name'),
        ];
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
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
