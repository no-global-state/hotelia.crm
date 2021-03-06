<?php

namespace Site\Storage\MySQL;

final class DistrictMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_regions_districts');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return DistrictTranslationMapper::getTableName();
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
            self::column('region_id'),
            self::column('order'),
            DistrictTranslationMapper::column('lang_id'),
            DistrictTranslationMapper::column('name'),
        ];
    }

    /**
     * Fetch district by its ID
     * 
     * @param int $id District ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all districts
     * 
     * @param int|null $regionId Optional region Id filter
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll($regionId = null, int $langId) : array
    {
        $db = $this->createEntitySelect($this->getColumns())
                    ->whereEquals(DistrictTranslationMapper::column('lang_id'), $langId);

        // Optional region ID filter
        if ($regionId !== null) {
            $db->andWhereEquals(self::column('region_id'), $regionId);
        }

        return $db->orderBy(self::column('order'))
                  ->queryAll();
    }
}
