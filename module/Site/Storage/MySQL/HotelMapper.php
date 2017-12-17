<?php

namespace Site\Storage\MySQL;

final class HotelMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return HotelTranslationMapper::getTableName();
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
            self::getFullColumnName('phone'),
            self::getFullColumnName('currency'),
            self::getFullColumnName('start_price'),
            self::getFullColumnName('rate'),
            self::getFullColumnName('discount'),
            self::getFullColumnName('daily_tax'),
            self::getFullColumnName('website'),
            self::getFullColumnName('email'),
            HotelTranslationMapper::getFullColumnName('lang_id'),
            HotelTranslationMapper::getFullColumnName('city'),
            HotelTranslationMapper::getFullColumnName('name'),
            HotelTranslationMapper::getFullColumnName('address'),
            HotelTranslationMapper::getFullColumnName('description')
        ];
    }

    /**
     * Fetch hotel by its ID
     * 
     * @param int $id Hotel ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all hotels
     * 
     * @return array
     */
    public function fetchAll() : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->orderBy($this->getPk())
                    ->desc()
                    ->asManyToMany(
                        'facilities', 
                        FacilitiyItemMapper::getJunctionTableName(), 
                        self::PARAM_JUNCTION_MASTER_COLUMN, 
                        FacilitiyItemMapper::getTableName(), 
                        'id', 
                        'name' // Columns to be selected in Service table
                    )
                    ->queryAll();
    }
}
