<?php

namespace Site\Storage\MySQL;

final class MealsMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_meals');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return MealsTranslationMapper::getTableName();
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
            MealsTranslationMapper::getFullColumnName('lang_id'),
            MealsTranslationMapper::getFullColumnName('name'),
        ];
    }

    /**
     * Fetch meal by its ID
     * 
     * @param int $id Meal ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all meals
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(MealsTranslationMapper::getFullColumnName('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
