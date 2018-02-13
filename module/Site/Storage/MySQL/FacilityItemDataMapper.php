<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class FacilityItemDataMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_facility_items_data');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return FacilityItemDataTranslationMapper::getTableName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Returns a collection of shared columns to be selected
     * 
     * @return array
     */
    private function getSharedColumns() : array
    {
        return [
            self::column('id'),
            self::column('item_id'),
            self::column('order'),
            FacilityItemDataTranslationMapper::column('lang_id'),
            FacilityItemDataTranslationMapper::column('name')
        ];
    }

    /**
     * Fetch by item data by its id
     * 
     * @param int $id
     * @param int $langId
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getSharedColumns(), $id, $langId);
    }

    /**
     * Fetch all data by associated item id
     * 
     * @param int $itemId
     * @param int $langId
     * @return array
     */
    public function fetchAll(int $itemId, int $langId) : array
    {
        return $this->createEntitySelect($this->getSharedColumns())
                    ->whereEquals(FacilityItemDataTranslationMapper::column('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
